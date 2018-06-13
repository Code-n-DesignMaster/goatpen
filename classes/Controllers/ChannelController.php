<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Channel;
use GoatPen\ViewHelpers\Notification;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class ChannelController
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
            return $this->renderer->render($response, '/channels/delete.phtml', [
                'channel' => Channel::findOrFail($args['channel_id']),
                'csrf'    => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function deleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $channel = (isset($args['channel_id']) ? Channel::findOrFail($args['channel_id']) : new Channel);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $channel->delete();

            Notification::add(sprintf('Channel \'%s\' has been deleted', $channel->name), 'success');

            return $response->withRedirect('/influencers/' . $args['id']);
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
