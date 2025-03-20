<?php

namespace App\Models;

use App\Services\DiskImageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplainReply extends Model
{
    use DiskImageTrait;
    protected $guarded = [];

    public function complain():BelongsTo
    {
        return $this->belongsTo(Complain::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->disk($value),
        );
    }
}
