<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmusementPark extends Model
{
    //
    protected $fillable = [
        'name',
        'location',
        'description',
        'distance',
        'ticket_price',
        'opening_hours',
        'map',
        'amusement_park_parent_id',
        'language_id',
    ];

    public function parent()
    {
        return $this->belongsTo(AmusementParkParent::class, 'amusement_park_parent_id');
    }
}
