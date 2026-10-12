<?php

namespace App\Http\Controllers\Api\V3\AmusementPark;

use App\Http\Controllers\Controller;
use App\Models\AmusementPark;
use App\Models\AmusementParkParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AmusementParkController extends Controller
{
    /**
     * Admin: List all amusement parks with all translations.
     */
    public function index(Request $request)
    {
        try {
            $parks = AmusementParkParent::query()
                ->with('amusementParks')
                ->when(
                    $request->filled('name'),
                    fn ($query) => $query->whereHas(
                        'amusementParks',
                        fn ($park) => $park->where(
                            'name',
                            'like',
                            '%' . $request->input('name') . '%'
                        )
                    )
                )
                ->when(
                    $request->filled('location'),
                    fn ($query) => $query->whereHas(
                        'amusementParks',
                        fn ($park) => $park->where(
                            'location',
                            'like',
                            '%' . $request->input('location') . '%'
                        )
                    )
                )
                ->get()
                ->map(fn ($parent) => $this->formatParent($parent))
                ->values();

            return response()->json([
                'status_code' => 200,
                'data' => $parks,
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
     * Admin: Show one amusement park with all translations.
     * $id is amusement_park_parents.id.
     */
    public function show(Request $request)
    {
        try {
            $parent = AmusementParkParent::with('amusementParks')
                ->find($request->input('id'));

            if (!$parent || $parent->amusementParks->isEmpty()) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Amusement park not found',
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
     * Admin: Create an amusement park with translations.
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
            'data.*.ticket_price' => ['nullable', 'string', 'max:255'],
            'data.*.opening_hours' => ['nullable', 'string', 'max:255'],
            'data.*.map' => ['nullable', 'string', 'max:255'],
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

            $parent = DB::transaction(function () use ($validated) {
                $parent = AmusementParkParent::create([]);

                foreach ($validated['data'] as $translation) {
                    $parent->amusementParks()->create([
                        'language_id' => $translation['language_id'],
                        'name' => $translation['name'],
                        'location' => $translation['location'],
                        'description' => $translation['description'] ?? null,
                        'distance' => $translation['distance'] ?? null,
                        'ticket_price' => $translation['ticket_price'] ?? null,
                        'opening_hours' => $translation['opening_hours'] ?? null,
                        'map' => $translation['map'] ?? null,
                    ]);
                }

                return $parent->load('amusementParks');
            });

            return response()->json([
                'status_code' => 201,
                'message' => 'Amusement park created successfully',
                'data' => $this->formatParent($parent),
            ], 201);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Admin: Update a park and its existing translations.
     * id = amusement_park_parents.id
     * data.*.id = amusement_parks.id
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'integer',
                'exists:amusement_park_parents,id',
            ],

            'data' => ['required', 'array', 'min:1'],

            'data.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:amusement_parks,id',
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
            'data.*.ticket_price' => ['nullable', 'string', 'max:255'],
            'data.*.opening_hours' => ['nullable', 'string', 'max:255'],
            'data.*.map' => ['nullable', 'string', 'max:255'],
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

            $parent = DB::transaction(function () use ($validated) {
                $parent = AmusementParkParent::findOrFail(
                    $validated['id']
                );

                foreach ($validated['data'] as $translation) {
                    // Ensure the translation belongs to this parent.
                    $park = $parent->amusementParks()
                        ->where('id', $translation['id'])
                        ->firstOrFail();

                    $park->update([
                        'language_id' => $translation['language_id'],
                        'name' => $translation['name'],
                        'location' => $translation['location'],
                        'description' => $translation['description'] ?? null,
                        'distance' => $translation['distance'] ?? null,
                        'ticket_price' => $translation['ticket_price'] ?? null,
                        'opening_hours' => $translation['opening_hours'] ?? null,
                        'map' => $translation['map'] ?? null,
                    ]);
                }

                return $parent->load('amusementParks');
            });

            return response()->json([
                'status_code' => 200,
                'message' => 'Amusement park updated successfully',
                'data' => $this->formatParent($parent),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Amusement park or translation not found',
            ], 404);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Public mobile API: List parks in a selected language.
     * Default language_id = 1.
     */
    public function fetchAmusementPark(Request $request)
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

            $parks = AmusementPark::query()
                ->where('language_id', $languageId)
                ->get()
                ->map(fn ($park) => $this->formatPublicPark($park))
                ->values();

            return response()->json([
                'status_code' => 200,
                'message' => 'Amusement parks fetched successfully',
                'data' => $parks,
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
     * Public mobile API: Fetch one park in a selected language.
     * $id = amusement_park_parents.id.
     * Default language_id = 1.
     */
    public function fetchAmusementParkDetails(Request $request)
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
                'exists:amusement_park_parents,id',
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

            $park = AmusementPark::query()
                ->where('amusement_park_parent_id', $request->input('id'))
                ->where('language_id', $languageId)
                ->first();

            if (!$park) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Amusement park not found for the selected language',
                ], 404);
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Amusement park details fetched successfully',
                'data' => $this->formatPublicPark($park),
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
     * Admin response: parent data plus all translations.
     */
    private function formatParent(AmusementParkParent $parent): array
    {
        return [
            'id' => $parent->id,
            'translations' => $parent->amusementParks
                ->map(function ($park) {
                    return [
                        'id' => $park->id,
                        'language_id' => $park->language_id,
                        'name' => $park->name,
                        'location' => $park->location,
                        'description' => $park->description,
                        'distance' => $park->distance,
                        'ticket_price' => $park->ticket_price,
                        'opening_hours' => $park->opening_hours,
                        'map' => $park->map,
                    ];
                })
                ->values(),
        ];
    }

    /**
     * Public response: one language-specific park record.
     */
    private function formatPublicPark(AmusementPark $park): array
    {
        return [
            'id' => $park->amusement_park_parent_id,
            'translation_id' => $park->id,
            'language_id' => $park->language_id,
            'name' => $park->name,
            'location' => $park->location,
            'description' => $park->description,
            'distance' => $park->distance,
            'ticket_price' => $park->ticket_price,
            'opening_hours' => $park->opening_hours,
            'map' => $park->map,
        ];
    }
}