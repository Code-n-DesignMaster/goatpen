<?php
namespace GoatPen;

use GoatPen\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $casts = [
        'name'  => 'string',
        'email' => 'string',
        'campaign' => 'string',
        'owner' => 'boolean',
    ];

    public $revisionable = [
        'name',
        'email',
        'owner',
        'campaign',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new UserObserver);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function token()
    {
        return $this->hasOne(UserToken::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }
}
