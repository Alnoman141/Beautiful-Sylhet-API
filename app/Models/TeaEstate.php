<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeaEstate extends Model
{
    protected $fillable = [
        'tea_estate_parent_id',
        'language_id',
        'name',
        'location',
        'description',
        'distance',
        'contact_info',
        'map',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            TeaEstateParent::class,
            'tea_estate_parent_id'
        );
    }
}
