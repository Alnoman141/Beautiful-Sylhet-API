<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SpotImage extends Model
{
    protected $guarded = [];
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => file_exists($value) ? asset($value) : '',
        );
    }
}
