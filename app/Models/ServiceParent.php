<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceParent extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'descriptions' => 'object',
        ];
    }
}
