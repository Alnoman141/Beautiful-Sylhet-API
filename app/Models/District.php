<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $guarded = [];

    public function upazila(): HasMany
    {
        return $this->hasMany(Upazila::class,'district_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class,'division_id');
    }
}
