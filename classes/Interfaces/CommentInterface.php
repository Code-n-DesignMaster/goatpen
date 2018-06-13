<?php
namespace GoatPen\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface CommentInterface
{
    public function user(): BelongsTo;
}
