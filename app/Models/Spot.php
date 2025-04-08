<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spot extends Model
{
    use LogsModelChanges;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SpotParent::class,'spot_parent_id');
    }
}
