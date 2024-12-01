<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-12-01 17:17
 */

namespace App\Services;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait DiskImageTrait
{
    protected function disk(string $name): string
    {
        return $name
            ? (config('filesystems.default') === 's3'
            ? Storage::disk('s3')->url($name)
            : (file_exists($name) ? asset($name) : '')
        ) : '';
    }
}
