<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockerImage extends Model
{
    use DiskImageTrait;
    protected $guarded = [];
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(LockerParent::class,'locker_parent_id');
    }
}
