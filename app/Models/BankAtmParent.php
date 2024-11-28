<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankAtmParent extends Model
{
    protected $guarded = [];

    public function atms(): HasMany
    {
        return $this->hasMany(BankAtm::class,'bank_atm_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(BankAtm::class,'bank_atm_parent_id');
    }
}
