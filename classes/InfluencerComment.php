<?php
namespace GoatPen;

use GoatPen\Interfaces\CommentInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluencerComment extends Model implements CommentInterface
{
    protected $casts = [
        'comment' => 'string',
    ];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
