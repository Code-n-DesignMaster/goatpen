<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{Metric, Session};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class MetricController
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

        return $this->renderer->render($response, '/metrics/list.phtml', [
            'metrics' => Metric::orderBy('name', 'asc')->get(),
        ]);
    }

    public function newAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        return $this->renderer->render($response, '/metrics/details.phtml', [
            'metric'       => new Metric,
            'platform_ids' => [],
            'csrf'         => $this->csrf,
        ]);
    }

    public function detailsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $metric = Metric::findOrFail($args['id']);

            return $this->renderer->render($response, '/metrics/details.phtml', [
                'metric'    => $metric,
                'platform_ids' => $metric->platforms()->allRelatedIds()->toArray(),
                'csrf'         => $this->csrf,
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
            return $this->renderer->render($response, '/metrics/delete.phtml', [
                'metric' => Metric::findOrFail($args['id']),
                'csrf'      => $this->csrf,
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
            $metric = (isset($args['id']) ? Metric::findOrFail($args['id']) : new Metric);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $metric->name      = $request->getParsedBodyParam('name');
            $metric->type      = $request->getParsedBodyParam('type');
            $metric->scope     = $request->getParsedBodyParam('scope');
            $metric->stats     = ($request->getParsedBodyParam('stats', false) !== false);
            $metric->automated = ($request->getParsedBodyParam('automated', false) !== false);

            if (strlen($metric->name) === 0) {
                throw new ValidationException('Please enter a name');
            }

            if (! in_array($metric->type, Metric::TYPES)) {
                throw new ValidationException('Please choose a type');
            }

            if (! in_array($metric->scope, Metric::SCOPES)) {
                throw new ValidationException('Please choose a scope');
            }

            $metric->save();

            $metric->platforms()->sync($request->getParsedBodyParam('platform_id', []));

            Notification::add(sprintf('Metric \'%s\' has been saved', $metric->name), 'success');

            return $response->withRedirect('/metrics');
        } catch (ValidationException $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response, '/metrics/details.phtml', [
                'metric'    => $metric,
                'platform_ids' => $metric->platforms()->allRelatedIds()->toArray(),
                'csrf'         => $this->csrf,
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
            $metric = (isset($args['id']) ? Metric::findOrFail($args['id']) : new Metric);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $metric->delete();

            Notification::add(sprintf('Metric \'%s\' has been deleted', $metric->name), 'success');

            return $response->withRedirect('/metrics');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
