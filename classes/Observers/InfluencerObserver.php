<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Influencer, Revision};

class InfluencerObserver
{
    public function saving(Influencer $influencer)
    {
        if ($influencer->user_id === $influencer->secondary_user_id) {
            $influencer->secondary_user_id = null;
        }
    }

    public function created(Influencer $influencer)
    {
        Revision::log($influencer, 'created_at');
    }

    public function updated(Influencer $influencer)
    {
        foreach (ModelDiff::asArray($influencer) as $key => $changes) {
            Revision::log($influencer, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(Influencer $influencer)
    {
        foreach ($influencer->channels as $channel) {
            $channel->delete();
        }
    }

    public function deleted(Influencer $influencer)
    {
        Revision::log($influencer, 'deleted_at');
    }
}
