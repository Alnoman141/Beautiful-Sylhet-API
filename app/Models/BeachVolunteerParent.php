<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeachVolunteerParent extends Model
{
    protected $guarded = [];

    public function volunteer() : HasMany
    {
        return $this->hasMany(BeachVolunteer::class, 'beach_volunteer_parent_id');
    }

    public function spot() : BelongsTo
    {
        return $this->belongsTo(SpotParent::class, 'spot_parent_id');
    }
}
