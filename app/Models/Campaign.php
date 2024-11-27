<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CampaignParent::class,'campaign_parent_id');
    }
}
