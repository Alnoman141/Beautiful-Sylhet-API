<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NoticeParent extends Model
{
    protected $guarded = [];

    public function notice(): HasMany
    {
        return $this->hasMany(Notice::class, 'notice_parent_id');
    }

    public function translate(): HasOne
    {
        return $this->hasOne(Notice::class, 'notice_parent_id');
    }
}
