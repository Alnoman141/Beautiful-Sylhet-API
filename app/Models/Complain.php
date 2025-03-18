<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Complain extends Model
{
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
}
