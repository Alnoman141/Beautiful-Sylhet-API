<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $guarded = [];

    public function district(): HasMany
    {
        return $this->hasMany(District::class,'division_id');
    }
}
