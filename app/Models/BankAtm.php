<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BankAtm extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(BankAtmParent::class,'bank_atm_parent_id');
    }

    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, BankAtmParent::class, 'id', 'spot_parent_id','bank_atm_parent_id','spot_parent_id');
    }

    public function bank(): HasOneThrough
    {
        return $this->hasOneThrough(Bank::class, BankAtmParent::class, 'id', 'bank_parent_id','bank_atm_parent_id','bank_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('atm')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $atm = $this->name ?? 'Atm';
                if ($eventName === 'created') {
                    return "{$userName} created a new atm: {$atm}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the atm: {$atm}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the atm: {$atm}.";
                }
                return "{$userName} performed an action on the {$atm}.";
            });
    }
}
