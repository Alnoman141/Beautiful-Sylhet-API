<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Haor extends Model
{
    protected $fillable = [
        'name',
        'location',
        'description',
        'distance',
        'suitable_visiting_time',
        'haor_parent_id',
        'language_id',
    ];

    /**
     * Get the parent of the haor.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(HaorParent::class, 'haor_parent_id');
    }
}
