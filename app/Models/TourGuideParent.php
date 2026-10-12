<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuideParent extends Model
{
    protected $fillable = [
        'email',
        'phone_number',
        'website',
        'logo',
        'license_number',
        'license_document',
        'service_locations',
        'rate_per_day',
    ];

    protected $casts = [
        'service_locations' => 'array',
    ];

    public function guides()
    {
        return $this->hasMany(TourGuide::class, 'tour_guide_parent_id');
    }
}
