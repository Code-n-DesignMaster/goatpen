<?php
namespace GoatPen;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    const TOKEN_EXPIRY_MINUTES = 5;

    protected $casts = [
        'user_id' => 'integer',
        'token'   => 'string',
    ];

    public function hasExpired(): bool
    {
        return ($this->created_at->diffInMinutes(Carbon::now()) > static::TOKEN_EXPIRY_MINUTES);
    }

    public function belongsToUser(string $email): bool
    {
        $user = User::query()
            ->where('email', '=', $email)
            ->first();

        if (! $user) {
            return false;
        }

        if ($user->token === null) {
            return false;
        }

        return ($this->token === $user->token->token);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
