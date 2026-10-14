<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class HaorParent extends Model
{
    protected $guarded = [];

    protected $fillable = ['image'];
    
    /**
     * Get all the haors for the haor parent.
     */
    public function haors(): HasMany
    {
        return $this->hasMany(Haor::class, 'haor_parent_id');
    }


    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? Storage::disk('s3')->url($value)
                : null,
        );
    }
}
