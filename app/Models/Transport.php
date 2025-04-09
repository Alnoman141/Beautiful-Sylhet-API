<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transport extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TransportParent::class, 'transport_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('transport')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $transport = $this->name ?? 'Transport';
                if ($eventName === 'created') {
                    return "{$userName} created a new transport: {$transport}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the transport: {$transport}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the transport: {$transport}.";
                }
                return "{$userName} performed an action on the {$transport}.";
            });
    }
}
