<?php

/**
 *Name: Abdullah Al Noman
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 12:01 PM
 */

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Nelexa\GPlay\GPlayApps;

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
            $path = 'SylhetApp/' . $string . '/' . $extension . '/';
            $data = File::get($file);
            $photo =  ImageManager::gd()->read($data)->toJpeg(30);
            if (config('filesystems.default') == 's3') {
                //Storage::disk('s3')->put($path . $name, $data, 'public');
                Storage::disk('s3')->put($path . $name, $photo, 'public'); //TODO @fa 2024-12-27 original image to adjust resolution
                //return  $temporaryUrl = Storage::disk('s3')->temporaryUrl($path . $name, now()->addMinutes(1));
                return $path . $name;
            } else {
                $disk = Storage::build([
                    'driver' => 'local',
                    'root' => public_path('storage/' . $path),
                ]);
                $disk->put($name, $photo); //TODO @fa 2024-12-27 original image to adjust resolution
                return 'storage/' . $path . $name;
            }
        } catch (\Exception $exception) {
            Log::error('Image upload failed', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            return '';
        }
    }

    public static function deleteImage($image)
    {
        if ($image) {
            if (config('filesystems.default') == 's3') {
                $image = Str::after($image, config('filesystems.disks.s3.url') . '/');
                Storage::disk('s3')->delete($image);
            } else {
                $filePath =  Str::afterLast($image, 'storage/');
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }
    }
    //TODO @fa 2025-03-11 upload file
    public static function uploadFile(array|\Illuminate\Http\UploadedFile|null $file, string $string)
    {
        try {
            $extension = $file->extension();
            $name = $file->hashName();
            $path = 'coxbazaar/' . $string . '/' . $extension . '/';
            $data = File::get($file);
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'])) {
                $data =  ImageManager::gd()->read($data)->toJpeg(30);
            }
            if (config('filesystems.default') == 's3') {
                Storage::disk('s3')->put($path . $name, $data, 'public');
                return $path . $name;
            } else {
                $disk = Storage::build([
                    'driver' => 'local',
                    'root' => public_path('storage/' . $path),
                ]);
                $disk->put($name, $data);
                return 'storage/' . $path . $name;
            }
        } catch (\Exception $exception) {
            return '';
        }
    }
    public static function activity($data)
    {
        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'content' => $data
        ]);
    }
    //TODO @fa 2025-02-18 app downloads
    public static function appDownloadAndroid()
    {
        return Cache::remember('app_download_android', 60 * 60 * 24, function () {
            $packageName = 'com.inflack.coxs_bazar_app'; // Your app's package ID
            $google = new GPlayApps();
            $app = $google->getAppInfo($packageName);
            $data = [
                'title' => $app->getName(),
                'androidDownloads' => self::counting($app->getInstalls()),
                'installText' => $app->getInstallsText(),
                'appleDownloads' => self::appDownloadIos(),
                'androidReviews' => collect($google->getReviews($packageName))
                    ->map(function ($review) {
                        $data = collect($review)->toArray();
                        return [
                            'avatar' => $data['avatar'],
                            'name' => $data['userName'],
                            'text' => $data['text'],
                            'score' => $data['score'],
                            'date' => $data['date']
                        ];
                    })->filter(fn($a) => $a['score'] > 4)->values()
            ];
            return $data;
        });
    }
    public static function appDownloadIos()
    {
        $url = 'https://apps.apple.com/us/app/vromonika/id6741048193';
        $response = Http::get($url);
        $html = $response->body();
        preg_match('/<dt[^>]*>\s*Age Rating\s*<\/dt>\s*<dd[^>]*>\s*(.*?)\s*</', $html, $matches);
        return @$matches[1] ?? '0';
    }
    public static function counting($n = 0, $precision = 1)
    {
        if ($n < 900) {
            $n_format = number_format($n, $precision);
            $suffix = '';
        } else if ($n < 900000) {
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }
        if ($precision > 0) {
            $dotZero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotZero, '', $n_format);
        }
        return $n_format . $suffix;
    }
}
