<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 27-Mar-25 12:28 PM
 */

namespace App\Services;
use Illuminate\Support\Facades\Http;
use OpenAI;
class OpenAIAPI
{
    public static function client()
    {
        return OpenAI::client(config('openai.api_key'));
    }

    public static function generate($prompt)
    {
        $apiKey = config('openai.api_key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ]);
        if ($response->ok()) {
            $result = collect($response->json())->toArray();
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }
        else {
            return $response->body();
        }
    }
    public static function generateOpenAI($prompt,$model='gpt-3.5-turbo',$maxTokens=200)
    {
        try {
            $response = self::client()->completions()->create([
                'model' => $model,
                'prompt' => $prompt,
                'max_tokens' => $maxTokens
            ]);
            return $response->choices[0]->text;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
}
