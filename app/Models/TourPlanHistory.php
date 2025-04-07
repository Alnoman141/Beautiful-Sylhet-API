<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPlanHistory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            //'data' => 'object',
        ];
    }

    public function plan():BelongsTo
    {
        return $this->belongsTo(TourPlan::class, 'tour_plan_id');
    }
}
