<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ServiceParent extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'descriptions' => 'object',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_parent_id');
    }

    public function spots(): HasManyThrough
    {
        return $this->hasManyThrough(Spot::class, Service::class, 'id', 'spot_parent_id', 'id', 'spot_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, Service::class, 'id', 'spot_parent_id', 'id', 'spot_parent_id');
    }
}
