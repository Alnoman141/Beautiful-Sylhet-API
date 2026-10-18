<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantRating extends Model
{
    protected $fillable = [
        'restaurant_parent_id',
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

    public function restaurantParent()
    {
        return $this->belongsTo(RestaurantParent::class, 'restaurant_parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
