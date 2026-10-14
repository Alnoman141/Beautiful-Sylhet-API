<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;

class SpotParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'link' => 'object',
        ];
    }
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

    public function hotels(): HasManyThrough
    {
        return $this->hasManyThrough(Hotel::class, HotelParent::class, 'spot_parent_id', 'hotel_parent_id');
    }
    //has one through hotel
    public function hotel(): HasOneThrough
    {
        return $this->hasOneThrough(Hotel::class, HotelParent::class, 'spot_parent_id', 'hotel_parent_id');
    }

    // has many ratings
    public function ratings(): HasMany 
    {
        return $this->hasMany(SpotRatting::class,'spot_parent_id')->where('status', 1);
    }
}
