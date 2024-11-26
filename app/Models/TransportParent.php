<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransportParent extends Model
{
    protected $guarded = [];

    public function transports(): HasMany
    {
        return $this->hasMany(Transport::class, 'transport_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(Transport::class, 'transport_parent_id');
    }
}
