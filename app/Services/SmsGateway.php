<?php
/**
 *Name: Abdullah Al Noman
 *Designation: Sr. Software Engineer
 *Date: 2025-03-11 12:30
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsGateway
{
    public static function send($phone, $token)
    {
        $smsConfig = self::getSmsConfig();
        $url = $smsConfig['url'];
        if (substr($phone, 0, 2) != '88') {
            $phone = '88' . $phone;
        }
        $apiKey = $smsConfig['username'];
        $name = 'Vromonika';
        $data = [
            "sender_id" => $smsConfig['sender_id'],
            "receiver" => $phone,
            'message' => "Your $name verification code is: " . $token,
            'remove_duplicate' => true
        ];

        $headers = [
            'Authorization: ' . $apiKey,
            'Content-Type: application/json'
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);
        if (json_decode($response)->code==200) {
            return [
                'code' => 200,
                'message' => 'Verification code Send Successfully',
            ];
        }
        return [
            'code' => 403,
            'message' => 'Verification code Send Failed',
        ];
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
