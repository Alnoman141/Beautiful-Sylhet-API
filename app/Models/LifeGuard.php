<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class LifeGuard extends Model
{
    protected $guarded = [];
    public function parent() : BelongsTo
    {
        return $this->belongsTo(LifeGuardParent::class, 'life_guard_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, LifeGuardParent::class, 'id', 'spot_parent_id','life_guard_parent_id','spot_parent_id');
    }
}
