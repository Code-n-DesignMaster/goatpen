<?php
namespace GoatPen\Formatters;

use GoatPen\Interfaces\CommentInterface;
use Illuminate\Database\Eloquent\Collection;

class CommentResponseFormatter
{
    public static function toArray(CommentInterface $comment, int $user_id): array
    {
        $data = $comment->toArray();

        $data['name']            = $comment->user->name;
        $data['is_current_user'] = ($comment->user_id === $user_id);
        $data['created']         = $comment->created_at->timezone('Europe/London')->toDateTimeString();

        return $data;
    }

    public static function listToArray(Collection $comments, int $user_id): array
    {
        $data = [];

        foreach ($comments as $comment) {
            $data[] = static::toArray($comment, $user_id);
        }

        return $data;
    }
}
