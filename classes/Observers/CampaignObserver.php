<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Campaign, Revision};

class CampaignObserver
{
    public function created(Campaign $campaign)
    {
        Revision::log($campaign, 'created_at');
    }

    public function updated(Campaign $campaign)
    {
        foreach (ModelDiff::asArray($campaign) as $key => $changes) {
            Revision::log($campaign, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(Campaign $campaign)
    {
        foreach ($campaign->posts as $post) {
            $post->delete();
        }

        foreach ($campaign->stats as $stat) {
            $stat->delete();
        }
    }

    public function deleted(Campaign $campaign)
    {
        Revision::log($campaign, 'deleted_at');
    }
}
