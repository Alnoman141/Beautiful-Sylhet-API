<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
