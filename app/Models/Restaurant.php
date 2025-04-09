<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Restaurant extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RestaurantParent::class, 'restaurant_parent_id');
    }
    public function spot() : HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, RestaurantParent::class, 'id', 'spot_parent_id','restaurant_parent_id','spot_parent_id');
    }

    public function category(): HasOneThrough
    {
        return $this->hasOneThrough(RestaurantCategory::class, RestaurantParent::class, 'id', 'restaurant_category_parent_id','restaurant_parent_id','restaurant_category_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('restaurant')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $restaurant = $this->name ?? 'Restaurant';
                if ($eventName === 'created') {
                    return "{$userName} created a new restaurant: {$restaurant}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the restaurant: {$restaurant}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the restaurant: {$restaurant}.";
                }
                return "{$userName} performed an action on the {$restaurant}.";
            });
    }
}
