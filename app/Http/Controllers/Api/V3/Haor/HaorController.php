<?php

namespace App\Http\Controllers\Api\V3\Haor;

use App\Http\Controllers\Controller;
use App\Models\Haor;
use App\Models\HaorParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Fa;
use Illuminate\Support\Facades\Storage;

class HaorController extends Controller
{
    /**
     * Admin: List all haors with all translations.
     */
    public function index(Request $request)
    {
        try {
            $haors = HaorParent::query()
                ->with('haors')
                ->when(
                    $request->filled('name'),
                    fn($query) => $query->whereHas(
                        'haors',
                        fn($haor) => $haor->where(
                            'name',
                            'like',
                            '%' . $request->input('name') . '%'
                        )
                    )
                )
                ->when(
                    $request->filled('location'),
                    fn($query) => $query->whereHas(
                        'haors',
                        fn($haor) => $haor->where(
                            'location',
                            'like',
                            '%' . $request->input('location') . '%'
                        )
                    )
                )
                ->get()
                ->map(fn($parent) => $this->formatParent($parent))
                ->values();

            return response()->json([
                'status_code' => 200,
                'data' => $haors,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Admin: Show one haor with all translations.
     * ID is haor_parents.id.
     */
    public function show(Request $request)
    {
        try {
            $parent = HaorParent::with('haors')->find($request->input('id'));

            if (!$parent || $parent->haors->isEmpty()) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Haor not found',
                ], 404);
            }

            return response()->json([
                'status_code' => 200,
                'data' => $this->formatParent($parent),
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Admin: Create a haor with multilingual translations.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => ['required', 'array', 'min:1'],

            'data.*.language_id' => [
                'required',
                'integer',
                'distinct',
                'exists:languages,id',
            ],

            'data.*.name' => ['required', 'string', 'max:255'],
            'data.*.location' => ['required', 'string', 'max:255'],
            'data.*.description' => ['nullable', 'string'],
            'data.*.distance' => ['nullable', 'string', 'max:255'],
            'data.*.suitable_visiting_time' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();


            DB::beginTransaction();
            $parent = HaorParent::create([
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'haor') : null,
            ]);

            foreach ($validated['data'] as $translation) {
                $parent->haors()->create([
                    'language_id' => $translation['language_id'],
                    'name' => $translation['name'],
                    'location' => $translation['location'],
                    'description' => $translation['description'] ?? null,
                    'distance' => $translation['distance'] ?? null,
                    'suitable_visiting_time' =>
                    $translation['suitable_visiting_time'] ?? null,
                ]);
            }


            DB::commit();
            return response()->json([
                'status_code' => 201,
                'message' => 'Haor created successfully',
                'data' => $this->formatParent($parent),
            ], 201);
        } catch (\Throwable $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong ' . $e->getMessage() . ' ' . $e->getLine(),
            ], 500);
        }
    }

    /**
     * Admin: Update a haor and its existing translations.
     * id = haor_parents.id
     * data.*.id = haors.id
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'integer',
                'exists:haor_parents,id',
            ],

            'data' => ['required', 'array', 'min:1'],

            'data.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:haors,id',
            ],

            'data.*.language_id' => [
                'required',
                'integer',
                'distinct',
                'exists:languages,id',
            ],

            'data.*.name' => ['required', 'string', 'max:255'],
            'data.*.location' => ['required', 'string', 'max:255'],
            'data.*.description' => ['nullable', 'string'],
            'data.*.distance' => ['nullable', 'string', 'max:255'],
            'data.*.suitable_visiting_time' => [
                'nullable',
                'string',
                'max:255',
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            DB::beginTransaction();

            $parent = HaorParent::findOrFail($validated['id']);

            // Only touch the image if a new file was actually uploaded.
            if ($request->hasFile('image')) {
                $newImage = $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'haor') : null;

                $oldImage = $parent->image;

                $parent->update(['image' => $newImage]);
            }

            foreach ($validated['data'] as $translation) {
                // Ensure the translation belongs to this parent.
                $haor = $parent->haors()
                    ->where('id', $translation['id'])
                    ->firstOrFail();

                $haor->update([
                    'language_id' => $translation['language_id'],
                    'name' => $translation['name'],
                    'location' => $translation['location'],
                    'description' => $translation['description'] ?? null,
                    'distance' => $translation['distance'] ?? null,
                    'suitable_visiting_time' =>
                    $translation['suitable_visiting_time'] ?? null,
                ]);
            }

            // Delete the old file only after the transaction succeeded.
            if (!empty($oldImage)) {
                Storage::disk('s3')->delete($oldImage);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Haor updated successfully',
                'data' => $this->formatParent($parent),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 404,
                'message' => 'Haor or translation not found',
            ], 404);
        } catch (\Throwable $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Public mobile API: List haors in the selected language.
     * Default language ID is 1.
     */
    public function fetchHaor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => [
                'nullable',
                'integer',
                'exists:languages,id',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $languageId = $request->input('language_id') ?: 1;

            $haors = Haor::query()
                ->where('language_id', $languageId)
                ->get()
                ->map(fn($haor) => $this->formatPublicHaor($haor))
                ->values();

            return response()->json([
                'status_code' => 200,
                'message' => 'Haors fetched successfully',
                'data' => $haors,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Public mobile API: Fetch one haor in the selected language.
     * $id = haor_parents.id.
     * Default language ID is 1.
     */
    public function fetchHaorDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => [
                'nullable',
                'integer',
                'exists:languages,id',
            ],
            'id' => [
                'required',
                'integer',
                'exists:haor_parents,id',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $languageId = $request->input('language_id') ?: 1;

            $haor = Haor::query()
                ->where('haor_parent_id', $request->input('id'))
                ->where('language_id', $languageId)
                ->first();

            if (!$haor) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Haor not found for the selected language',
                ], 404);
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Haor details fetched successfully',
                'data' => $this->formatPublicHaor($haor),
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Admin response: parent plus all translations.
     */
    private function formatParent(HaorParent $parent): array
    {
        return [
            'id' => $parent->id,
            'image' => $parent->image ?? null,
            'translations' => $parent->haors->map(function ($haor) {
                return [
                    'id' => $haor->id,
                    'language_id' => $haor->language_id,
                    'name' => $haor->name,
                    'location' => $haor->location,
                    'description' => $haor->description,
                    'distance' => $haor->distance,
                    'suitable_visiting_time' => $haor->suitable_visiting_time,
                ];
            })->values(),
        ];
    }

    /**
     * Public response: one language-specific haor.
     */
    private function formatPublicHaor(Haor $haor): array
    {
        return [
            'id' => $haor->haor_parent_id,
            'translation_id' => $haor->id,
            'language_id' => $haor->language_id,
            'name' => $haor->name,
            'location' => $haor->location,
            'description' => $haor->description,
            'distance' => $haor->distance,
            'suitable_visiting_time' => $haor->suitable_visiting_time,
            'image' => $haor->parent->image ?? null,
        ];
    }
}
