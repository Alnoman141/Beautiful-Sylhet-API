<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpotParent extends Model
{
    protected $guarded = [];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => file_exists($value) ? asset($value) : '',
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(SpotImage::class,'spot_parent_id');
    }

    public function spot(): HasMany
    {
        return $this->hasMany(Spot::class,'spot_parent_id');
    }
}
