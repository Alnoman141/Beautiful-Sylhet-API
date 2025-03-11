<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedZoneCategoryParent extends Model
{
    protected $guarded = [];

    public function category(): HasMany
    {
        return $this->hasMany(RedZoneCategory::class,'red_zone_category_parent_id');
    }
}
