<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuide extends Model
{
    protected $fillable = [
        'name',
        'description',
        'tour_guide_parent_id',
        'language_id',
    ];

    public function parent()
    {
        return $this->belongsTo(TourGuideParent::class, 'tour_guide_parent_id');
    }
}
