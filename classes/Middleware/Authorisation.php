<?php
namespace GoatPen\Middleware;

use GoatPen\Formatters\Url as UrlFormatter;
use GoatPen\Services\AuthorisationService;
use GoatPen\{Session, User};
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class Authorisation
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (! AuthorisationService::routeIsWhiteListed(UrlFormatter::getUrl())) {
            if (! isset($_SESSION['user_id'])) {
                Session::rememberRequestedUri($_SERVER['REQUEST_URI']);

                return $response->withRedirect('/login');
            }

            Session::setUser(User::find($_SESSION['user_id']));
        }

        return $next($request, $response);
    }
}
