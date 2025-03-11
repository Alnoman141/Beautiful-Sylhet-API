<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplainCategoryParent extends Model
{
    protected $guarded = [];

    public function categories(): HasMany
    {
        return $this->hasMany(ComplainCategory::class, 'complain_category_parent_id');
    }
}
