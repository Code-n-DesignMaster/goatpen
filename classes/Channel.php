<?php
namespace GoatPen;

use GoatPen\Observers\ChannelObserver;
use GoatPen\Services\{FacebookService, InstagramService, TwitchService, TwitterService, YouTubeService};
use Illuminate\Database\Eloquent\{Builder, Model};

class Channel extends Model
{
    public $casts = [
        'influencer_id'     => 'integer',
        'platform_id'       => 'integer',
        'name'              => 'string',
        'url'               => 'string',
        'identifier'        => 'string',
        'username'          => 'string',
        'price'             => 'double',
        'negotiable'        => 'boolean',
        'metrics'           => 'json',
        'demographics'      => 'json',
        'error'             => 'string',
        'cached_metrics_at' => 'datetime',
    ];

    public $revisionable = [
        'influencer_id',
        'platform_id',
        'name',
        'url',
        'price',
        'negotiable',
        'metrics',
        'demographics',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new ChannelObserver);
    }

    public function getUrlAttribute($value): string
    {
        return 'http://' . $value;
    }

    public function setUrlAttribute($value)
    {
        $this->attributes['url'] = preg_replace('#^https?://#', '', $value);
    }

    public function influencer()
    {
        return $this->belongsTo(Influencer::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function stats()
    {
        return $this->hasMany(Stat::class);
    }

    public function campaigns()
    {
        return $this->hasManyThrough(Campaign::class, Post::class, 'channel_id', 'id', 'id', 'campaign_id')->distinct();
    }

    public function relatedMetrics()
    {
        return $this->hasManyThrough(Metric::class, Platform::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }

    public function totalPosts(): int
    {
        $total = $this->posts->count();

        foreach ($this->stats as $stat) {
            $total += $stat->total_posts;
        }

        return $total;
    }

    public function totalCampaigns(): int
    {
        return count(
            array_unique(
                array_merge(
                    $this->campaigns->pluck('id')->toArray(),
                    $this->stats->pluck('campaign_id')->toArray()
                )
            )
        );
    }

    public function allCampaigns(): array
    {
        $campaigns = [];

        foreach ($this->campaigns as $campaign) {
            $campaigns[$campaign->getKey()] = $campaign;
        }

        foreach ($this->stats as $stat) {
            $campaigns[$stat->campaign->getKey()] = $stat->campaign;
        }

        usort($campaigns, function ($a, $b) {
            return ($a->client > $b->client);
        });

        return $campaigns;
    }

    public function scopeWithMetric(Builder $query, Metric $metric): Builder
    {
        return $query->where('channels.metrics', 'LIKE', '%"' . $metric->getKey() . '":');
    }

    public function scopeWithDemographic(Builder $query, string $demographic): Builder
    {
        return $query->where('channels.demographics', 'LIKE', '%"' . $demographic . '":"');
    }

    public function metricRange(Metric $metric): array
    {
        $range = [];

        foreach ($this->posts as $post) {
            if (isset($post->metrics[$metric->getKey()])) {
                $range[] = $post->metrics[$metric->getKey()];
            }
        }

        foreach ($this->stats as $stat) {
            if (isset($stat->metrics[$metric->getKey()])) {
                if ($metric->automated) {
                    $range[] = $stat->metrics[$metric->getKey()];
                } else {
                    $range[] = round($stat->metrics[$metric->getKey()] / $stat->total_posts, 2);
                }
            }
        }

        if (empty($range)) {
            return [];
        }

        return [
            min($range),
            max($range),
        ];
    }

    public function campaignStats(array $types = Metric::TYPES): array
    {
        $stats = [];

        $metrics = Metric::query()
            ->select('metrics.*')
            ->join('metric_platform', function ($join) {
                $join->on('metric_platform.metric_id', '=', 'metrics.id')
                    ->where('metric_platform.platform_id', '=', $this->platform->id);
            })
            ->where('metrics.scope', '=', 'Campaign')
            ->whereIn('metrics.type', $types)
            ->orderBy('metrics.name', 'asc')
            ->get();

        foreach ($metrics as $metric) {
            $count = 0;
            $total = 0;

            foreach ($this->posts as $post) {
                if (isset($post->metrics[$metric->getKey()])) {
                    $count++;
                    $total += $post->metrics[$metric->getKey()];
                }
            }

            foreach ($this->stats as $stat) {
                if (isset($stat->metrics[$metric->getKey()])) {
                    $count += $stat->total_posts;

                    if ($metric->automated) {
                        $total += ($stat->metrics[$metric->getKey()] * $stat->total_posts);
                    } else {
                        $total += $stat->metrics[$metric->getKey()];
                    }
                }
            }

            if ($count > 0) {
                $stats[$metric->getKey()] = ($total / $count);
            }
        }

        return $stats;
    }

    public function populateMetrics()
    {
        switch ($this->platform->getKey()) {
            case Platform::BLOG_ID:
                $this->populateMetricsFromBlog();
                break;
            case Platform::FACEBOOK_ID:
            case Platform::FACEBOOK_LIVE_ID:
                $this->populateMetricsFromFacebook();
                break;
            case Platform::INSTAGRAM_ID:
            case Platform::INSTAGRAM_STORIES_ID:
                $this->populateMetricsFromInstagram();
                break;
            case Platform::LIVELY_ID:
                $this->populateMetricsFromLively();
                break;
            case Platform::MUSICALLY_ID:
                $this->populateMetricsFromMusically();
                break;
            case Platform::SNAPCHAT_ID:
                $this->populateMetricsFromSnapchat();
                break;
            case Platform::TWITCH_ID:
                $this->populateMetricsFromTwitch();
                break;
            case Platform::TWITTER_ID:
                $this->populateMetricsFromTwitter();
                break;
            case Platform::YOUTUBE_ID:
                $this->populateMetricsFromYouTube();
                break;
        }
    }

    private function populateMetricsFromBlog()
    {
        if (! $this->username) {
            $this->username = $this->url;
        }
    }

    private function populateMetricsFromFacebook()
    {
        if (! $this->username && preg_match('/facebook.com\/((pg|pages)\/)?([a-z\d_.]+)/i', $this->url, $matches)) {
            $this->username = $matches[3];
        }

        if ($this->identifier) {
            $data = (new FacebookService)->getPageFromId($this->identifier);
        } else {
            $data = (new FacebookService)->getPageFromUrl($this->url);
        }

        if (! $data) {
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::PAGE_LIKES_ID] = ($data['fan_count'] ?? null);

        $this->identifier = $data['id'];
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromInstagram()
    {
        if (! $this->identifier || ! $this->username) {
            preg_match('/instagram.com\/([a-z\d_.]+)/i', $this->url, $matches);

            if (! isset($matches[1])) {
                return;
            }

            $this->identifier = $matches[1];
            $this->username   = $matches[1];
        }

        $data = (new InstagramService)->getChannelFromUser($this->identifier);

        if (! $data) {
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::FOLLOWERS_ID] = $data->user->followed_by->count;

        foreach ($data->user->media->nodes as $node) {
            $engagements[] = ($node->comments->count + $node->likes->count);
        }

        if (! empty($engagements)) {
            $metrics[Metric::AVERAGE_ENGAGEMENTS_ID] = round(array_sum($engagements) / count($engagements));
        }

        $this->identifier = $data->user->username;
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromLively()
    {
        if (! $this->username && preg_match('/@([a-z\d_.]+)/i', $this->url, $matches)) {
            $this->username = $matches[1];
        }
    }

    private function populateMetricsFromMusically()
    {
        if (! $this->username && preg_match('/musical.ly\/([a-z\d.]+)/i', $this->url, $matches)) {
            $this->username = $matches[1];
        }
    }

    private function populateMetricsFromSnapchat()
    {
        if (! $this->username && preg_match('/snapchat.com\/([a-z\d]+)/i', $this->url, $matches)) {
            $this->username = $matches[1];
        }
    }

    private function populateMetricsFromTwitch()
    {
        if ($this->identifier && $this->username) {
            $data = (new TwitchService)->getChannelFromId($this->identifier);
        } else {
            preg_match('/twitch.tv\/([a-z\d_]+)/i', $this->url, $matches);

            if (! isset($matches[1])) {
                return;
            }

            $this->username = $matches[1];

            $data = (new TwitchService)->getChannelFromUser($this->username);
        }

        if (! $data) {
            return;
        }

        $metrics = ($this->metrics ?: []);
        // @todo Followers count will be available soon:
        // https://discuss.dev.twitch.tv/t/new-twitch-api-get-total-followers-count/12489/8
        // $metrics[Metric::FOLLOWERS_ID] = $data->data[0]->follower_count;

        $this->identifier = $data->data[0]->id;
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromTwitter()
    {
        if ($this->identifier && $this->username) {
            $data = (new TwitterService)->getUserFromId($this->identifier);
        } else {
            preg_match('/twitter.com\/([a-z\d_]+)/i', $this->url, $matches);

            if (! isset($matches[1])) {
                return;
            }

            $this->username = $matches[1];

            $data = (new TwitterService)->getUserFromName($this->username);
        }

        if (! $data) {
            return;
        }

        if (! empty($data->errors)) {
            $this->error = sprintf('Error %d: %s', $data->errors[0]->code, $data->errors[0]->message);
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::FOLLOWERS_ID] = $data->followers_count;

        $this->identifier = $data->id_str;
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromYouTube()
    {
        if ($this->identifier && $this->username) {
            $data = (new YouTubeService)->getChannelFromId($this->identifier);
        } else {
            preg_match('/youtube.com\/(channel|user)\/([a-z\d-]+)/i', $this->url, $matches);

            if (! isset($matches[1], $matches[2])) {
                return;
            }

            $this->username = $matches[2];

            switch ($matches[1]) {
                case 'channel':
                    $data = (new YouTubeService)->getChannelFromId($matches[2]);
                    break;
                case 'user':
                    $data = (new YouTubeService)->getChannelFromUser($matches[2]);
                    break;
                default:
                    return;
            }
        }

        if (! $data || empty($data->getItems())) {
            return;
        }

        $stats = $data->getItems()[0]->getStatistics();

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::SUBSCRIBERS_ID]         = (int) $stats->getSubscriberCount();
        $metrics[Metric::AVERAGE_VIDEO_VIEWS_ID] = ((int) $stats->getVideoCount() > 0 ? round((int) $stats->getViewCount() / (int) $stats->getVideoCount()) : 0);

        $this->identifier = $data->getItems()[0]->getId();
        $this->metrics    = array_filter($metrics);
    }

    public function inCampaign(Campaign $campaign)
    {
        $posts = Post::query()
            ->where('campaign_id', '=', $campaign->getKey())
            ->where('channel_id', '=', $this->getKey());

        return ($posts->count() > 0);
    }

    public function following(): int
    {
        foreach (Metric::FOLLOWINGS as $id) {
            if (array_key_exists($id, $this->metrics)) {
                return $this->metrics[$id];
            }
        }

        return 0;
    }

    public function getMetrics(array $types = []): array
    {
        $metrics = [];

        foreach ($this->metrics ?? [] as $id => $value) {
            $metric = Metric::find($id);

            if (! $metric) {
                continue;
            }

            if ($types && ! in_array($metric->type, $types)) {
                continue;
            }

            $metrics[$metric->getKey()] = $value;
        }

        return $metrics;
    }

    public function getDemographics(int $id): array
    {
        $demographics = [];

        foreach (Demographic::find($id)->groups() as $group) {
            if (array_key_exists($group, $this->demographics)) {
                $demographics[$group] = $this->demographics[$group];
            }
        }

        return $demographics;
    }
}
