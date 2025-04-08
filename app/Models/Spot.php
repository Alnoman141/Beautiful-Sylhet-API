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
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $sportName = $this->name ?? 'Sport';
                if ($eventName === 'created') {
                    return "{$userName} created a new sport: {$sportName}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the sport: {$sportName}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the sport: {$sportName}.";
                }
                return "{$userName} performed an action on the sport.";
            });
    }
}
