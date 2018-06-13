<?php
namespace GoatPen;

use GoatPen\Interfaces\CommentInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignComment extends Model implements CommentInterface
{
    protected $casts = [
        'comment' => 'string',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
