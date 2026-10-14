<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class AmusementParkParent extends Model
{

    protected $guarded = [];

    protected $fillable = ['image'];
    
    public function amusementParks()
    {
        return $this->hasMany(AmusementPark::class, 'amusement_park_parent_id');
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? Storage::disk('s3')->url($value)
                : null,
        );
    }
}
