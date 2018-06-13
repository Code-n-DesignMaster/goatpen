<?php
namespace GoatPen\Formatters;

class Url
{
    private static $url;

    public static function setUrl($url)
    {
        static::$url = $url;
    }

    public static function getUrl()
    {
        return static::$url;
    }
}
