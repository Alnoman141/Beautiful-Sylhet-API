<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HotelCategoryParent extends Model
{
    protected $guarded = [];

    public function categories() : HasMany
    {
        return $this->hasMany(HotelCategory::class, 'hotel_category_parent_id');
    }
    //TODO @fa 2024-11-25 translate
    public function translate(): HasOne
    {
        return $this->hasOne(HotelCategory::class, 'hotel_category_parent_id');
    }
}
