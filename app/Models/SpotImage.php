<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SpotImage extends Model
{
    use DiskImageTrait;
    protected $guarded = [];
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
}
