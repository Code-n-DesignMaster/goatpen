<?php
namespace GoatPen\ViewHelpers;

use LaravelGems\Escape\HTML;

class ActionsNavigation
{
    protected $items = [];

    public function add(string $name, string $uri, string $icon, array $attrs = []): self
    {
        $this->items[] = sprintf(
            '<a href="%s" %s><i class="fa fa-%s"></i> %s</a>',
            HTML::attr($uri),
            implode(' ', array_map(function ($attribute, $value) {
                return sprintf('%s="%s"', HTML::param($attribute), HTML::attr($value));
            }, array_keys($attrs), array_values($attrs))),
            HTML::attr($icon),
            HTML::text($name)
        );

        return $this;
    }

    public function get(): array
    {
        return $this->items;
    }
}
