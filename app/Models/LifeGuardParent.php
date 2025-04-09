<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LifeGuardParent extends Model
{
    use LogsActivity;
    protected $guarded = [];
    public function guards() : HasMany
    {
        return $this->hasMany(LifeGuard::class, 'life_guard_parent_id');
    }

    public function translate() : HasOne
    {
        return $this->hasOne(LifeGuard::class, 'life_guard_parent_id');
    }
    public function spot() : BelongsTo
    {
        return $this->belongsTo(SpotParent::class, 'spot_parent_id');
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
                $life = $this->name ?? 'Life Guard';
                if ($eventName === 'created') {
                    return "{$userName} created a new life guard: {$life}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the life guard: {$life}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the life guard: {$life}.";
                }
                return "{$userName} performed an action on the {$life}.";
            });
    }
}
