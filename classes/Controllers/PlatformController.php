<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{Platform, Session};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class PlatformController
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

        return $this->renderer->render($response, '/platforms/list.phtml', [
            'platforms' => Platform::orderBy('order', 'asc')->get(),
        ]);
    }

    public function newAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        return $this->renderer->render($response, '/platforms/details.phtml', [
            'platform' => new Platform,
            'csrf'     => $this->csrf,
        ]);
    }

    public function detailsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            return $this->renderer->render($response, '/platforms/details.phtml', [
                'platform' => Platform::findOrFail($args['id']),
                'csrf'     => $this->csrf,
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
            return $this->renderer->render($response, '/platforms/delete.phtml', [
                'platform' => Platform::findOrFail($args['id']),
                'csrf'     => $this->csrf,
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
            $platform = (isset($args['id']) ? Platform::findOrFail($args['id']) : new Platform);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $platform->name = $request->getParsedBodyParam('name');

            if (strlen($platform->name) === 0) {
                throw new ValidationException('Please enter a name');
            }

            $platform->save();

            Notification::add(sprintf('Platform \'%s\' has been saved', $platform->name), 'success');

            return $response->withRedirect('/platforms');
        } catch (ValidationException $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response, '/platforms/details.phtml', [
                'platform' => $platform,
                'csrf'     => $this->csrf,
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
            $platform = (isset($args['id']) ? Platform::findOrFail($args['id']) : new Platform);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $platform->delete();

            Notification::add(sprintf('Platform \'%s\' has been deleted', $platform->name), 'success');

            return $response->withRedirect('/platforms');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }

    public function orderAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $order = 1;

        foreach ($request->getParam('id', []) as $id) {
            $platform = Platform::find($id);

            if (! $platform) {
                continue;
            }

            $platform->order = $order++;
            $platform->save();
        }

        return $response;
    }
}
