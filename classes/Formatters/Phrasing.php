<?php
namespace GoatPen\Formatters;

class Phrasing
{
    public static function readableList(array $items = [], string $join = 'and'): string
    {
        $items = array_values(array_filter((array) $items, function ($item) {
            return ! (is_null($item) || $item === false || $item === '');
        }));

        if (empty($items)) {
            return '';
        }

        if (count($items) > 1) {
            return implode(', ', array_slice($items, 0, count($items) - 1)) . ' ' . $join . ' ' . $items[count($items) - 1];
        }

        return $items[0];
    }

    public static function plural(int $count = 0, string $singular = '', string $plural = null): string
    {
        return number_format($count) . ' ' . ($count === 1 ? $singular : ($plural ?: $singular . 's'));
    }
}
