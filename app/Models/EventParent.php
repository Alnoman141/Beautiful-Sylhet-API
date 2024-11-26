<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventParent extends Model
{
    protected $guarded = [];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(Event::class, 'event_parent_id');
    }
}
