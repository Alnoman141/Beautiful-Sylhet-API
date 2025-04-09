<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FairBoard extends Model
{
    use LogsActivity;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            //'name'=>'object'
        ];
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FairBoardParent::class, 'fair_board_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, FairBoardParent::class, 'id', 'spot_parent_id', 'fair_board_parent_id', 'spot_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fare_board')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $fare = "Fare Board";
                if ($eventName === 'created') {
                    return "{$userName} created a new {$fare}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the {$fare}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the {$fare}.";
                }
                return "{$userName} performed an action on the {$fare}.";
            });
    }
}
