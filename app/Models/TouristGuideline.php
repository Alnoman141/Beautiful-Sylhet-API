<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TouristGuideline extends Model
{
    use LogsActivity;
    protected $guarded = [];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('guidelines')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $guideline = "Guidelines";
                if ($eventName === 'created') {
                    return "{$userName} created a new  {$guideline}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the  {$guideline}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the  {$guideline}.";
                }
                return "{$userName} performed an action on the {$guideline}.";
            });
    }
}
