<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Doctor extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DoctorParent::class, 'doctor_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, DoctorParent::class, 'id', 'spot_parent_id','doctor_parent_id','spot_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('doctor')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $doctor = $this->name ?? 'Doctor';
                if ($eventName === 'created') {
                    return "{$userName} created a new doctor: {$doctor}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the doctor: {$doctor}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the doctor: {$doctor}.";
                }
                return "{$userName} performed an action on the doctor.";
            });
    }
}
