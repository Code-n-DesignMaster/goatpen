<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{Campaign, Channel, Post};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class CampaignBuilderController
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
        try {
            $campaign = Campaign::findOrFail($args['id']);
            $channels = [];

            $posts = $campaign->posts()
                ->select('posts.*')
                ->join('channels', 'channels.id', '=', 'posts.channel_id')
                ->join('influencers', 'influencers.id', '=', 'channels.influencer_id')
                ->join('platforms', 'platforms.id', '=', 'channels.platform_id')
                ->orderBy('influencers.name', 'asc')
                ->orderBy('channels.name', 'asc')
                ->orderBy('platforms.name', 'asc');

            foreach ($posts->get() as $post) {
                if (! array_key_exists($post->channel->getKey(), $channels)) {
                    $channels[$post->channel->getKey()] = 0;
                }

                $channels[$post->channel->getKey()]++;
            }

            return $this->renderer->render($response, '/campaigns/builder.phtml', [
                'campaign' => $campaign,
                'channels' => $channels,
                'csrf'     => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function saveAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $campaign = Campaign::findOrFail($args['id']);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            foreach ($request->getParsedBodyParam('channel') as $id => $posts) {
                $channel = Channel::findOrFail($id);

                $currentPosts = Post::query()
                    ->where('campaign_id', '=', $campaign->getKey())
                    ->where('channel_id', '=', $channel->getKey())
                    ->count();

                // Delete unwanted posts
                if ($currentPosts > $posts) {
                    Post::query()
                        ->where('campaign_id', '=', $campaign->getKey())
                        ->where('channel_id', '=', $channel->getKey())
                        ->orderBy('id', 'desc')
                        ->limit($currentPosts - $posts)
                        ->delete();
                }

                // Add new posts
                for ($count = $currentPosts; $count < $posts; $count++) {
                    $post = new Post;
                    $post->channel()->associate($channel);
                    $post->campaign()->associate($campaign);
                    $post->save();
                }
            }

            Notification::add(sprintf('The template for campaign \'%s\' has been saved', $campaign->name), 'success');

            return $response->withRedirect('/campaigns/' . $campaign->getKey() . '/builder');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }

    public function addChannelAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $campaign = Campaign::findOrFail($args['id']);
            $channel  = Channel::findOrFail($request->getParsedBodyParam('channel_id'));
        } catch (Exception $exception) {
            return $response->withStatus(404);
        }

        $post = new Post;
        $post->channel()->associate($channel);
        $post->campaign()->associate($campaign);
        $post->save();

        return $response;
    }
}
