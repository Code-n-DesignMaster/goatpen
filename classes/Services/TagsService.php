<?php
namespace GoatPen\Services;

class TagsService
{
    public static function sanitise(array $tags, string $omit = null): array
    {
        if (! is_null($omit)) {
            $tags = array_diff($tags, [$omit]);
        }

        $tags = array_filter(array_unique(array_map('trim', array_map('strtoupper', $tags))));
        sort($tags);

        return $tags;
    }
}
