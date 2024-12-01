<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class RedZoneParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
    public function reds(): HasMany
    {
        return $this->hasMany(RedZone::class,'red_zone_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(RedZone::class,'red_zone_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class,SpotParent::class,'id','spot_parent_id','spot_parent_id');
    }
}
