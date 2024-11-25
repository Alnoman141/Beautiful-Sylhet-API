<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LockerParent extends Model
{
    protected $guarded = [];

    public function spot(): BelongsTo
    {
        return $this->belongsTo(SpotParent::class,'spot_parent_id');
    }

    public function lockers(): HasMany
    {
        return $this->hasMany(Locker::class,'locker_parent_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(LockerImage::class,'locker_parent_id');
    }
}
