<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Stat;
use GoatPen\ViewHelpers\Notification;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class StatController
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
            $stat = Stat::findOrFail($args['stat_id']);

            return $this->renderer->render($response, '/stats/delete.phtml', [
                'stat'     => $stat,
                'campaign' => $stat->campaign,
                'channel'  => $stat->channel,
                'csrf'     => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function deleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $stat = (isset($args['stat_id']) ? Stat::findOrFail($args['stat_id']) : new Stat);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $stat->delete();

            Notification::add(sprintf(
                'Stat \'%s\' has been deleted from campaign \'%s\' for %s',
                $stat->channel->name,
                $stat->campaign->name,
                $stat->campaign->client
            ), 'success');

            return $response->withRedirect('/campaigns/' . $stat->campaign->id . '/edit');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
