<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotType extends Model
{
    protected $fillable = [
        'spot_type_parent_id',
        'language_id',
        'name',
        'description',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            SpotTypeParent::class,
            'spot_type_parent_id'
        );
    }
}