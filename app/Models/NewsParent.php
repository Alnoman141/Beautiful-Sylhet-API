<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsParent extends Model
{
    protected $guarded = [];
    public function type(): BelongsTo
    {
        return $this->belongsTo(NewsType::class,'news_type_id');
    }
}
