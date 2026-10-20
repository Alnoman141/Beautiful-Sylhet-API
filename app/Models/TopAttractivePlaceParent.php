<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class TopAttractivePlaceParent extends Model
{
    //

    protected $fillable = [
        'image',
        'spot_parent_id',
    ];

    // relationship with TopAttractivePlace
    public function topAttractivePlaces()
    {
        return $this->hasMany(TopAttractivePlace::class, 'top_attractive_place_parent_id');
    }

    public function translate()
    {
        return $this->hasMany(TopAttractivePlace::class, 'top_attractive_place_parent_id');
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
