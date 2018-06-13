<?php
namespace GoatPen;

use GoatPen\Observers\InfluencerTraitObserver;
use Illuminate\Database\Eloquent\Model;

class InfluencerTrait extends Model
{
    protected $table = 'traits';

    public $casts = [
        'name'        => 'string',
        'description' => 'string',
    ];

    public $revisionable = [
        'name',
        'description',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new InfluencerTraitObserver);
    }

    public function influencers()
    {
        return $this->belongsToMany(Influencer::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }
}
