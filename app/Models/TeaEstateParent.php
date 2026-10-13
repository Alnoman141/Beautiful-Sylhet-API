<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class TeaEstateParent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'image' => 'string',
    ];

    public function teaEstates(): HasMany
    {
        return $this->hasMany(
            TeaEstate::class,
            'tea_estate_parent_id'
        );
    }

    protected function image(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value
            ? Storage::disk('public')->url($value)
            : null,
    );
}
}
