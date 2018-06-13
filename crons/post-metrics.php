<?php
define('ROOT_DIR',   __DIR__ . '/..');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('VENDOR_DIR', ROOT_DIR . '/vendor');

require_once VENDOR_DIR . '/autoload.php';
require_once CONFIG_DIR . '/config.php';
require_once CONFIG_DIR . '/feature-flags.php';
require_once CONFIG_DIR . '/eloquent.php';

use Carbon\Carbon;
use GoatPen\Post;

$posts = Post::query()
    ->where('posted', '>=', (new Carbon('21 days ago'))->toDateTimeString())
    ->where(function ($query) {
        $query->whereNull('cached_metrics_at')
            ->orWhere('cached_metrics_at', '<', (new Carbon('12 hours ago'))->toDateTimeString());
    })
    ->where('url', '!=', '')
    ->orderBy('cached_metrics_at', 'asc')
    ->limit(100);

foreach ($posts->cursor() as $post) {
    $post->populateMetrics();
    $post->cached_metrics_at = Carbon::now();
    $post->save();
}
