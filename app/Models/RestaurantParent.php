<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantParent extends Model
{
    protected $guarded = [];

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'restaurant_parent_id');
    }
}
