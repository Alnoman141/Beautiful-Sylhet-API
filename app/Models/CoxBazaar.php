<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CoxBazaar extends Model
{
    use LogsActivity;
    protected $guarded = [];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('about_cox_bazaar')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $cox_bazar = "about Cox's Bazaar";
                if ($eventName === 'created') {
                    return "{$userName} created  {$cox_bazar}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated {$cox_bazar}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted {$cox_bazar}.";
                }
                return "{$userName} performed an action on the {$cox_bazar}.";
            });
    }
}
