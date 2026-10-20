<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TopAttractivePlace extends Model
{
    //

    protected $fillable = [
        'name',
        'tag_line',
        'tag',
        'status',
        'top_attractive_place_parent_id',
        'language_id',
    ];

    // parent relationship
    public function parent()
    {
        return $this->belongsTo(TopAttractivePlaceParent::class, 'top_attractive_place_parent_id');
    }
}
