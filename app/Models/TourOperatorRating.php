<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourOperatorRating extends Model
{
    protected $fillable = [
        'tour_operator_parent_id',
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

    public function tour_operatorParent()
    {
        return $this->belongsTo(TourOperatorParent::class, 'tour_operator_parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
