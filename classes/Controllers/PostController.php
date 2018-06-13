<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Post;
use GoatPen\ViewHelpers\Notification;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class PostController
{
    protected $csrf;
    protected $renderer;

    public function __construct(ContainerInterface $container)
    {
        $this->csrf     = $container['csrf'];
        $this->renderer = $container['renderer'];
    }

    public function confirmDeleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $post = Post::findOrFail($args['post_id']);

            return $this->renderer->render($response, '/posts/delete.phtml', [
                'post'     => $post,
                'campaign' => $post->campaign,
                'channel'  => $post->channel,
                'csrf'     => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function deleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $post = (isset($args['post_id']) ? Post::findOrFail($args['post_id']) : new Post);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $post->delete();

            Notification::add(sprintf(
                'Post \'%s\' has been deleted from campaign \'%s\' for %s',
                $post->channel->name,
                $post->campaign->name,
                $post->campaign->client
            ), 'success');

            return $response->withRedirect('/campaigns/' . $post->campaign->id . '/edit');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
