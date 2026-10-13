<?php
/**
 *Name: Abdullah Al Noman
 *Designation: Sr. Software Engineer
 *Date: 2024-12-10 18:18
 */

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Weather
{
    //TODO @fa 2024-12-10 get weather
    public static function getWeather()
    {
        $weather = Cache::rememberForever('weather-api', function () {
            $response = Http::get('https://tides.today/en/dynamic/tide-forecast/7623');
            if ($response->successful()) {
                Cache::forget('weather');
                $content = $response->json()['content'];
                $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
                $content = preg_replace('/<p\b[^>]*>(.*?)<\/p>/is', "", $content);
                return $content;
            }
            return null;
        });
        if (!$weather) {
            Cache::forget('weather-api');
            return 403;
        }
        $content = $weather;
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
                    //trim($height)
                    $tideDetails['height'][] = (double) Str::before(trim($height),'m');
                }
            }
            $weatherPanel = $xpath->query(".//div[contains(@class, 'weather-container')]", $tabPanel)->item(0);
            $high = $xpath->query(".//span[contains(@class, 'value-high')]//span[contains(@class, 'value-primary')]", $weatherPanel)->item(0)->textContent ?? '';
            $low = $xpath->query(".//span[contains(@class, 'value-low')]//span[contains(@class, 'value-primary')]", $weatherPanel)->item(0)->textContent ?? '';
            $weatherDetails = [
                'condition' => $xpath->query(".//div[contains(@class, 'weather-icon')]//strong", $weatherPanel)->item(0)->textContent ?? '',
                'high_temperature' => Str::replace('Â°c','Â°C', $high),
                'low_temperature' => Str::replace('Â°c','Â°C', $low),
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

    //TODO @fa 2024-12-12
    public static function glass()
    {
        $start = now()->format('Y-m-d');
        $end = now()->addDays(14)->format('Y-m-d');
        $data = Cache::rememberForever('tides', function () use ($start, $end) {
            $other = Http::withHeaders([
                'Authorization'=>config('fa.weather.glass')
            ])->get("https://api.stormglass.io/v2/tide/extremes/point",[
                'lat'=>24.8864,
                'lng'=>91.8807,
                'start'=>$start,
                'end'=>$end
            ]);
            $results =  $other->json();
           return collect( $results['data'])->map(function ($item) {
               $ft = round($item['height'] * 3.28084,2);
               $height = "{$item['height']}m ({$ft}ft)";
                return [
                    'date' => Carbon::parse($item['time'])->format('Y-m-d'),
                    'height' => $height,
                    'type' => $item['type'],
                    'time' => Carbon::parse($item['time'])->format('H:i')
                ];
            });
        });
        return $data;
    }
    //TODO @fa 2024-12-12 visual
    public static function visual($name='coxsbazar')
    {
        $key = config('fa.weather.visual');
        $data = Cache::rememberForever('forecast', function ()use ($key,$name) {
            $response = Http::get("https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/$name?unitGroup=us&include=days&key=$key&contentType=json");
            $results =  $response->json();
            return collect($results['days'])->map(function ($a) {
                return [
                    'date' => Carbon::parse($a['datetime'])->format('Y-m-d'),
                    'weather_data'=>[
                        'sunrise' => Carbon::parse($a['sunrise'])->format('H:i'),
                        'sunset' => Carbon::parse($a['sunset'])->format('H:i'),
                        'high_temperature' => self::fToc($a['tempmax']),
                        'low_temperature' => self::fToc($a['tempmin']),
                        'icon' => $a['icon'],
                        'condition' => round($a['conditions']),
                        'cloud_cover' => round($a['cloudcover']),
                        'humidity' => round($a['humidity']),
                    ]
                ];
            });
        });
        return $data;
    }

    public static function fToc($value)
    {
        return round(($value - 32) * 5 / 9). 'Â°C';
    }
    //TODO @fa 2024-12-12 weather tides and forecast
    public static function tideAndForecast()
    {
        $forecast = self::visual();
        $tides = self::glass();
        return Cache::rememberForever('tide-forecast', function () use ($forecast, $tides) {
            return collect($forecast)->map(function ($a) use ($tides) {
                $tide = collect($tides)->where('date', Carbon::parse($a['date'])->format('Y-m-d'))
                    ->map(fn($a) => [
                        'type' => Str::ucfirst($a['type']),
                        'time' => $a['time'],
                        'height' => $a['height']
                    ])->values();
                return [
                    'date' => $a['date'],
                    'tide_details' => [
                        'type' => $tide->pluck('type'),
                        'time' => $tide->pluck('time'),
                        'height' => $tide->pluck('height'),
                    ],
                    //'tides'=>$tide,
                    'weather_data' => $a['weather_data'],
                ];
            });
        });
    }

    //TODO @fa 2024-12-12 get tides
    public static function getTides()
    {
        try {
            $start = now()->format('Y-m-d');
            $end = now()->addDays(14)->format('Y-m-d');
            $other = Http::withHeaders([
                'Authorization'=>config('fa.weather.glass')
            ])->get("https://api.stormglass.io/v2/tide/extremes/point",[
                'lat'=>24.8864,
                'lng'=>91.8807,
                'start'=>$start,
                'end'=>$end
            ]);
            if ($other->successful()) {
                Cache::forget('tides');
                $results =  $other->json();
                $data = collect( $results['data'])->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item['time'])->format('Y-m-d'),
                        'height' => round($item['height'],2),
                        'type' => $item['type'],
                        'time' => Carbon::parse($item['time'])->format('H:i')
                    ];
                });
                Cache::rememberForever('tides', function () use ($data) {
                    return $data;
                });
            }
        }catch (\Exception $e) {}
    }
    //TODO @fa 2024-12-12 get forecast
    public static function getForecast($name='coxsbazar',$type='visual')
    {
        try {
            $key = config('fa.weather.visual');
            $response = Http::get("https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/$name?unitGroup=us&include=days&key=$key&contentType=json");
            if ($response->successful()) {
                if ($type == 'visual') {
                    Cache::forget('forecast');
                }
                $results =  $response->json();
                $data = collect($results['days'])->map(function ($a) {
                    return [
                        'date' => Carbon::parse($a['datetime'])->format('Y-m-d'),
                        'weather_data'=>[
                            'sunrise' => Carbon::parse($a['sunrise'])->format('H:i'),
                            'sunset' => Carbon::parse($a['sunset'])->format('H:i'),
                            'high_temperature' => self::fToc($a['tempmax']),
                            'low_temperature' => self::fToc($a['tempmin']),
                            'icon' => $a['icon'],
                            'condition' => $a['conditions'],
                            'cloud_cover' => round($a['cloudcover']).'%',
                            'humidity' =>round( $a['humidity']).'%',
                        ]
                    ];
                });
                if ($type !== 'visual') {
                    return $data;
                }
                Cache::rememberForever('forecast', function () use ($data) {
                    return $data;
                });
            }
        }catch (\Exception $e) {}
    }
    //TODO @fa 2024-12-12 open weather
    public static function openWeather($name,$id,$days=null)
    {
        if (empty($name)){
            return [];
        }
        //13d4ddd75f8148649bc53144240812
            return Cache::remember('open-weather-'.$id, now()->addDays(6), function () use ($name,$days,$id) {
                $response = Http::get("http://api.weatherapi.com/v1/forecast.json", [
                    'key' => '13d4ddd75f8148649bc53144240812',
                    'q' => $name,
                    'days' => $days ?? 7
                ]);
                if ($response->successful()) {
                    $data = $response->json();
                    return isset($data['forecast']['forecastday']) ? collect($data['forecast']['forecastday'])->map(function ($a) {
                        return [
                            'date' => $a['date'],
                            'weather_data' => [
                                'sunrise' => Carbon::parse($a['astro']['sunrise'])->format('H:i'),
                                'sunset' => Carbon::parse($a['astro']['sunset'])->format('H:i'),
                                'high_temperature' => round($a['day']['maxtemp_c']) . 'Â°C',
                                'low_temperature' => round($a['day']['mintemp_c']) . 'Â°C',
                                'icon' => Str::remove('//', $a['day']['condition']['icon']),
                                'condition' => $a['day']['condition']['text'],
                                'cloud_cover' => '0%',
                                'humidity' => '0%',
                            ]
                        ];
                    }) : [];
                }
                Cache::forget('open-weather-'.$id);
                return [];
            });
    }
}
