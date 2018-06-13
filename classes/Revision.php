<?php
namespace GoatPen;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $casts = [
        'revisionable_type' => 'string',
        'revisionable_id'   => 'integer',
        'user_id'           => 'integer',
        'key'               => 'string',
        'old_value'         => 'string',
        'new_value'         => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revisionable()
    {
        return $this->belongsTo($this->revisionable_type);
    }

    public function format(string $value = null): string
    {
        if ($value === null) {
            return '';
        }

        switch ($this->revisionable->casts[$this->key] ?? null) {
            case 'array':
                return implode(', ', json_decode($value, true));
            default:
                return (string) $value;
        }
    }

    public static function log($object, string $key, $oldValue = null, $newValue = null)
    {
        $revision                    = new Revision;
        $revision->revisionable_type = $object->getMorphClass();
        $revision->revisionable_id   = $object->getKey();
        $revision->key               = $key;
        $revision->old_value         = $oldValue;
        $revision->new_value         = $newValue;
        $revision->user()->associate(Session::getUser());
        $revision->save();
    }
}
