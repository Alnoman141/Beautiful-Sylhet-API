<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TourPlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payload' => 'object',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function conversations():HasMany
    {
        return $this->hasMany(TourPlanHistory::class, 'tour_plan_id');
    }

    public function history(): HasOne
    {
        return $this->hasOne(TourPlanHistory::class, 'tour_plan_id');
    }
}
