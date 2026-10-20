<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PoliceParent extends Model
{
    protected $guarded = [];

    public function police(): HasMany
    {
        return $this->hasMany(Police::class, 'police_parent_id');
    }
    public function translate(): HasOne
    {
        return $this->hasOne(Police::class,'police_parent_id');
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(SpotParent::class,'spot_parent_id');
    }

    // updazila relation
    public function upazila(): BelongsTo 
    {
        return $this->belongsTo(Upazila::class,'upazila_id');
    }
}
