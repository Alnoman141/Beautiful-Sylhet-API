<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Complain extends Model
{
    protected $guarded = [];

    public function category(): BelongsTo
    {
       // return $this->hasOneThrough(ComplainCategory::class, ComplainCategoryParent::class, 'id', 'complain_category_parent_id', 'complain_category_parent_id', 'complain_category_parent_id');
        return $this->belongsTo(ComplainCategoryParent::class,'complain_category_parent_id');
    }
}
