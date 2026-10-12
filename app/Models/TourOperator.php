<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourOperator extends Model
{
    protected $fillable = [
        'tour_operator_parent_id',
        'language_id',
        'organization_name',
        'name',
        'address',
        'description',// This will be stored as a JSON array of strings
    ];

    public function parent()
    {
        return $this->belongsTo(
            TourOperatorParent::class,
            'tour_operator_parent_id'
        );
    }
}
