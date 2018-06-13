<?php
namespace GoatPen\Controllers;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class CsrfController
{
    protected $csrf;

    public function __construct(ContainerInterface $container)
    {
        $this->csrf = $container['csrf'];
    }

    public function jsAction(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response->getBody()->write(sprintf(
            "$.ajaxSetup({ data: { '%s': '%s', '%s': '%s' } });",
            $this->csrf->getTokenNameKey(),
            $this->csrf->getTokenName(),
            $this->csrf->getTokenValueKey(),
            $this->csrf->getTokenValue()
        ));

        return $response->withHeader('Content-type', 'application/javascript');
    }
}
