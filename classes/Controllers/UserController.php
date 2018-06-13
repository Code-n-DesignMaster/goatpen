<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\Exceptions\ValidationException;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{Session, User};
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class UserController
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

        return $this->renderer->render($response, '/users/list.phtml', [
            'users' => User::orderBy('name', 'asc')->get(),
        ]);
    }

    public function newAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        $user = new User;
        $user['campaigns'] = [];

        return $this->renderer->render($response, '/users/details.phtml', [
            'user' => $user,
            'campaigns' => CampaignController::getJSONList(),
            'csrf' => $this->csrf,
        ]);
    }

    public function detailsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $user = User::findOrFail($args['id']);
            $user['campaigns'] = json_decode($user['campaign']) ?? [];

            return $this->renderer->render($response, '/users/details.phtml', [
                'user' => $user,
                'campaigns' => CampaignController::getJSONList(),
                'csrf' => $this->csrf,
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
            $user = User::findOrFail($args['id']);

            if ($user->owner) {
                return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
            }
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        return $this->renderer->render($response, '/users/delete.phtml', [
            'user' => $user,
            'csrf' => $this->csrf,
        ]);
    }

    public function saveAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (! Session::getUser()->owner) {
            return $this->renderer->render($response->withStatus(403), '/errors/403.phtml');
        }

        try {
            $user = (isset($args['id']) ? User::findOrFail($args['id']) : new User);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $user->name  = $request->getParsedBodyParam('name');
            $user->email = $request->getParsedBodyParam('email');
            $user->campaign = json_encode($request->getParsedBodyParam('campaign_slug'));
            $user->owner = ($request->getParsedBodyParam('owner', false) !== false);

            if (strlen($user->name) === 0) {
                throw new ValidationException('Please enter a name');
            }

            if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException('Please enter a valid email address');
			}

			//TODO: validate campaign exists

            $emailCheck = User::where('email', '=', $user->email);

            if ($user->exists) {
                $emailCheck->where('id', '!=', $user->id);
            }

            if ($emailCheck->count() > 0) {
                throw new ValidationException(sprintf('A user with email \'%s\' already exists', $user->email));
            }

            $user->save();

            Notification::add(sprintf('User \'%s\' has been saved', $user->name), 'success');

            return $response->withRedirect('/users');
        } catch (ValidationException $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response, '/users/details.phtml', [
                'user' => $user,
                'csrf' => $this->csrf,
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
            $user = (isset($args['id']) ? User::findOrFail($args['id']) : new User);
        } catch (Exception $exception) {
            return $this->renderer->render($response->withStatus(404), '/errors/404.phtml');
        }

        try {
            $user->delete();

            Notification::add(sprintf('User \'%s\' has been deleted', $user->name), 'success');

            return $response->withRedirect('/users');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $this->renderer->render($response->withStatus(500), '/errors/500.phtml');
        }
    }
}
