<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function ratings(): HasMany
    {
        return $this->hasMany(TourGuideRating::class, 'tour_guide_parent_id')->where('status', 1);
    }
}
