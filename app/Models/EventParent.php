<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(Event::class, 'event_parent_id');
    }
}
