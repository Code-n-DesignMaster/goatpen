<?php
namespace GoatPen;

use GoatPen\Observers\StatObserver;
use Illuminate\Database\Eloquent\{Builder, Model};

class Stat extends Model
{
    public $casts = [
        'campaign_id' => 'integer',
        'channel_id'  => 'integer',
        'link'        => 'string',
        'metrics'     => 'json',
    ];

    public $revisionable = [
        'campaign_id',
        'channel_id',
        'link',
        'metrics',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new StatObserver);
    }

    public function getLinkAttribute($value): string
    {
        return (strlen($value) > 0 ? 'http://' . $value : '');
    }

    public function setLinkAttribute($value)
    {
        $this->attributes['link'] = preg_replace('#^https?://#', '', $value);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }

    public function scopeWithMetric(Builder $query, Metric $metric): Builder
    {
        return $query->where('stats.metrics', 'LIKE', '%"' . $metric->getKey() . '":');
    }
}
