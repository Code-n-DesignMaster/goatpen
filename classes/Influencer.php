<?php
namespace GoatPen;

use GoatPen\Observers\InfluencerObserver;
use Illuminate\Database\Eloquent\Model;

class Influencer extends Model
{
    const AGE_GROUPS = ['<18', '18-24', '25+'];
    const GENDERS    = ['Male', 'Female', 'Page', 'Both'];

    public $casts = [
        'user_id'           => 'integer',
        'secondary_user_id' => 'integer',
        'name'              => 'string',
        'email'             => 'string',
        'phone'             => 'string',
        'gender'            => 'string',
        'age_group'         => 'string',
        'location'          => 'string',
        'nationality'       => 'string',
        'primary_tag'       => 'string',
        'tags'              => 'array',
    ];

    public $revisionable = [
        'user_id',
        'secondary_user_id',
        'name',
        'email',
        'phone',
        'gender',
        'age_group',
        'location',
        'nationality',
        'primary_tag',
        'tags',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new InfluencerObserver);
    }

	/*
    public function user()
    {
        return $this->belongsTo(User::class);
    }
	 */

	/*
    public function secondaryUser()
    {
        return $this->belongsTo(User::class, 'secondary_user_id');
    }
	 */

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function traits()
    {
        return $this->belongsToMany(InfluencerTrait::class, 'influencer_trait', 'influencer_id', 'trait_id');
    }

    public function comments()
    {
        return $this->hasMany(InfluencerComment::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }

    public function costings(): array
    {
        $costings = [];

        $channels = $this->channels()
            ->join('platforms', 'platforms.id', '=', 'channels.platform_id')
            ->whereNotNull('channels.price')
            ->orderBy('platforms.name', 'asc')
            ->orderBy('channels.price', 'asc')
            ->get();

        foreach ($channels as $channel) {
            $costings[] = [
                'platform'   => $channel->platform->name,
                'price'      => $channel->price,
                'negotiable' => $channel->negotiable,
            ];
        }

        return $costings;
    }

    public function totalPosts(): int
    {
        $posts = 0;

        foreach ($this->channels as $channel) {
            $posts += $channel->totalPosts();
        }

        return $posts;
    }

    public function totalCampaigns(): int
    {
        $campaigns = 0;

        foreach ($this->channels as $channel) {
            $campaigns += $channel->totalCampaigns();
        }

        return $campaigns;
    }
}
