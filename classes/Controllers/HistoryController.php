<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\ViewHelpers\Paginator;
use GoatPen\{Campaign, Channel, Demographic, Influencer, Metric, Platform, Post, Session, User};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class HistoryController
{
    protected $renderer;

    public function __construct(ContainerInterface $container)
    {
        $this->renderer = $container['renderer'];
    }

    public function campaignAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Campaign::class);
    }

    public function channelAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Channel::class);
    }

    public function influencerAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Influencer::class);
    }

    public function metricAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Metric::class);
    }

    public function platformAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Platform::class);
    }

    public function demographicAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Demographic::class);
    }

    public function postAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, Post::class);
    }

    public function userAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->listAction($request, $response, $args, User::class);
    }

    public function listAction(RequestInterface $request, ResponseInterface $response, $args, string $class): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $revisionable = $class::findOrFail($args['id']);
            $revisions    = $revisionable->revisions()->orderBy('created_at', 'desc')->orderBy('id', 'desc');

            $page = (int) $request->getParam('page', 1);

            return $this->renderer->render($response, '/history.phtml', [
                'paginator'    => new Paginator($revisions->count(), $page),
                'revisionable' => $revisionable,
                'revisions'    => $revisions->offset(($page - 1) * Paginator::PAGE_SIZE)->limit(Paginator::PAGE_SIZE)->get(),
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }
}
