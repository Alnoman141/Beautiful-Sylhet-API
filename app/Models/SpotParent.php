<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class SpotParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
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
    //TODO @fa 2024-11-25 has one translate
    public function translate(): HasOne
    {
        return $this->hasOne(Spot::class,'spot_parent_id');
    }
}
