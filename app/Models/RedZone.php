<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class RedZone extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RedZoneParent::class, 'red_zone_parent_id');
    }

    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, RedZoneParent::class, 'id', 'spot_parent_id','red_zone_parent_id','spot_parent_id');
    }

    public function category(): HasOneThrough
    {
        return $this->hasOneThrough(RedZoneCategory::class, RedZoneParent::class, 'id', 'red_zone_category_parent_id','red_zone_parent_id','red_zone_category_parent_id');
    }
}
