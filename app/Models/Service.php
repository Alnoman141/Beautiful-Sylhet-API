<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Service extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'booking_numbers' => 'array',
            'supervisor_numbers' => 'array',
        ];
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ServiceParent::class, 'service_parent_id');
    }

    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, Service::class, 'id', 'spot_parent_id', 'id', 'spot_parent_id');
    }
}
