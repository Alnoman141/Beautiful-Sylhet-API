<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class News extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsParent::class, 'news_parent_id');
    }

    public function type(): HasOneThrough
    {
        return $this->hasOneThrough(NewsType::class, NewsParent::class, 'id', 'id', 'news_parent_id', 'news_type_id');
    }
}
