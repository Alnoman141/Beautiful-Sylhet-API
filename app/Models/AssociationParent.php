<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssociationParent extends Model
{
    protected $fillable = [
        'spot_parent_id',
        'president_phone',
        'secretary_phone',
        'logo',
        'email',
        'website',
    ];

    // All language rows of this association (like Spot's `spot` relation)
    public function association(): HasMany
    {
        return $this->hasMany(Association::class, 'association_parent_id');
    }

    public function spotParent(): BelongsTo
    {
        return $this->belongsTo(SpotParent::class, 'spot_parent_id');
    }
}
