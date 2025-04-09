<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RedZone extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RedZoneParent::class, 'red_zone_parent_id');
    }

    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, RedZoneParent::class, 'id', 'spot_parent_id','red_zone_parent_id','spot_parent_id');
    }

    public function category(): HasOneThrough
    {
        return $this->hasOneThrough(RedZoneCategory::class, RedZoneParent::class, 'id', 'red_zone_category_parent_id','red_zone_parent_id','red_zone_category_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('danger_zone')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $danger_zone = $this->name ?? 'Danger Zone';
                if ($eventName === 'created') {
                    return "{$userName} created a new danger zone: {$danger_zone}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the danger zone: {$danger_zone}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the danger zone: {$danger_zone}.";
                }
                return "{$userName} performed an action on the {$danger_zone}.";
            });
    }
}
