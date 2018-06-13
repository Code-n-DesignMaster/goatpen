<?php
namespace GoatPen;

use GoatPen\Observers\CampaignObserver;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public $casts = [
        'name'         => 'string',
        'client'       => 'string',
        'budget'       => 'double',
        'tags'         => 'array',
        'deliverables' => 'json',
    ];

    public $revisionable = [
        'name',
        'client',
        'budget',
        'tags',
        'deliverables',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new CampaignObserver);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function stats()
    {
        return $this->hasMany(Stat::class);
    }

    public function comments()
    {
        return $this->hasMany(CampaignComment::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }

    public function remainingBudget(): float
    {
        $cost = 0;

        foreach ($this->posts as $post) {
            $cost += ($post->channel->price ?: 0);
        }

        foreach ($this->stats as $stat) {
            $cost += ($stat->total_cost ?: 0);
        }

        return ($this->budget - $cost);
    }

    public function remainingDeliverables(): array
    {
        $remaining = ($this->deliverables ?? []);

        if (empty($remaining)) {
            return [];
        }

        $channelStats = [];

        foreach ($this->posts as $post) {
            if (! in_array($post->channel->getKey(), $channelStats)) {
                $channelStats[$post->channel->getKey()] = $post->channel->campaignStats();
            }
        }

        foreach ($this->stats as $stat) {
            if (! in_array($stat->channel->getKey(), $channelStats)) {
                $channelStats[$stat->channel->getKey()] = $stat->channel->campaignStats();
            }
        }

        foreach ($channelStats as $stats) {
            foreach (array_keys($remaining) as $id) {
                if (array_key_exists($id, $stats)) {
                    $remaining[$id] -= $stats[$id];
                }
            }
        }

        return $remaining;
    }
}
