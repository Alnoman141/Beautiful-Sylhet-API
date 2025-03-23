<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Complain extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    public function category(): HasOneThrough
    {
        return $this->hasOneThrough(
            ComplainCategory::class,  // Final target model
            ComplainCategoryParent::class, // Intermediate model
            'id',  // Foreign key on ComplainCategoryParent (Primary key of ComplainCategoryParent)
            'complain_category_parent_id', // Foreign key on ComplainCategory (points to ComplainCategoryParent)
            'complain_category_parent_id', // Foreign key on Complain model (points to ComplainCategoryParent)
            'id'  // Primary key of ComplainCategoryParent
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ComplainReply::class,'complain_id');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
    protected function file(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
}
