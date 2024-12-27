<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 12:01 PM
 */

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

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
            $path = 'coxbazaar/'.$string.'/' . $extension . '/';
            $data = File::get($file);
            $photo =  ImageManager::gd()->read($data)->toJpeg(30);
            if (config('filesystems.default') == 's3') {
                //Storage::disk('s3')->put($path . $name, $data, 'public');
                Storage::disk('s3')->put($path . $name, $photo, 'public');
              //return  $temporaryUrl = Storage::disk('s3')->temporaryUrl($path . $name, now()->addMinutes(1));
                return $path.$name;
            } else {
                $disk = Storage::build([
                    'driver' => 'local',
                    'root' => public_path('storage/'.$path),
                ]);
                $disk->put($name, $photo); //TODO @fa 2024-12-27 original image to adjust resolution
                return 'storage/' . $path.$name;
            }
        }catch (\Exception $exception){
            return '';
        }
    }

    public static function deleteImage($image)
    {
        if ($image){
            if (config('filesystems.default')=='s3') {
                $image = Str::after($image, config('filesystems.disks.s3.url').'/');
                Storage::disk('s3')->delete($image);
            }
            else{
                $filePath =  Str::afterLast($image, 'storage/');
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }
    }
    public static function activity($data)
    {
        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'content' => $data
        ]);
    }
}
