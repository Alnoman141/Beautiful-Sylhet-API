<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Locker extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LockerParent::class,'locker_parent_id');
    }

    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, LockerParent::class, 'id', 'spot_parent_id','locker_parent_id','spot_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('locker')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $locker = $this->spot?->name ?? 'Locker';
                if ($eventName === 'created') {
                    return "{$userName} created a new locker: {$locker}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the locker: {$locker}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the locker: {$locker}.";
                }
                return "{$userName} performed an action on the {$locker}.";
            });
    }
}
