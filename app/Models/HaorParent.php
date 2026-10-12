<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HaorParent extends Model
{
    public function haors(): HasMany
    {
        return $this->hasMany(Haor::class, 'haor_parent_id');
    }
}
