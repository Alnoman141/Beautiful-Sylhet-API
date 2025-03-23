<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantCategory extends Model
{
    protected $guarded = [];

    public function parent():BelongsTo
    {
        return $this->belongsTo(RestaurantCategoryParent::class, 'restaurant_category_parent_id');
    }
}
