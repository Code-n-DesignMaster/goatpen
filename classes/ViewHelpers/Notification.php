<?php
namespace GoatPen\ViewHelpers;

use LaravelGems\Escape\HTML;

class Notification
{
    protected static $priorities = ['danger', 'warning', 'success'];

    public static function add(string $message = '', string $class = 'success', bool $html = false)
    {
        if (! isset($_SESSION) || empty($message)) {
            return;
        }

        $_SESSION['notifications'][$class][] = ($html ? $message : HTML::text($message));
    }

    public static function render(): string
    {
        if (! isset($_SESSION['notifications'])) {
            return '';
        }

        $html = '';

        foreach (static::$priorities as $class) {
            if (! isset($_SESSION['notifications'][$class])) {
                continue;
            }

            foreach ($_SESSION['notifications'][$class] as $message) {
                $html .= '<div class="alert alert-dismissible alert-' . HTML::attr($class) . '">';
                $html .= '<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>';
                $html .= $message;
                $html .= '</div>';
            }
        }

        unset($_SESSION['notifications']);

        return $html;
    }
}
