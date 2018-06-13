<?php
namespace GoatPen\Services;

class DemographicsService
{
    public static function sanitise(array $demographics): array
    {
        $demographics = array_filter($demographics, function ($k, $v) { return ($k && $v); }, ARRAY_FILTER_USE_BOTH);
        ksort($demographics);

        return $demographics;
    }
}
