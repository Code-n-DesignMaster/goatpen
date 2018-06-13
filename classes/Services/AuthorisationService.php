<?php
namespace GoatPen\Services;

class AuthorisationService
{
    // https://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html
    const WHITELIST = '~^(
        / | 
        /j/csrf.js | 
        /login | 
        /login/[a-zA-Z0-9]{40} | 
        /logout
    )$~x';

    public static function routeIsWhiteListed(string $route): bool
    {
        return (preg_match(static::WHITELIST, $route) === 1);
    }
}
