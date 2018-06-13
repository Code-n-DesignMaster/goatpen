<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Demographic, Revision};

class DemographicObserver
{
    public function created(Demographic $demographic)
    {
        Revision::log($demographic, 'created_at');
    }

    public function updated(Demographic $demographic)
    {
        foreach (ModelDiff::asArray($demographic) as $key => $changes) {
            Revision::log($demographic, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleted(Demographic $demographic)
    {
        Revision::log($demographic, 'deleted_at');
    }
}
