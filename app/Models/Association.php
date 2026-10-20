<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Association extends Model
{
    protected $fillable = [
        'association_parent_id',
        'language_id',
        'name',
        'president_name',
        'secretary_name',
        'address',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssociationParent::class, 'association_parent_id');
    }
}