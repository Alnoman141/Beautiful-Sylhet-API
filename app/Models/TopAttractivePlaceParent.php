<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
