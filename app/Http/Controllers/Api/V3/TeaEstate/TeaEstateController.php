<?php

namespace App\Http\Controllers\Api\V3\TeaEstate;

use App\Http\Controllers\Controller;
use App\Models\TeaEstate;
use App\Models\TeaEstateParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Fa;
use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Storage;

class TeaEstateController extends Controller
{
    /**
     * Admin: List tea estates with all translations.
     */
    public function index(Request $request)
    {
        try {
            $estates = TeaEstate::with([
                'parent' => fn($a) => $a->select('id', 'image')
                    ->with([
                        'teaEstates' => fn($q) => $q
                            ->select(['tea_estate_parent_id', 'name'])
                            ->where('language_id', $this->lang == 1 ? 2 : 1)
                    ])
            ])
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('location'), fn($q) => $q->where('location', 'like', '%' . $request->location . '%'))
                ->when($request->filled('id'), fn($q) => $q->where('id', $request->id))
                ->where('language_id', $this->lang)
                ->select('id', 'name', 'tea_estate_parent_id', 'location', 'contact_info', 'distance')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($estate) {
                    return [
                        'id'        => $estate->id,
                        'parent_id' => $estate->tea_estate_parent_id,
                        'name'      => $estate->name,
                        'location'  => $estate->location,
                        'distance'    => $estate->distance,
                        'contact_info' => $estate->contact_info,
                        'name_en'   => collect($estate?->parent?->teaEstates)->pluck('name')->first(),
                        'image'     => $estate?->parent?->image,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'data' => $estates,
            ], 200);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Admin: Show one tea estate with all translations.
     * $id is tea_estate_parents.id.
     */
    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => ['required', 'integer', 'exists:tea_estate_parents,id'],
            ]);

            $parent = TeaEstateParent::with('teaEstates')->find($validated['id']);

            if (!$parent || $parent->teaEstates->isEmpty()) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Tea estate not found',
                ], 404);
            }

            return response()->json([
                'status_code' => 200,
                'data' => $this->formatParent($parent),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Admin: Create a tea estate with multilingual translations.
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
            'data.*.contact_info' => ['nullable', 'string', 'max:255'],
            'data.*.map' => ['nullable', 'string', 'max:255'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'map' => 'nullable|string|max:255',
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

            $parent = TeaEstateParent::create([
                'map' => $validated['map'] ?? null,
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'spots') : null,
            ]);

            foreach ($validated['data'] as $translation) {
                $parent->teaEstates()->create([
                    'language_id' => $translation['language_id'],
                    'name' => $translation['name'],
                    'location' => $translation['location'],
                    'description' => $translation['description'] ?? null,
                    'distance' => $translation['distance'] ?? null,
                    'contact_info' => $translation['contact_info'] ?? null,
                ]);
            }
            DB::commit();

            // return $parent->load('teaEstates');

            return response()->json([
                'status_code' => 201,
                'message' => 'Tea estate created successfully',
                'data' => $this->formatParent($parent),
            ], 201);
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
     * Admin: Update a tea estate and its existing translations.
     *
     * id = tea_estate_parents.id
     * data.*.id = tea_estates.id
     */
    public function update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => ['required', 'integer', 'exists:tea_estate_parents,id'],

        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        'map' => 'nullable|string|max:255',

        'data' => ['required', 'array', 'min:1'],

        'data.*.id' => ['required', 'integer', 'distinct', 'exists:tea_estates,id'],
        'data.*.language_id' => ['required', 'integer', 'distinct', 'exists:languages,id'],
        'data.*.name' => ['required', 'string', 'max:255'],
        'data.*.location' => ['required', 'string', 'max:255'],
        'data.*.description' => ['nullable', 'string'],
        'data.*.distance' => ['nullable', 'string', 'max:255'],
        'data.*.contact_info' => ['nullable', 'string', 'max:255'],
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

        $parent = TeaEstateParent::findOrFail($validated['id']);

        // Only touch the image if a new file was actually uploaded.
        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('tea_estates', 'public');

            $oldImage = $parent->image;

            $parent->update(['image' => $newImage], ['map' => $validated['map'] ?? null]);
        }

        foreach ($validated['data'] as $translation) {
            // Only update translations belonging to this parent.
            $estate = $parent->teaEstates()
                ->where('id', $translation['id'])
                ->firstOrFail();

            $estate->update([
                'language_id' => $translation['language_id'],
                'name' => $translation['name'],
                'location' => $translation['location'],
                'description' => $translation['description'] ?? null,
                'distance' => $translation['distance'] ?? null,
                'contact_info' => $translation['contact_info'] ?? null,
            ]);
        }

        DB::commit();

        // Delete the old file only after the transaction succeeded.
        if (!empty($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        return response()->json([
            'status_code' => 200,
            'message' => 'Tea estate updated successfully',
            'data' => $this->formatParent($parent->fresh()),
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();

        // Remove the newly uploaded file if the transaction failed.
        if (isset($newImage)) {
            Storage::disk('public')->delete($newImage);
        }

        return response()->json([
            'status_code' => 404,
            'message' => 'Tea estate or translation not found',
        ], 404);
    } catch (\Throwable $e) {
        report($e);

        DB::rollBack();

        if (isset($newImage)) {
            Storage::disk('public')->delete($newImage);
        }

        return response()->json([
            'status_code' => 500,
            'message' => 'Something went wrong ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Public mobile API: List tea estates in a selected language.
     * Defaults to language_id = 1.
     */
    public function fetchTeaEstate(Request $request)
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

            $teaEstates = TeaEstate::query()
                ->where('language_id', $languageId)
                ->get()
                ->map(fn($estate) => $this->formatPublicEstate($estate))
                ->values();

            return response()->json([
                'status_code' => 200,
                'message' => 'Tea estates fetched successfully',
                'data' => $teaEstates,
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
     * Public mobile API: Fetch one tea estate in a selected language.
     * $id = tea_estate_parents.id.
     * Defaults to language_id = 1.
     */
    public function fetchTeaEstateDetails(Request $request)
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
                'exists:tea_estate_parents,id',
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

            $estate = TeaEstate::query()
                ->where('tea_estate_parent_id', $request->input('id'))
                ->where('language_id', $languageId)
                ->first();

            if (!$estate) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Tea estate not found for the selected language',
                ], 404);
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Tea estate details fetched successfully',
                'data' => $this->formatPublicEstate($estate),
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin response: parent plus all translations.
     */
    private function formatParent(TeaEstateParent $parent): array
    {
        return [
            'id'         => $parent->id,
            'image'      => $parent->image,
            'map'   => $parent->map,
            'data' => $parent->teaEstates
                ->map(fn($t) => [
                    'id'                    => $t->id,
                    'tea_estate_parent_id'  => $t->tea_estate_parent_id,
                    'language_id'           => $t->language_id,
                    'name'                  => $t->name,
                    'location'              => $t->location,
                    'description'           => $t->description,
                    'distance'              => $t->distance,
                    'contact_info'          => $t->contact_info,
                    'created_at'            => $t->created_at,
                    'updated_at'            => $t->updated_at,
                ])
                ->values(),
        ];
    }

    /**
     * Public response: one language-specific tea estate.
     */
    private function formatPublicEstate(TeaEstate $estate): array
    {
        return [
            'id' => $estate->tea_estate_parent_id,
            'translation_id' => $estate->id,
            'language_id' => $estate->language_id,
            'name' => $estate->name,
            'location' => $estate->location,
            'description' => $estate->description,
            'distance' => $estate->distance,
            'contact_info' => $estate->contact_info,
            'map' => $estate->map,
        ];
    }
}
