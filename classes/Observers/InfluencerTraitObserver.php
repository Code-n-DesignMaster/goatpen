<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{InfluencerTrait, Revision};

class InfluencerTraitObserver
{
    public function created(InfluencerTrait $trait)
    {
        Revision::log($trait, 'created_at');
    }

    public function updated(InfluencerTrait $trait)
    {
        foreach (ModelDiff::asArray($trait) as $key => $changes) {
            Revision::log($trait, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(InfluencerTrait $trait)
    {
        $trait->influencers()->detach();
    }

    public function deleted(InfluencerTrait $trait)
    {
        Revision::log($trait, 'deleted_at');
    }
}
