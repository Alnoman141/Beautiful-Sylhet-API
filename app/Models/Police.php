<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Police extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PoliceParent::class, 'police_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, PoliceParent::class, 'id', 'spot_parent_id','police_parent_id','spot_parent_id');
    }
}
