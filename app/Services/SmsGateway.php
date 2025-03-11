<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2025-03-11 12:30
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsGateway
{
    public static function send($phone, $message)
    {
        $message = rawurlencode($message);
        $smsConfig = self::getSmsConfig();
        $url = $smsConfig['url'];
        if (substr($phone, 0, 2) != '88') {
            $phone = '88' . $phone;
        }
        $username = $smsConfig['username'];
        $password = $smsConfig['password'];
        $message = rawurlencode($message);
        $url = "$url?user=$username&password=$password&mobiles=$phone&sms=$message&unicode=1";
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $response = curl_exec($c);
        dd($response);
        return $response;
    }

    public static function getSmsConfig()
    {
        return [
            'username' => config('sms.sms.username'),
            'password' => config('sms.sms.password'),
            'sender_id' => config('sms.sms.sender_id'),
            'url' => config('sms.sms.url'),
        ];
    }
}
