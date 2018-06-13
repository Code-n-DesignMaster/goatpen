<?php
namespace GoatPen;

use GoatPen\Observers\PlatformObserver;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    const BLOG_ID              = 11;
    const FACEBOOK_ID          = 1;
    const FACEBOOK_LIVE_ID     = 10;
    const INSTAGRAM_ID         = 5;
    const INSTAGRAM_STORIES_ID = 2;
    const LIVELY_ID            = 9;
    const MUSICALLY_ID         = 3;
    const SNAPCHAT_ID          = 4;
    const TWITCH_ID            = 8;
    const TWITTER_ID           = 6;
    const YOUTUBE_ID           = 7;

    public $casts = [
        'name' => 'string',
    ];

    public $revisionable = [
        'name',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new PlatformObserver);
    }

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function metrics()
    {
        return $this->belongsToMany(Metric::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }
}
