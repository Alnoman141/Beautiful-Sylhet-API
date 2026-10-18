<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuideRating extends Model
{
    protected $fillable = [
        'tour_guide_parent_id',
        'user_id',
        'rating',
        'review',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'review' => 'string',
        'status' => 'boolean',
    ];

    public function tour_guideParent()
    {
        return $this->belongsTo(TourGuideParent::class, 'tour_guide_parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
