<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Locker extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LockerParent::class,'locker_parent_id');
    }

    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(SpotParent::class, LockerParent::class, 'id', 'id', 'locker_parent_id', 'spot_parent_id');
    }
}
