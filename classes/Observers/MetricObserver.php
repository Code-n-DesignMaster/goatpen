<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Channel, Metric, Post, Revision};

class MetricObserver
{
    public function created(Metric $metric)
    {
        Revision::log($metric, 'created_at');
    }

    public function updated(Metric $metric)
    {
        foreach (ModelDiff::asArray($metric) as $key => $changes) {
            Revision::log($metric, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(Metric $metric)
    {
        $metric->platforms()->detach();

        foreach (Channel::query()->withMetric($metric)->cursor() as $channel) {
            $metrics = $channel->metrics;
            unset($metrics[$metric->getKey()]);
            $channel->metrics = $metrics;
            $channel->save();
        }

        foreach (Post::query()->withMetric($metric)->cursor() as $post) {
            $metrics = $post->metrics;
            unset($metrics[$metric->getKey()]);
            $post->metrics = $metrics;
            $post->save();
        }
    }

    public function deleted(Metric $metric)
    {
        Revision::log($metric, 'deleted_at');
    }
}
