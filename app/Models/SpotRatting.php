<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpotRatting extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'sport_parent_id',
        'user_id',
        'rating',
        'review',
    ];
    protected $casts = [
        'rating' => 'integer',
        'review' => 'string',
    ];

    public function spotParent()
    {
        return $this->belongsTo(Spot::class, 'sport_parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
