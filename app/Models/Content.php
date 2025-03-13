<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'data'=>'array'
        ];
    }
}
