<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $guarded = [];
    protected $appends = ['module_name'];
    public function module(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }
    public function getModuleNameAttribute(): string
    {
        return Str::after($this->name, '-');
    }
}
