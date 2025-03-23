<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantCategoryParent extends Model
{
    protected $guarded = [];

    public function categories(): HasMany
    {
        return $this->hasMany(RestaurantCategory::class, 'restaurant_category_parent_id');
    }
}
