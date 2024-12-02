<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CampaignParent extends Model
{
    use DiskImageTrait;
    protected $guarded = [];
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'campaign_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(Campaign::class, 'campaign_parent_id');
    }
}
