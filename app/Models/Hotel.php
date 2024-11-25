<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Hotel extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(HotelParent::class,'hotel_parent_id');
    }
    //sport parent
    public function spotParent() : HasOneThrough
    {
        return $this->hasOneThrough(SpotParent::class,HotelParent::class,'id','id','hotel_parent_id','spot_parent_id');
    }
    //TODO @fa 2024-11-25 category parent
    public function categoryParent() : HasOneThrough
    {
        return $this->hasOneThrough(HotelCategoryParent::class,HotelParent::class,'id','id','hotel_parent_id','hotel_category_parent_id');
    }
    //TODO @fa 2024-11-25 type parent
    public function typeParent() : HasOneThrough
    {
        return $this->hasOneThrough(HotelTypeParent::class,HotelParent::class,'id','id','hotel_parent_id','hotel_type_parent_id');
    }
}
