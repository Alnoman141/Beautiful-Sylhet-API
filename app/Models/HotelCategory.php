<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelCategory extends Model
{
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(HotelCategoryParent::class,'hotel_category_parent_id');
    }
}
