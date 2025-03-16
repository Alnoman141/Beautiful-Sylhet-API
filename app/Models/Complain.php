<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Complain extends Model
{
    protected $guarded = [];

    public function category(): HasOneThrough
    {
        return $this->hasOneThrough(ComplainCategory::class, ComplainCategoryParent::class, 'id', 'complain_category_parent_id', 'complain_category_parent_id', 'complain_category_parent_id');
    }
}
