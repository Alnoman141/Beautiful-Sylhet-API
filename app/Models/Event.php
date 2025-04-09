<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(EventParent::class, 'event_parent_id');
    }
    //TODO @fa 2024-12-03 spot
    public function spotParent(): HasOneThrough
    {
        return $this->hasOneThrough(SpotParent::class, EventParent::class, 'id', 'id', 'event_parent_id', 'spot_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, EventParent::class, 'id', 'spot_parent_id','event_parent_id','spot_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('event')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $event = $this->name ?? 'Event';
                if ($eventName === 'created') {
                    return "{$userName} created a new event: {$event}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the event: {$event}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the event: {$event}.";
                }
                return "{$userName} performed an action on the {$event}.";
            });
    }
}
