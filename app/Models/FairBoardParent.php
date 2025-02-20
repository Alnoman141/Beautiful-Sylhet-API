<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FairBoardParent extends Model
{
    protected $guarded = [];

    public function fairBoards(): HasMany
    {
        return $this->hasMany(FairBoard::class, 'fair_board_parent_id');
    }
}
