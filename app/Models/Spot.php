<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Spot extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SpotParent::class,'spot_parent_id');
    }
    //TODO @fa 2025-04-08 get activity log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('spot')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $sportName = $this->name ?? 'Spot';
                if ($eventName === 'created') {
                    return "{$userName} created a new spot: {$sportName}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the spot: {$sportName}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the spot: {$sportName}.";
                }
                return "{$userName} performed an action on the spot.";
            });
    }
}
