<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpotRatting extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'spot_parent_id',
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

    public function spotParent()
    {
        return $this->belongsTo(SpotParent::class, 'spot_parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
