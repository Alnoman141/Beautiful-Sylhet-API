<?php

namespace App\Http\Controllers\Api\V3\AmusementPark;

use App\Http\Controllers\Controller;
use App\Models\AmusementPark;
use App\Models\AmusementParkParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Fa;
use App\Enum\PaginationEnum;
use App\Models\Spot;
use App\Models\SpotParent;
use App\Services\Weather;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                    fn($query) => $query->whereHas(
                        'amusementParks',
                        fn($park) => $park->where(
                            'name',
                            'like',
                            '%' . $request->input('name') . '%'
                        )
                    )
                )
                ->when(
                    $request->filled('location'),
                    fn($query) => $query->whereHas(
                        'amusementParks',
                        fn($park) => $park->where(
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

            $parent = AmusementParkParent::create([
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'park') : null,
            ]);

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

            DB::commit();

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

            $parent = AmusementParkParent::findOrFail(
                $validated['id']
            );

            // Only touch the image if a new file was actually uploaded.
            if ($request->hasFile('image')) {
                $newImage = $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'park') : null;

                $oldImage = $parent->image;

                $parent->update(['image' => $newImage]);
            }

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

            // Delete the old file only after the transaction succeeded.
            if (!empty($oldImage)) {
                Storage::disk('s3')->delete($oldImage);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Amusement park updated successfully',
                'data' => $this->formatParent($parent),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 404,
                'message' => 'Amusement park or translation not found',
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
     * Public mobile API: List parks in a selected language.
     * Default language_id = 1.
     */
    public function fetchAmusementPark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'per_page'    => ['nullable', 'integer', 'min:1'],
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
            $otherLanguageId = $languageId == 1 ? 2 : 1;

            $spots = Spot::with([
                'parent' => fn($a) => $a
                    ->select('id', 'image', 'spot_type_parent_id')
                    ->with([
                        // name in the other language (name_en)
                        'spot' => fn($q) => $q
                            ->select('spot_parent_id', 'name')
                            ->where('language_id', $otherLanguageId),

                        'ratings' => fn($r) => $r->select('spot_parent_id', 'rating'),

                        // spot type in the requested language
                        'spotTypeParents' => fn($t) => $t
                            ->select('id')
                            ->with([
                                'translate' => fn($tr) => $tr
                                    ->select('id', 'spot_type_parent_id', 'name')
                                    ->where('language_id', $languageId),
                            ]),
                    ]),
            ])
                ->whereHas('parent.spotTypeParents.types', fn($q) => $q->where('name', 'Amusement Park'))
                ->where('language_id', $languageId)
                ->select('id', 'name', 'spot_parent_id', 'tag_line')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($spot) {
                    $parent = $spot->parent;

                    return [
                        'id'        => $spot->id,
                        'parent_id' => $spot->spot_parent_id,
                        'name'      => $spot->name,
                        'tag_line'  => $spot->tag_line,
                        'name_en'   => $parent?->spot?->pluck('name')->first(),
                        'image'     => $parent?->image,
                        'spot_type' => $parent?->spotTypeParents?->translate,
                        'ratings'   => $parent?->ratings?->count() ? $parent->ratings->avg('rating') : 0,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'message' => 'Amusement parks fetched successfully',
                'data' => $spots,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong ' . $e->getMessage(),
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
        try {
            $spot = Spot::with(['parent' => [
                'images',
                'hotels' => fn($q) => $q
                    ->where('language_id', $this->lang)
                    ->select(['hotels.id as id', 'hotel_parents.id as parent_id', 'name', 'phone'])
            ]])->where('language_id', $this->lang)->findOrFail($request->id);
            // 1. Eager load parent images + parent ratings (runs the queries)
            $spot->load([
                'parent.images',
                'parent.ratings' => fn($q) => $q
                    ->select('id', 'spot_parent_id', 'user_id', 'rating', 'review') // spot_id here = parent's id
                    ->with('user:id,first_name,last_name') // load user with only id, first_name, last_name, email
            ]);

            // 2. Reshape the parent's images (only if a parent exists)
            if ($spot->parent) {
                $spot->parent->setRelation(
                    'images',
                    $spot->parent->images->map(fn($image) => [
                        'link' => $image->name,
                    ])
                );
            }

            // 3. Reshape the parent's spot type (only if a parent exists)
            if ($spot->parent) {
                $spot->parent->setRelation(
                    'spot_type',
                    $spot->parent?->spotTypeParents?->translate
                );
            }

            $map = $spot?->parent?->map_link;
            $link = Str::between($map, 'place/', '/@');
            $name = Str::lower(Str::replace('+', ' ', $link));
            return response()->json([
                'status_code' => 200,
                'data' => $spot,
                'weather' => Weather::openWeather($name, $spot?->spot_parent_id),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Admin response: parent data plus all translations.
     */
    private function formatParent(AmusementParkParent $parent): array
    {
        return [
            'id' => $parent->id,
            'image' => $parent->image ?? null,
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
            'image' => $park->parent->image ?? null,
        ];
    }
}
