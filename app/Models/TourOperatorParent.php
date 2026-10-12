<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourOperatorParent extends Model
{
    protected $fillable = [
        'email',
        'phone_number',
        'website',
        'logo',
        'license_number',
        'license_document',
        'tin_number',
        'tin_document',
        'service_locations',
    ];

    protected $casts = [
        'service_locations' => 'array',
    ];

    public function operators()
    {
        return $this->hasMany(TourOperator::class, 'tour_operator_parent_id');
    }
}
