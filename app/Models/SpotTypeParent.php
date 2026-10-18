<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SpotTypeParent extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(
            SpotType::class,
            'spot_type_parent_id'
        );
    }

    public function translate(): HasOne
    {
        return $this->hasOne(
            SpotType::class,
            'spot_type_parent_id'
        );
    }
}