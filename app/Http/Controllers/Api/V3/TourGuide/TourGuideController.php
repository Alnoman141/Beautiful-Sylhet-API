<?php

namespace App\Http\Controllers\Api\V3\TourGuide;

use App\Http\Controllers\Controller;
use App\Models\TourGuide;
use App\Models\TourGuideParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enum\PaginationEnum;

class TourGuideController extends Controller
{
    /**
     * Admin: List all tour guides with all translations.
     */
    public function index(Request $request)
    {
        try {
            $otherLang = $this->lang == 1 ? 2 : 1;

            $tourGuides = TourGuide::query()
                ->with(['parent' => fn($a) => $a->withAvg('ratings', 'rating')
                    ->with(['guides' => fn($b) => $b->where('language_id', $otherLang)->select('tour_guide_parent_id', 'name')])])
                ->where('language_id', $this->lang)
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('email'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('email', 'like', '%' . $request->email . '%')))
                ->when($request->filled('phone_number'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('phone_number', 'like', '%' . $request->phone_number . '%')))
                ->when($request->filled('license_number'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('license_number', 'like', '%' . $request->license_number . '%')))
                ->when($request->filled('service_location'), fn($q) => $q->whereHas('parent', fn($p) => $p->whereJsonContains('service_locations', $request->service_location)))
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(fn($g) => [
                    'id'               => $g->id,
                    'parent_id'        => $g->tour_guide_parent_id,
                    'name'             => $g->name,
                    'name_en'          => $g->parent?->guides->first()?->name ?? '',
                    'phone'            => $g->parent?->phone_number ?? '',
                    'email'            => $g->parent?->email ?? '',
                    'license_number'   => $g->parent?->license_number ?? '',
                    'address'          => $g->address ?? '',
                    'description'      => $g->description ?? '',
                    'service_locations' => $g->parent?->service_locations ?? [],
                    'image'            => $g->parent?->image,
                    'ratings'          => (float) ($g->parent?->ratings_avg_rating ?? 0),
                ]);

            return response()->json([
                'status_code' => 200,
                'data' => $tourGuides,
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
     * Admin: Show one tour guide parent with all translations.
     */
    public function show(Request $request)
    {
        try {
            $parent = TourGuideParent::with('guides')->find($request->input('id'));

            if (!$parent) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Tour guide not found',
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
     * Admin: Create a tour guide with multilingual translations.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:255',
                'unique:tour_guide_parents,phone_number',
            ],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'license_document' => ['nullable', 'file', 'max:5120'],
            'service_locations' => ['nullable', 'array'],
            'rate_per_day' => ['nullable', 'numeric', 'min:0'],

            'data' => ['required', 'array', 'min:1'],
            'data.*.language_id' => [
                'required',
                'integer',
                'distinct',
                'exists:languages,id',
            ],
            'data.*.name' => ['required', 'string', 'max:255'],
            'data.*.description' => ['nullable', 'string'],
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

            $parent = DB::transaction(function () use (
                $request,
                $validated
            ) {
                $parent = TourGuideParent::create([
                    'email' => $validated['email'] ?? null,
                    'phone_number' => $validated['phone_number'],
                    'website' => $validated['website'] ?? null,
                    'logo' => $request->hasFile('logo')
                        ? $request->file('logo')->store('tour-guides/logos', 'public')
                        : null,
                    'license_number' => $validated['license_number'] ?? null,
                    'license_document' => $request->hasFile('license_document')
                        ? $request->file('license_document')->store('tour-guides/licenses', 'public')
                        : null,
                    'service_locations' => $validated['service_locations'] ?? null,
                    'rate_per_day' => $validated['rate_per_day'] ?? null,
                ]);

                foreach ($validated['data'] as $translation) {
                    $parent->guides()->create([
                        'language_id' => $translation['language_id'],
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                    ]);
                }

                return $parent->load('guides');
            });

            return response()->json([
                'status_code' => 201,
                'message' => 'Tour guide created successfully',
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
     * Admin: Update shared information and existing translations.
     *
     * The id is the tour_guide_parents.id.
     * Each data.*.id is the corresponding tour_guides.id.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'integer',
                'exists:tour_guide_parents,id',
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tour_guide_parents', 'phone_number')
                    ->ignore($request->input('id')),
            ],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'license_document' => ['nullable', 'file', 'max:5120'],
            'service_locations' => ['nullable', 'array'],
            'rate_per_day' => ['nullable', 'numeric', 'min:0'],

            'data' => ['required', 'array', 'min:1'],
            'data.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:tour_guides,id',
            ],
            'data.*.language_id' => [
                'required',
                'integer',
                'distinct',
                'exists:languages,id',
            ],
            'data.*.name' => ['required', 'string', 'max:255'],
            'data.*.description' => ['nullable', 'string'],
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

            $parent = DB::transaction(function () use (
                $request,
                $validated
            ) {
                $parent = TourGuideParent::findOrFail($validated['id']);

                $parentData = [
                    'email' => $validated['email'] ?? null,
                    'phone_number' => $validated['phone_number'],
                    'website' => $validated['website'] ?? null,
                    'license_number' => $validated['license_number'] ?? null,
                    'service_locations' => $validated['service_locations'] ?? null,
                    'rate_per_day' => $validated['rate_per_day'] ?? null,
                ];

                if ($request->hasFile('logo')) {
                    $newLogo = $request->file('logo')
                        ->store('tour-guides/logos', 'public');

                    if ($parent->logo) {
                        Storage::disk('public')->delete($parent->logo);
                    }

                    $parentData['logo'] = $newLogo;
                }

                if ($request->hasFile('license_document')) {
                    $newDocument = $request->file('license_document')
                        ->store('tour-guides/licenses', 'public');

                    if ($parent->license_document) {
                        Storage::disk('public')->delete(
                            $parent->license_document
                        );
                    }

                    $parentData['license_document'] = $newDocument;
                }

                $parent->update($parentData);

                foreach ($validated['data'] as $translation) {
                    $guide = $parent->guides()
                        ->where('id', $translation['id'])
                        ->firstOrFail();

                    $guide->update([
                        'language_id' => $translation['language_id'],
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                    ]);
                }

                return $parent->load('guides');
            });

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour guide updated successfully',
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
     * Public mobile API: List guides in the selected language.
     * Defaults to language ID 1.
     */
    public function fetchTourGuide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
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
            $otherLang = $languageId == 1 ? 2 : 1;

            $tourGuides = TourGuide::query()
                ->with(['parent' => fn($a) => $a->withAvg('ratings', 'rating')
                    ->with(['guides' => fn($b) => $b->where('language_id', $otherLang)->select('tour_guide_parent_id', 'name')])])
                ->where('language_id', $languageId)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(fn($g) => [
                    'id'               => $g->id,
                    'parent_id'        => $g->tour_guide_parent_id,
                    'language_id'      => $g->language_id,
                    'name'             => $g->name,
                    'name_en'          => $g->parent?->guides->first()?->name ?? '',
                    'description'      => $g->description ?? '',
                    'email'            => $g->parent?->email ?? '',
                    'phone'            => $g->parent?->phone_number ?? '',
                    'website'          => $g->parent?->website ?? '',
                    'logo'             => $g->parent?->logo,
                    'license_number'   => $g->parent?->license_number ?? '',
                    'license_document' => $g->parent?->license_document,
                    'service_locations' => $g->parent?->service_locations ?? [],
                    'rate_per_day'     => $g->parent?->rate_per_day ?? 0,
                    'ratings'          => (float) ($g->parent?->ratings_avg_rating ?? 0),
                ]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour guides fetched successfully',
                'data' => $tourGuides,
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
     * Public mobile API: Fetch one guide in the selected language.
     * The route ID is tour_guide_parents.id.
     * Defaults to language ID 1.
     */
    public function fetchTourGuideDetails(Request $request)
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
                'exists:tour_guide_parents,id',
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

            $parent = TourGuideParent::query()
                ->whereHas('guides', function ($query) use ($languageId) {
                    $query->where('language_id', $languageId);
                })
                ->with([
                    'guides' => function ($query) use ($languageId) {
                        $query->where('language_id', $languageId);
                    },
                    'ratings' => function ($query) {
                        $query->select('id', 'tour_guide_parent_id', 'user_id', 'rating', 'review')
                            ->with('user:id,first_name,last_name');
                    },
                ])
                ->find($request->input('id'));

            if (!$parent || $parent->guides->isEmpty()) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Tour guide not found',
                ], 404);
            }

            $guide = $parent->guides->first();

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour guide details fetched successfully',
                'data' => [
                    'id' => $parent->id,
                    'translation_id' => $guide->id,
                    'language_id' => $guide->language_id,
                    'name' => $guide->name,
                    'description' => $guide->description,

                    'email' => $parent->email,
                    'phone_number' => $parent->phone_number,
                    'website' => $parent->website,
                    'logo' => $parent->logo,
                    'license_number' => $parent->license_number,
                    'license_document' => $parent->license_document,
                    'service_locations' => $parent->service_locations,
                    'rate_per_day' => $parent->rate_per_day,
                    'ratings' => $parent->ratings ?? [],
                ],
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
     * Format a parent with all translations.
     */
    private function formatParent(TourGuideParent $parent): array
    {
        return [
            'id' => $parent->id,
            'email' => $parent->email,
            'phone_number' => $parent->phone_number,
            'website' => $parent->website,
            'logo' => $parent->logo,
            'license_number' => $parent->license_number,
            'license_document' => $parent->license_document,
            'service_locations' => $parent->service_locations,
            'rate_per_day' => $parent->rate_per_day,

            'translations' => $parent->guides->map(function ($guide) {
                return [
                    'id' => $guide->id,
                    'language_id' => $guide->language_id,
                    'name' => $guide->name,
                    'description' => $guide->description,
                ];
            })->values(),
        ];
    }
}
