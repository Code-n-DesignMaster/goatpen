<?php
namespace GoatPen;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    const IMPORT_CAMPAIGN_CONTENT = 1;
    const IMPORT_CAMPAIGN_STATS   = 2;

    protected $casts = [
        'name'   => 'string',
        'script' => 'string',
    ];
}
