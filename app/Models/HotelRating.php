<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRating extends Model
{
    protected $fillable = [
        'hotel_parent_id',
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

    public function hotelParent()
    {
        return $this->belongsTo(HotelParent::class, 'hotel_parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
