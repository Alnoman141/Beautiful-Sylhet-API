<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class FairBoard extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            //'name'=>'object'
        ];
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FairBoardParent::class, 'fair_board_parent_id');
    }
    public function spot(): HasOneThrough
    {
        return $this->hasOneThrough(Spot::class, FairBoardParent::class, 'id', 'spot_parent_id', 'fair_board_parent_id', 'spot_parent_id');
    }
}
