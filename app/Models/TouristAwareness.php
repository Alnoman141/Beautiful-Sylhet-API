<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TouristAwareness extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('awareness')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $awareness = "Awareness";
                if ($eventName === 'created') {
                    return "{$userName} created a new  {$awareness}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the  {$awareness}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the  {$awareness}.";
                }
                return "{$userName} performed an action on the {$awareness}.";
            });
    }
}
