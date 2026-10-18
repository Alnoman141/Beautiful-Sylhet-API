<?php

namespace App\Http\Controllers\Api\V3\Haor;

use App\Http\Controllers\Controller;
use App\Models\Haor;
use App\Models\HaorParent;
use App\Models\Spot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Fa;
use App\Services\Weather;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Enum\PaginationEnum;

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
                ->whereHas('parent.spotTypeParents.types', fn($q) => $q->where('name', 'Haor'))
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
                'message' => 'Haors fetched successfully',
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
     * Public mobile API: Fetch one haor in the selected language.
     * $id = haor_parents.id.
     * Default language ID is 1.
     */
    public function fetchHaorDetails(Request $request)
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
