<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Activity extends \Spatie\Activitylog\Models\Activity
{
    //causer_id is present user model id get roles from this model
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            config('permission.table_names.model_has_roles'),
            'model_id',
            'role_id',
            'causer_id',
            'id'
        );
    }
}
