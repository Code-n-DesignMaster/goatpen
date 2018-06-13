<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Platform, Revision};

class PlatformObserver
{
    public function created(Platform $platform)
    {
        Revision::log($platform, 'created_at');
    }

    public function updated(Platform $platform)
    {
        foreach (ModelDiff::asArray($platform) as $key => $changes) {
            Revision::log($platform, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(Platform $platform)
    {
        $platform->metrics()->detach();

        foreach ($platform->channels as $channel) {
            $channel->delete();
        }
    }

    public function deleted(Platform $platform)
    {
        Revision::log($platform, 'deleted_at');
    }
}
