<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Notice extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(NoticeParent::class, 'notice_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('announcement')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $announcement = "announcement";
                if ($eventName === 'created') {
                    return "{$userName} created a new {$announcement}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the {$announcement}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the {$announcement}.";
                }
                return "{$userName} performed an action on the {$announcement}.";
            });
    }
}
