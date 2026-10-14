<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmusementParkParent extends Model
{

    protected $guarded = [];

    protected $fillable = ['image'];
    
    public function amusementParks()
    {
        return $this->hasMany(AmusementPark::class, 'amusement_park_parent_id');
    }
}
