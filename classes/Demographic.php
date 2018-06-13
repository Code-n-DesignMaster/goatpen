<?php
namespace GoatPen;

use GoatPen\Observers\DemographicObserver;
use GoatPen\ViewHelpers\CountryCodes;
use Illuminate\Database\Eloquent\Model;

class Demographic extends Model
{
    const AGE_GROUP = 1;
    const COUNTRY   = 2;
    const GENDER    = 3;

    public $casts = [
        'name' => 'string',
    ];

    public $revisionable = [
        'name',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new DemographicObserver);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }

    public function groups(): array
    {
        switch ($this->getKey()) {
            case static::AGE_GROUP:
                return ['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'];
            case static::COUNTRY:
                return CountryCodes::countries();
            case static::GENDER:
                return ['Female', 'Male'];
            default:
                return [];
        }
    }
}
