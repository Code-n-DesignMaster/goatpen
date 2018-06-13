<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{Demographic, Session};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class DemographicController
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

        return $this->renderer->render($response, '/demographics/list.phtml', [
            'demographics' => Demographic::orderBy('name', 'asc')->get(),
        ]);
    }

    public function newAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        return $this->renderer->render($response, '/demographics/details.phtml', [
            'demographic' => new Demographic,
            'csrf'        => $this->csrf,
        ]);
    }

    public function detailsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            return $this->renderer->render($response, '/demographics/details.phtml', [
                'demographic' => Demographic::findOrFail($args['id']),
                'csrf'        => $this->csrf,
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
            return $this->renderer->render($response, '/demographics/delete.phtml', [
                'demographic' => Demographic::findOrFail($args['id']),
                'csrf'        => $this->csrf,
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
            $demographic = (isset($args['id']) ? Demographic::findOrFail($args['id']) : new Demographic);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $demographic->name = $request->getParsedBodyParam('name');

            if (strlen($demographic->name) === 0) {
                throw new ValidationException('Please enter a name');
            }

            $demographic->save();

            Notification::add(sprintf('Demographic \'%s\' has been saved', $demographic->name), 'success');

            return $response->withRedirect('/demographics');
        } catch (ValidationException $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response, '/demographics/details.phtml', [
                'demographic' => $demographic,
                'csrf'        => $this->csrf,
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
            $demographic = (isset($args['id']) ? Demographic::findOrFail($args['id']) : new Demographic);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $demographic->delete();

            Notification::add(sprintf('Demographic \'%s\' has been deleted', $demographic->name), 'success');

            return $response->withRedirect('/demographics');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
