<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    protected $guarded = [];

    public function parent() : BelongsTo
    {
        return $this->belongsTo(NoticeParent::class, 'notice_parent_id');
    }
}
