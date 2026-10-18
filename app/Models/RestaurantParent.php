<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'restaurant_parent_id');
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }

    public function ratings(): HasMany
{
    return $this->hasMany(RestaurantRating::class, 'restaurant_parent_id')->where('status', 1);
}
}
