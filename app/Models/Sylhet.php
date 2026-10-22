<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sylhet extends Model
{
    use LogsActivity;
    protected $guarded = [];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('about_sylhet')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $sylhet = "about Sylhet";
                if ($eventName === 'created') {
                    return "{$userName} created  {$sylhet}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated {$sylhet}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted {$sylhet}.";
                }
                return "{$userName} performed an action on the {$sylhet}.";
            });
    }
}
