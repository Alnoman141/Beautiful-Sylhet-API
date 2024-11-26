<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(EventParent::class, 'event_parent_id');
    }
}
