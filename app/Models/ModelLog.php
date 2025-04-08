<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelLog extends Model
{
    protected $fillable = ['model_type', 'model_id', 'action', 'changes', 'user_id'];
    protected $casts = [
        'changes' => 'object',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
