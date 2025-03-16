<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'data'=>'object'
        ];
    }
}
