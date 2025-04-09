<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class News extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsParent::class, 'news_parent_id');
    }

    public function type(): HasOneThrough
    {
        return $this->hasOneThrough(NewsType::class, NewsParent::class, 'id', 'id', 'news_parent_id', 'news_type_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('news')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $news = "news";
                if ($eventName === 'created') {
                    return "{$userName} created a new {$news}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the {$news}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the {$news}.";
                }
                return "{$userName} performed an action on the {$news}.";
            });
    }
}
