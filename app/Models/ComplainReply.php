<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplainReply extends Model
{
    protected $guarded = [];

    public function complain():BelongsTo
    {
        return $this->belongsTo(Complain::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
