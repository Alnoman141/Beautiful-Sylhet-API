<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransportTypeParent extends Model
{
    protected $guarded = [];

    public function types(): HasMany
    {
        return $this->hasMany(TransportType::class, 'transport_type_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(TransportType::class, 'transport_type_parent_id');
    }
}
