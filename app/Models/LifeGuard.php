<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LifeGuard extends Model
{
    use LogsActivity;
    protected $guarded = [];
    public function parent() : BelongsTo
    {
        return $this->belongsTo(LifeGuardParent::class, 'life_guard_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, LifeGuardParent::class, 'id', 'spot_parent_id','life_guard_parent_id','spot_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('life_guard')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $guard = $this->name ?? 'Life Guard';
                if ($eventName === 'created') {
                    return "{$userName} created a new life guard: {$guard}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the life guard: {$guard}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the life guard: {$guard}.";
                }
                return "{$userName} performed an action on the life guard.";
            });
    }
}
