<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Doctor extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DoctorParent::class, 'doctor_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, DoctorParent::class, 'id', 'spot_parent_id','doctor_parent_id','spot_parent_id');
    }
}
