<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeaEstateParent extends Model
{
    protected $guarded = [];

    public function teaEstates(): HasMany
    {
        return $this->hasMany(
            TeaEstate::class,
            'tea_estate_parent_id'
        );
    }
}
