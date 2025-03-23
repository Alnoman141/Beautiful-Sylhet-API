<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Restaurant extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RestaurantParent::class, 'restaurant_parent_id');
    }
    public function spot() : HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, RestaurantParent::class, 'id', 'spot_parent_id','restaurant_parent_id','spot_parent_id');
    }
}
