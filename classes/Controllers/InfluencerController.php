<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\Formatters\CommentResponseFormatter;
use GoatPen\Search\InfluencerSearch;
use GoatPen\Services\{DemographicsService, TagsService};
use GoatPen\ViewHelpers\{Notification, Paginator};
use GoatPen\{Campaign, Channel, Demographic, Influencer, InfluencerComment, Metric, Platform, Session, User};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class InfluencerController
{
    protected $csrf;
    protected $renderer;

    public function __construct(ContainerInterface $container)
    {
        $this->csrf     = $container['csrf'];
        $this->renderer = $container['renderer'];
    }

    public function listAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $search      = new InfluencerSearch($request);
        $influencers = $search->query()
            ->select('influencers.*', 'channels.id as channel_id')
            ->orderBy('influencers.name', 'asc')
            ->orderBy('channels.name', 'asc')
            ->orderBy('platforms.name', 'asc');

        $campaign = Campaign::find($request->getQueryParam('campaign'));

        $page = (int) $request->getParam('page', 1);

        return $this->renderer->render($response, '/influencers/list.phtml', [
            'paginator'   => new Paginator($influencers->count(), $page),
            'influencers' => $influencers->offset(($page - 1) * Paginator::PAGE_SIZE)->limit(Paginator::PAGE_SIZE),
            'filters'     => $search,
            'campaign'    => $campaign,
            'csrf'        => $this->csrf,
        ]);
    }

    public function profileAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $influencer = Influencer::findOrFail($args['id']);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        $channels = $influencer->channels()
            ->select('channels.*')
            ->join('platforms', 'platforms.id', '=', 'channels.platform_id')
            ->orderBy('platforms.name', 'asc')
            ->orderBy('channels.name', 'asc')
            ->get();

        $metrics = Metric::query()
            ->where('scope', '=', 'Influencer')
            ->where('automated', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        return $this->renderer->render($response, '/influencers/profile.phtml', [
            'influencer' => $influencer,
            'channels'   => $channels,
            'metrics'    => $metrics,
        ]);
    }

    public function newAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->renderer->render($response, '/influencers/details.phtml', [
            'influencer'   => new Influencer,
            'users'        => User::orderBy('name', 'asc')->get(),
            'trait_ids'    => [],
            'channels'     => [new Channel],
            'platforms'    => Platform::orderBy('order', 'asc')->get(),
            'metrics'      => Metric::where('scope', '=', 'Influencer')->where('automated', '=', false)->orderBy('name', 'asc')->get(),
            'demographics' => Demographic::query()->orderBy('name', 'asc')->get(),
            'csrf'         => $this->csrf,
        ]);
    }

    public function detailsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $influencer = Influencer::findOrFail($args['id']);
            $channels   = $influencer->channels->all();

            if (empty($channels)) {
                $channels[] = new Channel;
            }

            return $this->renderer->render($response, '/influencers/details.phtml', [
                'influencer'   => $influencer,
                'users'        => User::orderBy('name', 'asc')->get(),
                'trait_ids'    => $influencer->traits()->allRelatedIds()->toArray(),
                'channels'     => $channels,
                'platforms'    => Platform::orderBy('order', 'asc')->get(),
                'metrics'      => Metric::where('scope', '=', 'Influencer')->where('automated', '=', false)->orderBy('name', 'asc')->get(),
                'demographics' => Demographic::query()->orderBy('name', 'asc')->get(),
                'csrf'         => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function confirmDeleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            return $this->renderer->render($response, '/influencers/delete.phtml', [
                'influencer' => Influencer::findOrFail($args['id']),
                'csrf'       => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function saveAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $influencer = (isset($args['id']) ? Influencer::findOrFail($args['id']) : new Influencer);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $influencer->name              = $request->getParsedBodyParam('name');
            $influencer->email             = $request->getParsedBodyParam('email') ?: null;
            $influencer->phone             = $request->getParsedBodyParam('phone') ?: null;
            $influencer->gender            = $request->getParsedBodyParam('gender') ?: null;
            $influencer->age_group         = $request->getParsedBodyParam('age_group') ?: null;
            $influencer->location          = $request->getParsedBodyParam('location') ?: null;
            $influencer->nationality       = $request->getParsedBodyParam('nationality') ?: null;
            $influencer->user_id           = $request->getParsedBodyParam('user_id');
            $influencer->secondary_user_id = $request->getParsedBodyParam('secondary_user_id') ?: null;
            $influencer->primary_tag       = $request->getParsedBodyParam('primary_tag') ?: null;
            $influencer->tags              = TagsService::sanitise($request->getParsedBodyParam('tag', []), $influencer->primary_tag) ?: null;

            $channels = [];
            $count = 0;

            foreach ($request->getParsedBodyParam('channel') as $id => $data) {
                $channel = Channel::findOrNew($id);
                $count++;

                if (! $channel->exists && strlen($data['name']) === 0) {
                    continue;
                }

                $channel->name         = $data['name'];
                $channel->url          = $data['url'];
                $channel->price        = $data['price'] ?: null;
                $channel->negotiable   = isset($data['negotiable']);
                $channel->platform_id  = $data['platform_id'];
                $channel->metrics      = array_filter($data['metric']);
                $channel->demographics = DemographicsService::sanitise(
                    array_combine($data['demographic']['name'], $data['demographic']['value'])
                );

                $channels[$count] = $channel;
            }

            // Validation
            if (strlen($influencer->name) === 0) {
                throw new ValidationException('Please enter a name for the influencer');
            }

            if (! $influencer->user) {
                throw new ValidationException('Please select a primary contact for the influencer');
            }

            foreach ($channels as $count => $channel) {
                if (strlen($channel->name) === 0) {
                    throw new ValidationException(sprintf('Please enter a name for channel #%d', $count));
                }

                if (strlen($channel->url) === 0) {
                    throw new ValidationException(sprintf('Please enter a URL for channel #%d', $count));
                }

                if (! $channel->platform) {
                    throw new ValidationException(sprintf('Please select a platform for channel #%d', $count));
                }
            }

            $influencer->save();

            $influencer->traits()->sync($request->getParsedBodyParam('trait_id', []));

            foreach ($channels as $channel) {
                $channel->influencer()->associate($influencer);
                $channel->save();
            }

            Notification::add(sprintf('Influencer \'%s\' has been saved', $influencer->name), 'success');

            return $response->withRedirect('/influencers');
        } catch (ValidationException $exception) {
            Notification::add($exception->getMessage(), 'danger');

            if (empty($channels)) {
                $channels[] = new Channel;
            }

            return $this->renderer->render($response, '/influencers/details.phtml', [
                'influencer' => $influencer,
                'users'      => User::orderBy('name', 'asc')->get(),
                'trait_ids'  => $influencer->traits()->allRelatedIds()->toArray(),
                'channels'   => $channels,
                'platforms'  => Platform::orderBy('order', 'asc')->get(),
                'metrics'    => Metric::where('scope', '=', 'Influencer')->where('automated', '=', false)->orderBy('name', 'asc')->get(),
                'csrf'       => $this->csrf,
            ]);
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }

    public function deleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $influencer = (isset($args['id']) ? Influencer::findOrFail($args['id']) : new Influencer);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $influencer->delete();

            Notification::add(sprintf('Influencer \'%s\' has been deleted', $influencer->name), 'success');

            return $response->withRedirect('/influencers');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }

    public function getCommentsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $response->withJson(
            CommentResponseFormatter::listToArray(
                Influencer::findOrFail($args['id'])->comments, Session::getUser()->id
            )
        );
    }

    public function saveCommentAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $comment          = new InfluencerComment;
        $comment->comment = trim($request->getParam('comment'));

        $comment->user()->associate(Session::getUser());
        $comment->influencer()->associate(Influencer::findOrFail($args['id']));
        $comment->save();

        return $response->withJson(CommentResponseFormatter::toArray($comment, Session::getUser()->id));
    }
}
