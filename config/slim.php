<?php
use GoatPen\ViewHelpers\Notification;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Views\PhpRenderer;

$app       = new App;
$container = $app->getContainer();

$container['renderer'] = new PhpRenderer(TEMPLATES_DIR);

$container['csrf'] = function ($container) {
    return (new Guard)->setPersistentTokenMode(true);
};

$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        if (ENV === 'dev') {
            echo $exception->getMessage();
            Notification::add($exception->getMessage(), 'danger');
        }

        return $container['renderer']->render($response->withStatus(500), '/errors/500.phtml');
    };
};
