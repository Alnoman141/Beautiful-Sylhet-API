<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsParent extends Model
{
    protected $guarded = [];
    public function type(): BelongsTo
    {
        return $this->belongsTo(NewsType::class,'news_type_id');
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class,'news_parent_id');
    }
}
