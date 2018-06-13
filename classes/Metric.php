<?php
namespace GoatPen;

use GoatPen\Observers\MetricObserver;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    const TYPES  = ['Number', 'Money', 'Percent'];
    const SCOPES = ['Influencer', 'Campaign'];

    const PAGE_LIKES_ID              = 1;
    const FOLLOWERS_ID               = 2;
    const SUBSCRIBERS_ID             = 3;
    const TOTAL_ENGAGEMENTS_ID       = 6;
    const POST_REACTIONS_ID          = 7;
    const POST_COMMENTS_ID           = 8;
    const PROFILE_ENGAGEMENT_RATE_ID = 10;
    const COST_PER_CLICK_ID          = 11;
    const LINK_CLICKS_ID             = 12;
    const IMPRESSIONS_ID             = 13;
    const COST_PER_IMPRESSION_ID     = 14;
    const VIDEO_VIEWS_ID             = 15;
    const RETWEETS_ID                = 16;
    const VIEWTHROUGH_RATE_ID        = 19;
    const POST_LIKES_ID              = 20;
    const COST_PER_VIEW_ID           = 23;
    const COST_PER_ENGAGEMENT_ID     = 24;
    const DEPOSITORS_ID              = 25;
    const AVERAGE_VIDEO_VIEWS_ID     = 26;
    const AVERAGE_ENGAGEMENTS_ID     = 27;
    const PRICE_PAID_ID              = 28;
    const POST_ENGAGEMENT_RATE_ID    = 29;
    const CUMULATIVE_FOLLOWING_ID    = 30;
    const REGISTRATIONS_ID           = 33;
    const INSTALLS_ID                = 34;
    const SHARES_ID                  = 35;
    const CUMULATIVE_SUBSCRIBERS_ID  = 40;
    const COST_PER_INSTALL_ID        = 41;
    const COST_PER_REGISTRATION_ID   = 42;
    const COST_PER_DEPOSITOR_ID      = 43;

    const FOLLOWINGS = [
        self::FOLLOWERS_ID,
        self::PAGE_LIKES_ID,
        self::SUBSCRIBERS_ID,
    ];

    public $casts = [
        'name'      => 'string',
        'type'      => 'string',
        'scope'     => 'string',
        'stats'     => 'boolean',
        'automated' => 'boolean',
    ];

    public $revisionable = [
        'name',
        'type',
        'scope',
        'stats',
        'automated',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new MetricObserver);
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }
}
