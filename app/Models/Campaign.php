<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Campaign extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CampaignParent::class,'campaign_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('campaign')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $campaign = $this->name ?? 'Campaign';
                if ($eventName === 'created') {
                    return "{$userName} created a new campaign: {$campaign}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the campaign: {$campaign}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the campaign: {$campaign}.";
                }
                return "{$userName} performed an action on the {$restaurant}.";
            });
    }
}
