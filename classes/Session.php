<?php
namespace GoatPen;

use GoatPen\Services\AuthorisationService;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $casts = [
        'user_id'    => 'integer',
        'user_agent' => 'string',
        'ip'         => 'integer',
    ];

    protected static $user;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setIpAttribute(string $value)
    {
        $this->attributes['ip'] = ip2long($value);
    }

    public function isInactive(): bool
    {
        return strtotime($this->created_at) < strtotime('3 months ago');
    }

    public static function getUser()
    {
        if (! static::$user) {
            if (! isset($_SESSION['user_id'])) {
                return;
            }

            $user = User::find($_SESSION['user_id']);

            if (! $user) {
                return;
            }

            static::$user = $user;
        }

        return static::$user;
    }

    public static function rememberRequestedUri(string $uri)
    {
        if (! AuthorisationService::routeIsWhiteListed($uri)) {
            $_SESSION['uri'] = $uri;
        }
    }

    public static function setUser($user)
    {
        static::$user = $user;
    }

    public static function startSession(User $user)
    {
        $_SESSION['user_id'] = $user->id;
        session_regenerate_id(true);
    }

    public static function destroySession()
    {
        // Unset all of the session variables.
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                (time() - 42000),
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_regenerate_id();
        session_destroy();
    }
}
