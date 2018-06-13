<?php
namespace GoatPen\Search;

use GoatPen\Formatters\Phrasing;
use GoatPen\ViewHelpers\Number;
use GoatPen\{Influencer, InfluencerTrait, Metric, Platform, User};
use Illuminate\Database\Eloquent\Builder;
use Iso3166\Codes;
use LaravelGems\Escape\HTML;
use Psr\Http\Message\RequestInterface;

class InfluencerSearch
{
    const OPERATORS = [
        'less than',
        'more than',
    ];

    protected $request;
    protected $query;

    public $name;
    public $gender;
    public $age_group;
    public $location;
    public $nationality;
    public $platform_id;
    public $price_operator;
    public $price;
    public $user_id;
    public $tags = [];
    public $traits = [];
    public $metrics = [];

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;

        $this->query = Influencer::query()
            ->leftJoin('channels', 'channels.influencer_id', '=', 'influencers.id')
            ->leftJoin('platforms', 'platforms.id', '=', 'channels.platform_id');

        $this->buildQuery();
    }

    private function buildQuery()
    {
        $this->name           = $this->request->getParam('name');
        $this->gender         = $this->request->getParam('gender');
        $this->age_group      = $this->request->getParam('age_group');
        $this->location       = $this->request->getParam('location');
        $this->nationality    = $this->request->getParam('nationality');
        $this->platform_id    = $this->request->getParam('platform_id');
        $this->price_operator = $this->request->getParam('price_operator');
        $this->price          = $this->request->getParam('price');
        $this->user_id        = $this->request->getParam('user_id');
        $this->tags           = array_filter(array_unique($this->request->getParam('tag', [])));
        $this->traits         = array_filter($this->request->getParam('trait', []));
        $this->metrics        = $this->request->getParam('metric', []);

        if ($this->name) {
            $this->query->where('influencers.name', 'like', '%' . $this->name . '%');
        }

        if ($this->gender) {
            $this->query->whereRaw('IFNULL(influencers.gender, "") IN (?, "")', [$this->gender]);
        }

        if ($this->age_group) {
            $this->query->whereRaw('IFNULL(influencers.age_group, "") IN (?, "")', [$this->age_group]);
        }

        if ($this->location) {
            $this->query->whereRaw('IFNULL(influencers.location, "") IN (?, "")', [$this->location]);
        }

        if ($this->nationality) {
            $this->query->whereRaw('IFNULL(influencers.nationality, "") IN (?, "")', [$this->nationality]);
        }

        if ($this->platform_id) {
            $this->query->where('channels.platform_id', '=', $this->platform_id);
        }

        if (in_array($this->price_operator, static::OPERATORS) && $this->price) {
            $this->query->where(function ($query) {
                $query->where('channels.price', ($this->price_operator === 'less than' ? '<=' : '>='), $this->price)
                    ->orWhereRaw('IFNULL(channels.price, "") = ""');
            });
        }

        if ($this->user_id) {
            $this->query->where(function ($query) {
                $query->where('influencers.user_id', '=', $this->user_id)
                    ->orWhere('influencers.secondary_user_id', '=', $this->user_id);
            });
        }

        if (! empty($this->tags)) {
            foreach ($this->tags as $tag) {
                $this->query->where(function ($query) use ($tag) {
                    $query->where('influencers.primary_tag', '=', $tag)
                        ->orWhere('influencers.tags', 'like', '%"' . $tag . '"%');
                });
            }
        }

        if (! empty($this->traits)) {
            foreach ($this->traits as $trait) {
                $this->query->whereIn('influencers.id', function ($query) use ($trait) {
                    $query->select('influencer_id')
                        ->from('influencer_trait')
                        ->where('trait_id', '=', $trait);
                });
            }
        }

        if (! empty($this->metrics)) {
            $this->metrics = array_filter($this->metrics, function ($metric) {
                return ($metric['id'] ?? false) && ($metric['operator'] ?? false) && ($metric['quantity'] ?? false);
            });

            foreach ($this->metrics as $criteria) {
                $metric = Metric::find($criteria['id']);

                switch ($criteria['operator']) {
                    case 'less than':
                        $operator = '<=';
                        break;
                    case 'more than':
                        $operator = '>=';
                        break;
                    default:
                        continue 2;
                }

                switch ($metric->scope) {
                    case 'Influencer':
                        $this->query->whereRaw('LOCATE(\'"' . $metric->id . '":\', `channels`.`metrics`) > 0')
                            ->whereRaw('CAST(SUBSTRING(`channels`.`metrics`, LOCATE(\'"' . $metric->id . '":\', `channels`.`metrics`) + 4 + ' . strlen($metric->id) . ', LOCATE(\'"\', SUBSTRING(`channels`.`metrics`, LOCATE(\'"' . $metric->id . '":\', `channels`.`metrics`) + 4 + ' . strlen($metric->id) . ')) - 1) AS DECIMAL(12,2)) ' . $operator . ' ' . $criteria['quantity']);

                        break;
                    case 'Campaign':
                        $this->query->where(function ($where) use ($metric, $operator, $criteria) {
                            $where->whereIn('channels.id', function ($query) use ($metric, $operator, $criteria) {
                                $query->select('channel_id')
                                    ->from('posts')
                                    ->whereRaw('LOCATE(\'"' . $metric->id . '":\', `metrics`) > 0')
                                    ->whereRaw('CAST(SUBSTRING(`metrics`, LOCATE(\'"' . $metric->id . '":\', `metrics`) + 4 + ' . strlen($metric->id) . ', LOCATE(\'"\', SUBSTRING(`metrics`, LOCATE(\'"' . $metric->id . '":\', `metrics`) + 4 + ' . strlen($metric->id) . ')) - 1) AS DECIMAL(12,2)) ' . $operator . ' ' . $criteria['quantity']);
                            })
                                ->orWhereIn('channels.id', function ($query) use ($metric, $operator, $criteria) {
                                    $query->select('channel_id')
                                        ->from('stats')
                                        ->whereRaw('LOCATE(\'"' . $metric->id . '":\', `metrics`) > 0');

                                    if ($metric->automated) {
                                        $query->whereRaw('CAST(SUBSTRING(`metrics`, LOCATE(\'"' . $metric->id . '":\', `metrics`) + 4 + ' . strlen($metric->id) . ', LOCATE(\'"\', SUBSTRING(`metrics`, LOCATE(\'"' . $metric->id . '":\', `metrics`) + 4 + ' . strlen($metric->id) . ')) - 1) AS DECIMAL(12,2)) ' . $operator . ' ' . $criteria['quantity']);
                                    } else {
                                        $query->whereRaw('(CAST(SUBSTRING(`metrics`, LOCATE(\'"' . $metric->id . '":\', `metrics`) + 4 + ' . strlen($metric->id) . ', LOCATE(\'"\', SUBSTRING(`metrics`, LOCATE(\'"' . $metric->id . '":\', `metrics`) + 4 + ' . strlen($metric->id) . ')) - 1) AS DECIMAL(12,2)) / `total_posts`) ' . $operator . ' ' . $criteria['quantity']);
                                    }
                                });
                        });

                        break;
                }
            }
        }
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function description()
    {
        $filters = [];

        if ($this->name) {
            $filters[] = sprintf('name contains "<strong>%s</strong>"', HTML::text($this->name));
        }

        if ($this->gender) {
            $filters[] = sprintf('gender is <strong>%s</strong>', HTML::text($this->gender));
        }

        if ($this->age_group) {
            $filters[] = sprintf('age group is <strong>%s</strong>', HTML::text($this->age_group));
        }

        if ($this->location) {
            $filters[] = sprintf('location is <strong>%s</strong>', HTML::text(Codes::country($this->location)));
        }

        if ($this->nationality) {
            $filters[] = sprintf('nationality is <strong>%s</strong>', HTML::text(Codes::country($this->nationality)));
        }

        if ($this->platform_id) {
            $filters[] = sprintf(
                'platform is <strong>%s</strong>',
                HTML::text(Platform::findOrFail($this->platform_id)->name)
            );
        }

        if ($this->price_operator && $this->price) {
            $filters[] = sprintf(
                'price is %s <strong>%s</strong>',
                HTML::text($this->price_operator),
                '£' . number_format($this->price, 2)
            );
        }

        if ($this->user_id) {
            $filters[] = sprintf(
                'contact is <strong>%s</strong>',
                HTML::text(User::findOrFail($this->user_id)->name)
            );
        }

        if (! empty($this->tags)) {
            $tags = array_map(function ($tag) {
                return HTML::text($tag);
            }, $this->tags);

            $filters[] = 'tagged as <strong>' . implode('</strong>, <strong>', $tags) . '</strong>';
        }

        if (! empty($this->traits)) {
            $traits = array_map(function ($id) {
                return InfluencerTrait::find($id)->name;
            }, $this->traits);

            $filters[] = 'traited as <strong>' . implode('</strong>, <strong>', $traits) . '</strong>';
        }

        if (! empty($this->metrics)) {
            foreach ($this->metrics as $criteria) {
                $metric = Metric::find($criteria['id']);

                if (! $metric->exists) {
                    continue;
                }

                $filters[] = sprintf(
                    '%s <strong>%s %s</strong>',
                    HTML::text($criteria['operator']),
                    Number::formatMetric($metric, $criteria['quantity']),
                    HTML::text(strtolower($metric->name))
                );
            }
        }

        if (empty($filters)) {
            return false;
        }

        return 'Where ' . Phrasing::readableList($filters) . '.';
    }
}
