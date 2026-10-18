<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function ratings(): HasMany
    {
        return $this->hasMany(TourOperatorRating::class, 'tour_operator_parent_id')->where('status', 1);
    }
}
