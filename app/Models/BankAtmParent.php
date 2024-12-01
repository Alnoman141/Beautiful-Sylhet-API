<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankAtmParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
    public function atms(): HasMany
    {
        return $this->hasMany(BankAtm::class,'bank_atm_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(BankAtm::class,'bank_atm_parent_id');
    }
}
