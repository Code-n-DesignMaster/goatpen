<?php
namespace GoatPen\ViewHelpers;

use GoatPen\Metric;

class Number
{
    public static function formatMetric(Metric $metric, string $value): string
    {
        switch ($metric->type) {
            case 'Percent':
                return (float) $value . '%';
                break;
            case 'Money':
                return '£' . number_format($value, 2);
                break;
            default:
                return number_format($value);
                break;
        }
    }
}
