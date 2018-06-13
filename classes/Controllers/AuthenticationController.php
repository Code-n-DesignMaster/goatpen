<?php
namespace GoatPen\Controllers;

use Exception;
use GoatPen\ViewHelpers\Notification;
use GoatPen\{Session, User, UserToken};
use Interop\Container\ContainerInterface;
use Mailgun\Mailgun;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class AuthenticationController
{
    protected $csrf;
    protected $renderer;

    public function __construct(ContainerInterface $container)
    {
        $this->csrf     = $container['csrf'];
        $this->renderer = $container['renderer'];
    }

    public function loginAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        if (Session::getUser()) {
            return $response->withRedirect('/dashboard');
        }

        return $this->renderer->render($response, '/login.phtml', [
            'csrf' => $this->csrf,
        ]);
    }

    public function logoutAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        Session::destroySession();

        return $response->withRedirect('/');
    }

    public function processLoginAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $userToken = UserToken::query()
                ->where('token', '=', $args['token'])
                ->first();

            if (! $userToken || ! $userToken->belongsToUser($request->getParam('email'))) {
                throw new Exception('Incorrect email or login token');
            }

            Session::startSession($userToken->user);

            $session             = new Session;
            $session->user_id    = $userToken->user->id;
            $session->user_agent = ($_SERVER['HTTP_USER_AGENT'] ?? null);
            $session->ip         = ($_SERVER['REMOTE_ADDR'] ?? null);
            $session->save();

            return $response->withRedirect($_SESSION['uri'] ?? '/dashboard');
        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');

            return $response->withRedirect('/login');
        } finally {
            if (isset($userToken)) {
                $userToken->delete();
            }
        }
    }

    public function emailAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $user = User::query()
                ->where('email', '=', $request->getParsedBodyParam('email'))
                ->first();

            if (! $user) {
                throw new Exception('There was an error emailing your magic link');
            }

            if ($user->token) {
                $user->token->delete();
            }

            $userToken        = new UserToken;
            $userToken->token = bin2hex(random_bytes(20));
            $userToken->user()->associate($user);
            $userToken->save();

            $domain = (ENV === 'dev' ? 'http://localhost:8082' : 'http://goatclientdashboard.com');

            $text = sprintf(implode("\n\n", [
                'Hi %s,',
                'Click the magic link to login to Goat Pen.',
                '<%s/login/%s?email=%s>',
                '– Goat Pen',
            ]), $user->name, $domain, $userToken->token, $user->email);

            $html = str_replace(
                [
                    '{{TEXT.LEAD}}',
                    '{{TEXT.BODY}}',
                    '{{ACTION.URL}}',
                    '{{ACTION.TEXT}}',
                ],
                [
                    sprintf('Hi %s,', $user->name),
                    'Click the magic link to login to Goat Pen.',
                    sprintf('%s/login/%s?email=%s', $domain, $userToken->token, $user->email),
                    'Login to Goat Pen',
                ],
                file_get_contents(TEMPLATES_DIR . '/emails/transactional.html')
            );

			try {
            Mailgun::create(MAILGUN_KEY)->messages()->send('mail.goatpen.io', [
                'from'    => 'noreply@goatpen.io',
                'to'      => $user->email,
                'subject' => 'Login to Goat Pen',
                'text'    => $text,
                'html'    => $html,
            ]);
            Notification::add('We have emailed you a magic link to login to Goat Pen, which will be active for five minutes', 'success');
			} catch (Exception $xception) {
				Notification::add('Temporary dev: '.$userToken->token, 'success');
			}

        } catch (Exception $exception) {
            Notification::add($exception->getMessage(), 'danger');
        }

        return $response->withRedirect('/login');
    }
}
