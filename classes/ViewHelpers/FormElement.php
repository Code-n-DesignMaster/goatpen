<?php
namespace GoatPen\ViewHelpers;

use LaravelGems\Escape\HTML;

class FormElement
{
    public static function input(array $args = [])
    {
        $html = '<input';

        foreach ($args as $key => $val) {
            if ($val === false) {
                continue;
            }

            $html .= ' ' . $key;

            if ($val !== true) {
                $html .= '="' . HTML::attr($val) . '"';
            }
        }

        $html .= '>';

        return $html;
    }
}
