<?php
namespace GoatPen\ViewHelpers;

use GoatPen\Influencer;

class Tag
{
    public static function all()
    {
        $tags = [];

        $sets = Influencer::select('tags')
            ->distinct()
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->toArray();

        foreach ($sets as $set) {
            foreach ($set as $tag) {
                if (! in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        sort($tags);

        return $tags;
    }
}
