<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{InfluencerTrait, Session};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class InfluencerTraitController
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
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        return $this->renderer->render($response, '/traits/list.phtml', [
            'traits' => InfluencerTrait::query()->orderBy('name', 'asc')->get(),
        ]);
    }

    public function newAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        return $this->renderer->render($response, '/traits/details.phtml', [
            'trait' => new InfluencerTrait,
            'csrf'  => $this->csrf,
        ]);
    }

    public function detailsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $trait = InfluencerTrait::findOrFail($args['id']);

            return $this->renderer->render($response, '/traits/details.phtml', [
                'trait' => $trait,
                'csrf'  => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function confirmDeleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            return $this->renderer->render($response, '/traits/delete.phtml', [
                'trait' => InfluencerTrait::findOrFail($args['id']),
                'csrf'  => $this->csrf,
            ]);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }
    }

    public function saveAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $trait = (isset($args['id']) ? InfluencerTrait::findOrFail($args['id']) : new InfluencerTrait);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $trait->name        = $request->getParsedBodyParam('name');
            $trait->description = $request->getParsedBodyParam('description');

            if (strlen($trait->name) === 0) {
                throw new ValidationException('Please enter a name');
            }

            $trait->save();

            Notification::add(sprintf('Trait \'%s\' has been saved', $trait->name), 'success');

            return $response->withRedirect('/traits');
        } catch (ValidationException $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response, '/traits/details.phtml', [
                'trait' => $trait,
                'csrf'  => $this->csrf,
            ]);
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }

    public function deleteAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $trait = (isset($args['id']) ? InfluencerTrait::findOrFail($args['id']) : new InfluencerTrait);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $trait->delete();

            Notification::add(sprintf('Trait \'%s\' has been deleted', $trait->name), 'success');

            return $response->withRedirect('/traits');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
