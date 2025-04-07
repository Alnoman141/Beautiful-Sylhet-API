<?php

namespace App\Http\Controllers\Api\V2\AI;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\TourPlan;
use App\Models\TourPlanHistory;
use App\Services\OpenAIAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TourPlanGenerateController extends Controller
{
    //TODO @fa 27-Mar-25 tour plan
    public function tourPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $tourPlans = OpenAIAPI::generate($request->prompt);
            return response()->json([
                'status_code'=> 200,
                'data' => $this->markdownToHtml($tourPlans)
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 27-Mar-25 tour plan
    public function tourPlanGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'tour_plan_id' => 'nullable|integer|exists:tour_plans,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        //DB::beginTransaction();
        try {
            $apiKey = config('openai.api_key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
            if ($request->filled('tour_plan_id')) {
                $history = TourPlanHistory::query()
                    ->where('tour_plan_id', $request->tour_plan_id)
                    ->latest('id')
                    ->limit(4)
                    ->get()
                    ->map(fn($a)=>[
                        'id'=>$a->id,
                        'role'=>$a->type,
                        'parts'=>[
                            'text'=>$a->data
                        ]
                    ])
                    ->sortBy('id')
                    ->values()
                    ->map(fn($a)=>[
                        //'id'=>$a['id'],
                        'role'=>$a['role'],
                        'parts'=>[
                            'text'=>collect($a['parts'])->toArray()
                        ]
                    ]);
                //new prompt push history
                $history->push([
                    'role'=>'user',
                    'parts'=>[
                        'text'=>$request->prompt
                    ]
                ]);
                //return $history;
            }
            else{
                $plan = TourPlan::create([
                    'user_id' => auth()->id(),
                    'payload' => $request->except('prompt') ?? $request->prompt,
                ]);
                $request->merge([
                    'tour_plan_id' => $plan->id,
                ]);
                $history = [];
                $history[] = [
                    'role'=>'user',
                    'parts'=>[
                        'text'=>$request->prompt
                    ]
                ];
                //return $history;
            }
            //$tourPlans = OpenAIAPI::generate($request->prompt);
            //Log::info('Tour Plan History: ', [$history]);
            $response = Http::post($url, [
                'contents' => $history,
            ]);
            if ($response->ok()) {
                $result = collect($response->json())->toArray();
                $tourPlans = $result['candidates'][0]['content']['parts'][0]['text'];
                //$results = $this->markdownToHtml($tourPlans);
                TourPlanHistory::create([
                    'tour_plan_id' => $request->tour_plan_id,
                    'data' => $request->prompt,
                    'type' => 'user'
                ]);
                TourPlanHistory::create([
                    'tour_plan_id' => $request->tour_plan_id,
                    'data' => $tourPlans,
                    'type' => 'model'
                ]);
                //DB::commit();
            }
            else{
                return response()->json([
                    'status_code'=> 403,
                    'errors' => $response->body()
                ], 403);
            }
            return response()->json([
                'status_code'=> 200,
                'tour_plan_id' => $request->tour_plan_id,
                'data' => $response->ok() ? $this->markdownToHtml($tourPlans) : $response->body(),
            ], 200);
        }catch (\Exception $e) {
            //DB::rollBack();
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    public function markdownToHtmlNew(string $markdown): string {
        $html = nl2br(htmlspecialchars($markdown));

        $html = preg_replace([
            '/\*\*(.*?)\*\*/',             // Bold
            '/\*(.*?)\*/',                 // Italic
            '/^## (.*)$/m',                // H2
            '/^\* (.*)$/m',                // List items
            '/<li>(.*?)<\/li>/'           // Add arrow
        ], [
            '<b>$1</b>',
            '<i>$1</i>',
            '<h2>$1</h2>',
            '<li>$1</li>',
            '<li>&rarr; $1</li>'
        ], $html);

        // Wrap <li> in <ul>
        $html = preg_replace_callback('/((?:<li>.*?<\/li>\s*)+)/', fn($m) => '<ul>' . $m[1] . '</ul>', $html);

        // Horizontal rule
        $html = str_replace('---', '<hr>', $html);

        return $html;
    }

    public function markdownToHtml(string $markdown): string {
        $html = nl2br(htmlspecialchars($markdown));
        $html = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<i>$1</i>', $html);
        $html = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^\* (.*)$/m', '<li>$1</li>', $html); // Corrected regex
        $html = preg_replace('/(<li>.*?<\/li>(\s*<br \/>\s*))+/', '<ul>$0</ul>', $html);
        $html = str_replace("<br />\n<ul>", "<ul>", $html);
        $html = str_replace("</ul><br />", "</ul>", $html);
        $html = str_replace("---", "<hr>", $html);
        return $html;
    }

    public function list(Request $request)
    {
        try {
            $plans = TourPlan::with(['history'])
                ->where('user_id', auth()->id())
                //->whereHas('history')
                ->latest()
                ->paginate($request->per_page ?? PaginationEnum::Default->value)
                ->through(fn($plan) => [
                    'id' => $plan->id,
                    'prompt' => $plan?->history?->data,
                ]);
            return response()->json([
                'status_code'=> 200,
                'data' => $plans,
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    public function details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_plan_id' => 'required|integer|exists:tour_plans,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $plan = TourPlanHistory::query()
                ->where('tour_plan_id', $request->tour_plan_id)
                ->whereHas('plan',fn($a)=>$a->where('user_id', auth()->id()))
                ->get();
            return response()->json([
                'status_code'=> 200,
                'data' => $plan,
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
}
