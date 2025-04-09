<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Bank extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BankParent::class,'bank_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('bank')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $bank = $this->name ?? 'Bank';
                if ($eventName === 'created') {
                    return "{$userName} created a new bank: {$bank}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the bank: {$bank}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the bank: {$bank}.";
                }
                return "{$userName} performed an action on the {$bank}.";
            });
    }
}
