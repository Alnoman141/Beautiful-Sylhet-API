<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankParent extends Model
{
    protected $guarded = [];

    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class,'bank_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(Bank::class,'bank_parent_id');
    }
}
