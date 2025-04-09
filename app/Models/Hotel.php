<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Hotel extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(HotelParent::class,'hotel_parent_id');
    }
    //sport parent
    public function spotParent() : HasOneThrough
    {
        return $this->hasOneThrough(SpotParent::class,HotelParent::class,'id','id','hotel_parent_id','spot_parent_id');
    }
    //TODO @fa 2024-11-25 category parent
    public function categoryParent() : HasOneThrough
    {
        return $this->hasOneThrough(HotelCategoryParent::class,HotelParent::class,'id','id','hotel_parent_id','hotel_category_parent_id');
    }
    //TODO @fa 2024-11-25 type parent
    public function typeParent() : HasOneThrough
    {
        return $this->hasOneThrough(HotelTypeParent::class,HotelParent::class,'id','id','hotel_parent_id','hotel_type_parent_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('hotel')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $hotel = $this->name ?? 'Hotel';
                if ($eventName === 'created') {
                    return "{$userName} created a new hotel: {$hotel}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the hotel: {$hotel}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the hotel: {$hotel}.";
                }
                return "{$userName} performed an action on the {$hotel}.";
            });
    }
}
