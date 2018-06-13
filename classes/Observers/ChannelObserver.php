<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Channel, Revision};

class ChannelObserver
{
    public function saving(Channel $channel)
    {
        if ($channel->isDirty('url')) {
            $channel->identifier = null;
            $channel->username   = null;
            $channel->error      = null;
        }

        $metrics = ($channel->metrics ?: []);

        foreach ($metrics as $id => $value) {
            $metrics[$id] = (string) $value;
        }

        $channel->metrics = $metrics;
    }

    public function created(Channel $channel)
    {
        Revision::log($channel, 'created_at');
    }

    public function updated(Channel $channel)
    {
        foreach (ModelDiff::asArray($channel) as $key => $changes) {
            Revision::log($channel, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(Channel $channel)
    {
        foreach ($channel->posts as $post) {
            $post->delete();
        }
    }

    public function deleted(Channel $channel)
    {
        Revision::log($channel, 'deleted_at');
    }
}
