<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content' => 'json',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
