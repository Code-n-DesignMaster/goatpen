<?php
namespace GoatPen\ViewHelpers;

use Iso3166\Codes;

class CountryCodes
{
    public static function countries()
    {
        $popular = [
            'GB' => Codes::$countries['GB'],
            'US' => Codes::$countries['US'],
        ];

        $countries = array_merge($popular, Codes::$countries);

        return $countries;
    }

    public static function codes()
    {
        return array_keys(static::countries());
    }
}
