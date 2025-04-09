<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Police extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PoliceParent::class, 'police_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, PoliceParent::class, 'id', 'spot_parent_id','police_parent_id','spot_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('police')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $police = $this->name ?? 'Police';
                if ($eventName === 'created') {
                    return "{$userName} created a new police: {$police}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the police: {$police}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the police: {$police}.";
                }
                return "{$userName} performed an action on the police.";
            });
    }
}
