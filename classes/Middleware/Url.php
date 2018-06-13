<?php
namespace GoatPen\Middleware;

use GoatPen\Formatters\Url as UrlFormatter;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class Url
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        UrlFormatter::setUrl($request->getUri()->getPath());

        return $next($request, $response);
    }
}
