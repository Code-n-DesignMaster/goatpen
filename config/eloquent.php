<?php
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => MYSQL_HOST,
    'database'  => MYSQL_DATABASE,
    'username'  => MYSQL_USER,
    'password'  => MYSQL_PASSWORD,
    'prefix'    => 'cli_dash_',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();
