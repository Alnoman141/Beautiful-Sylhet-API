<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class BeachVolunteer extends Model
{
    protected $guarded = [];
    public function parent() : BelongsTo
    {
        return $this->belongsTo(BeachVolunteerParent::class, 'beach_volunteer_parent_id');
    }
    public function spot() : HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, BeachVolunteerParent::class, 'id', 'spot_parent_id','beach_volunteer_parent_id','spot_parent_id');
    }
}
