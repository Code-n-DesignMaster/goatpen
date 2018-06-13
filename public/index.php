<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

define('ROOT_DIR',      __DIR__ . '/..');
define('CONFIG_DIR',    ROOT_DIR . '/config');
define('PUBLIC_DIR',    ROOT_DIR . '/public');
define('TEMPLATES_DIR', ROOT_DIR . '/templates');
define('UPLOADS_DIR',   ROOT_DIR . '/uploads');
define('VENDOR_DIR',    ROOT_DIR . '/vendor');

require_once ROOT_DIR . '/bootstrap.php';

$app->get('/', function (Request $request, Response $response, $args) {
    return $response->withRedirect('/dashboard');
});

$app->group('/dashboard', function(){
	$this->get('', 'GoatPen\Controllers\DashboardController:indexAction');
    $this->get('/{slug:[\da-zA-Z-]{0,256}}', 'GoatPen\Controllers\CampaignController:getDashboardAction');
});//group:dashboard

$app->group('/login', function () {
    $this->get('', 'GoatPen\Controllers\AuthenticationController:loginAction');
    $this->post('', 'GoatPen\Controllers\AuthenticationController:emailAction');
    $this->get('/{token:[\da-f]{40}}', 'GoatPen\Controllers\AuthenticationController:processLoginAction');
});

$app->get('/logout', 'GoatPen\Controllers\AuthenticationController:logoutAction');

$app->get('/sheets-reload', 'GoatPen\Controllers\SheetsController:reloadDataAction');

$app->group('/users', function () {
    $this->get('', 'GoatPen\Controllers\UserController:listAction');

    $this->get('/new', 'GoatPen\Controllers\UserController:newAction');
    $this->post('/new', 'GoatPen\Controllers\UserController:saveAction');

    $this->group('/{id:[\d]+}', function () {
        $this->get('/history', 'GoatPen\Controllers\HistoryController:userAction');

        $this->group('/edit', function () {
            $this->get('', 'GoatPen\Controllers\UserController:detailsAction');
            $this->post('', 'GoatPen\Controllers\UserController:saveAction');
        });

        $this->group('/delete', function () {
            $this->get('', 'GoatPen\Controllers\UserController:confirmDeleteAction');
            $this->post('', 'GoatPen\Controllers\UserController:deleteAction');
        });
    });
});

$app->get('/js/csrf.js', 'GoatPen\Controllers\CsrfController:jsAction');

$app->run();
