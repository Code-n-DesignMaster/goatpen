<?php
define('ROOT_DIR',    __DIR__ . '/..');
define('CONFIG_DIR',  ROOT_DIR . '/config');
define('TASKS_DIR',   ROOT_DIR . '/tasks');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('VENDOR_DIR',  ROOT_DIR . '/vendor');

require_once VENDOR_DIR . '/autoload.php';
require_once CONFIG_DIR . '/config.php';
require_once CONFIG_DIR . '/feature-flags.php';
require_once CONFIG_DIR . '/eloquent.php';

use GoatPen\Queue;

if (Queue::query()->running()->count() > 0) {
    if (ENV === 'dev') {
        echo 'There is a task running' . PHP_EOL;
    }

    return;
}

$queued = Queue::query()
    ->queued()
    ->orderBy('queued', 'asc')
    ->orderBy('id', 'asc');

if ($queued->count() === 0) {
    if (ENV === 'dev') {
        echo 'There are no queued tasks' . PHP_EOL;
    }

    return;
}

$queued->first()->run();
