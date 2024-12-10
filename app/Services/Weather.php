<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-12-10 18:18
 */

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Weather
{
    //TODO @fa 2024-12-10 get weather
    public static function getWeather()
    {
        $weather = Cache::rememberForever('weather-api', function () {
            $response = Http::get('https://tides.today/en/dynamic/tide-forecast/2801');
            if ($response->successful()) {
                Cache::forget('weather');
                return $response->json();
            }
            return null;
        });
        if (!$weather) {
            Cache::forget('weather-api');
            return 403;
        }
        $content = $weather['content'];
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
        $content = preg_replace('/<p\b[^>]*>(.*?)<\/p>/is', "", $content);
        return self::weatherData($content);
    }

    private static function weatherData($html)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $data = [];
        $xpath = new \DOMXPath($dom);
        $tabPanels = $xpath->query("//div[contains(@class, 'tab-panel')]");
        foreach ($tabPanels as $tabPanel) {
            $panelId = $tabPanel->getAttribute('id');
            $caption = $xpath->query(".//table/caption", $tabPanel)->item(0)->textContent ?? '';
            $tideRows = $xpath->query(".//table/tbody/tr", $tabPanel);
            $tideDetails = [
                'type' => [],
                'time' => [],
                'height' => []
            ];
            $validTideTypes = ['High', 'Low'];
            foreach ($tideRows as $row) {
                $cols = $xpath->query('./td', $row);
                $type = trim($cols->item(0)->textContent) ?? '';
                if (in_array($type, $validTideTypes)) {
                    $tideDetails['type'][] = $type;
                }
                $time = $cols->item(2)->textContent ?? '';
                if (strtotime($time)) {
                    $tideDetails['time'][] = trim($time);
                }
                $height = $cols->item(3)->textContent ?? '';
                if ($height){
                    $tideDetails['height'][] = trim($height);
                }
            }
            $weatherPanel = $xpath->query(".//div[contains(@class, 'weather-container')]", $tabPanel)->item(0);
            $weatherDetails = [
                'condition' => $xpath->query(".//div[contains(@class, 'weather-icon')]//strong", $weatherPanel)->item(0)->textContent ?? '',
                'high_temperature' => $xpath->query(".//span[contains(@class, 'value-high')]//span[contains(@class, 'value-primary')]", $weatherPanel)->item(0)->textContent ?? '',
                'low_temperature' => $xpath->query(".//span[contains(@class, 'value-low')]//span[contains(@class, 'value-primary')]", $weatherPanel)->item(0)->textContent ?? '',
            ];
            $weatherTableRows = $xpath->query(".//table//tbody/tr", $weatherPanel);
            foreach ($weatherTableRows as $row) {
                $cells = $row->getElementsByTagName('td');
                $label = trim($row->getElementsByTagName('th')->item(0)->textContent);
                $value = trim($cells->item(0)->textContent);

                switch ($label) {
                    case 'Sunrise':
                        $weatherDetails['sunrise'] = $value;
                        break;
                    case 'Sunset':
                        $weatherDetails['sunset'] = $value;
                        break;
                    case 'Cloud cover':
                        $weatherDetails['cloud_cover'] = $value;
                        break;
                    case 'Humidity':
                        $weatherDetails['humidity'] = $value;
                        break;
                }
            }
            $data[] = [
                'date' => Str::replace('tabpanel-', '', $panelId),
                'caption' => $caption,
                'tide_details' => $tideDetails,
                'weather_data' => $weatherDetails,
            ];
        }
       $weather = Cache::rememberForever('weather', function () use ($data) {
            return $data;
        });
        return response()->json($weather);
    }
}
