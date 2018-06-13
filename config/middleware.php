<?php
use GoatPen\Middleware\{Authorisation, Url};

$app->add(new Authorisation);
$app->add(new Url);
$app->add($container->get('csrf'));
