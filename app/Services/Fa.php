<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 12:01 PM
 */

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Fa
{
    public static function token(): string
    {
        $token = '0123456789';
        $token = str_shuffle($token);
        return substr($token, 0, 6);
    }
    public static function location(string $ip)
    {
        $response = Http::get("http://ip-api.com/json/{$ip}");
        return $response->json();
    }

    public static function uploadImage(array|\Illuminate\Http\UploadedFile|null $file, string $string)
    {
        try {
            $extension = $file->extension();
            $name = $file->hashName();
            $path = 'uploads/'.$string.'/' . $extension . '/';
            $data = File::get($file);
            $disk = Storage::build([
                'driver' => 'local',
                'root' => public_path('storage/'.$path),
            ]);
            $disk->put($name, $data);
            return 'storage/' . $path.$name;
        }catch (\Exception $exception){
            return 'default.png';
        }
    }
}
