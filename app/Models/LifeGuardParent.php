<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LifeGuardParent extends Model
{
    protected $guarded = [];
    public function guards() : HasMany
    {
        return $this->hasMany(LifeGuard::class, 'lifeguard_parent_id');
    }

    public function translate() : HasOne
    {
        return $this->hasOne(LifeGuard::class, 'lifeguard_parent_id');
    }
    public function spot() : BelongsTo
    {
        return $this->belongsTo(SpotParent::class, 'spot_parent_id');
    }
}
