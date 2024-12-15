<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Event extends Model
{
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(EventParent::class, 'event_parent_id');
    }
    //TODO @fa 2024-12-03 spot
    public function spotParent(): HasOneThrough
    {
        return $this->hasOneThrough(SpotParent::class, EventParent::class, 'id', 'id', 'event_parent_id', 'spot_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, EventParent::class, 'id', 'spot_parent_id','event_parent_id','spot_parent_id');
    }
}
