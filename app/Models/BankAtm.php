<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class BankAtm extends Model
{
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
}
