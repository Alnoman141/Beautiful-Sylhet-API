<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelParent extends Model
{
    protected $guarded = [];

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'hotel_parent_id');
    }
}
