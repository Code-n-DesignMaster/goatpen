<?php
namespace GoatPen\Utilities;

class Text
{
    public static function asUri(string $text): string
    {
        return preg_replace('/[^a-z\d]/', '', strtolower($text));
    }
}
