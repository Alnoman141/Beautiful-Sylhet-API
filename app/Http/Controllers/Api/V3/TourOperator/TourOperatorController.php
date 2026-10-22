<?php

namespace App\Http\Controllers\Api\V3\TourOperator;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\TourOperator;
use App\Models\TourOperatorParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TourOperatorController extends Controller
{
    // get all tour operators

    public function index(Request $request)
    {
        try {

            $otherLang = $this->lang == 1 ? 2 : 1;

            $tourOperators = TourOperator::query()
                ->with(['parent' => fn($a) => $a->withAvg('ratings', 'rating')
                    ->with(['operators' => fn($b) => $b->where('language_id', $otherLang)->select('tour_operator_parent_id', 'organization_name', 'name')])])
                ->where('language_id', $this->lang)
                ->when($request->filled('organization_name'), fn($q) => $q->where('organization_name', 'like', '%' . $request->organization_name . '%'))
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('email'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('email', 'like', '%' . $request->email . '%')))
                ->when($request->filled('phone_number'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('phone_number', 'like', '%' . $request->phone_number . '%')))
                ->when($request->filled('license_number'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('license_number', 'like', '%' . $request->license_number . '%')))
                ->when($request->filled('tin_number'), fn($q) => $q->whereHas('parent', fn($p) => $p->where('tin_number', 'like', '%' . $request->tin_number . '%')))
                ->when($request->filled('service_location'), fn($q) => $q->whereHas('parent', fn($p) => $p->whereJsonContains('service_locations', $request->service_location)))
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(fn($o) => [
                    'id'                    => $o->id,
                    'parent_id'             => $o->tour_operator_parent_id,
                    'organization_name'     => $o->organization_name,
                    'organization_name_en'  => $o->parent?->operators->first()?->organization_name ?? '',
                    'name'                  => $o->name,
                    'name_en'               => $o->parent?->operators->first()?->name ?? '',
                    'address'               => $o->address ?? '',
                    'description'           => $o->description ?? '',
                    'email'                 => $o->parent?->email ?? '',
                    'phone'                 => $o->parent?->phone_number ?? '',
                    'website'               => $o->parent?->website ?? '',
                    'logo'                  => $o->parent?->logo,
                    'license_number'        => $o->parent?->license_number ?? '',
                    'license_document'      => $o->parent?->license_document,
                    'tin_number'            => $o->parent?->tin_number ?? '',
                    'tin_document'          => $o->parent?->tin_document,
                    'service_locations'     => $o->parent?->service_locations ?? [],
                    'ratings'               => (float) ($o->parent?->ratings_avg_rating ?? 0),
                ]);

            return response()->json([
                'status_code' => 200,
                'data' => $tourOperators,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    // store a new tour operator


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array|min:1',
            'data.*.language_id' => [
                'required',
                'integer',
                'distinct',
                'exists:languages,id',
            ],
            'data.*.organization_name' => 'required|string|max:255',
            'data.*.name' => 'required|string|max:255',
            'data.*.address' => 'required|string|max:255',
            'data.*.description' => 'nullable|string',

            'email' => 'required|email|unique:tour_operator_parents,email',
            'phone_number' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',

            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'license_number' => 'nullable|string|max:100',
            'license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'tin_number' => 'nullable|string|max:100',
            'tin_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            'service_locations' => 'nullable|array',
            'service_locations.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $parent = TourOperatorParent::create([
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'website' => $validated['website'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'tin_number' => $validated['tin_number'] ?? null,
                'service_locations' => $validated['service_locations'] ?? [],
                'logo' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'tour-operators') : null,
                'license_document' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'tour-operators/licenses') : null,
            ]);

            foreach ($validated['data'] as $data) {
                $parent->operators()->create([
                    'language_id' => $data['language_id'],
                    'organization_name' => $data['organization_name'],
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'description' => $data['description'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operator created successfully',
                'data' => $parent->load('operators'),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function show(Request $request)
    {
        try {
            $parent = TourOperatorParent::with(['operators'])
                ->findOrFail($request->id);

            return response()->json([
                'status_code' => 200,
                'data' => $parent,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }


    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:tour_operator_parents,id',

            // Shared parent fields
            'email' => [
                'required',
                'email',
                Rule::unique('tour_operator_parents', 'email')
                    ->ignore($request->id),
            ],
            'phone_number' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'license_number' => 'nullable|string|max:100',
            'license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'tin_number' => 'nullable|string|max:100',
            'tin_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'service_locations' => 'nullable|array',
            'service_locations.*' => 'string|max:255',

            // Translated fields
            'data' => 'required|array|min:1',
            'data.*.id' => 'required|integer|exists:tour_operators,id',
            'data.*.language_id' => 'required|integer|distinct|exists:languages,id',
            'data.*.organization_name' => 'required|string|max:255',
            'data.*.name' => 'required|string|max:255',
            'data.*.address' => 'required|string|max:255',
            'data.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $parent = TourOperatorParent::findOrFail($validated['id']);

            // Only touch the image if a new file was actually uploaded.
            if ($request->hasFile('logo')) {
                $newImage = $request->hasFile('logo') ? Fa::uploadImage($request->file('logo'), 'tour-operators') : null;

                $oldImage = $parent->logo;

                $parent->update(['logo' => $newImage], ['map' => $validated['map'] ?? null]);
            }

            // Update shared information
            $parent->update([
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'website' => $validated['website'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'tin_number' => $validated['tin_number'] ?? null,
                'service_locations' => $validated['service_locations'] ?? [],
            ]);

            // Delete the old file only after the transaction succeeded.
            if (!empty($oldImage)) {
                Storage::disk('s3')->delete($oldImage);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operator updated successfully',
                'data' => $parent->load('operators'),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    /**
     * Fetch a tour operator by ID.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchTourOperator(Request $request)
    {
        try {
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

            $languageId = $request->input('language_id') ?: 1;
            $otherLang = $languageId == 1 ? 2 : 1;

            $tourOperators = TourOperator::query()
                ->with(['parent' => fn($a) => $a->withAvg('ratings', 'rating')
                    ->with(['operators' => fn($b) => $b->where('language_id', $otherLang)->select('tour_operator_parent_id', 'organization_name', 'name')])])
                ->where('language_id', $languageId)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(fn($o) => [
                    'id'                   => $o->id,
                    'parent_id'            => $o->tour_operator_parent_id,
                    'language_id'          => $o->language_id,
                    'organization_name'    => $o->organization_name,
                    'organization_name_en' => $o->parent?->operators->first()?->organization_name ?? '',
                    'name'                 => $o->name,
                    'name_en'              => $o->parent?->operators->first()?->name ?? '',
                    'address'              => $o->address ?? '',
                    'description'          => $o->description ?? '',
                    'email'                => $o->parent?->email ?? '',
                    'phone'                => $o->parent?->phone_number ?? '',
                    'website'              => $o->parent?->website ?? '',
                    'logo'                 => $o->parent?->logo,
                    'license_number'       => $o->parent?->license_number ?? '',
                    'license_document'     => $o->parent?->license_document,
                    'tin_number'           => $o->parent?->tin_number ?? '',
                    'tin_document'         => $o->parent?->tin_document,
                    'service_locations'    => $o->parent?->service_locations ?? [],
                    'ratings'              => (float) ($o->parent?->ratings_avg_rating ?? 0),
                ]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operators fetched successfully',
                'data' => $tourOperators,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function fetchTourOperatorDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'language_id' => [
                    'nullable',
                    'integer',
                    'exists:languages,id',
                ],
                'id' => [
                    'required',
                    'integer',
                    'exists:tour_operator_parents,id',
                ],
            ]);

            $id = $request->input('id');

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Default language ID is 1
            $languageId = $request->input('language_id') ?: 1;

            $parent = TourOperatorParent::query()
                ->whereHas('operators', function ($query) use ($languageId) {
                    $query->where('language_id', $languageId);
                })
                ->with([
                    'operators' => function ($query) use ($languageId) {
                        $query->where('language_id', $languageId);
                    },
                    'ratings' => function ($query) {
                        $query->select('id', 'tour_operator_parent_id', 'user_id', 'rating', 'review')
                            ->with('user:id,first_name,last_name');
                    },
                ])
                ->find($id);

            if (!$parent || $parent->operators->isEmpty()) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Tour operator not found',
                ], 404);
            }

            $operator = $parent->operators->first();

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operator details fetched successfully',
                'data' => [
                    'id' => $parent->id,
                    'translation_id' => $operator->id,
                    'language_id' => $operator->language_id,
                    'organization_name' => $operator->organization_name,
                    'name' => $operator->name,
                    'address' => $operator->address,
                    'description' => $operator->description,

                    'email' => $parent->email,
                    'phone_number' => $parent->phone_number,
                    'website' => $parent->website,
                    'logo' => $parent->logo,
                    'license_number' => $parent->license_number,
                    'license_document' => $parent->license_document,
                    'tin_number' => $parent->tin_number,
                    'tin_document' => $parent->tin_document,
                    'service_locations' => $parent->service_locations,
                    'ratings' => $parent->ratings ?? [],
                ],
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDropdown(Request $request)
    {
        try {
            $languageId = $request->input('language_id', 1);

            $tourOperators = TourOperator::query()
                ->where('language_id', $languageId)
                ->with('parent:id,logo')
                ->get()
                ->map(function ($operator) {
                    return [
                        'id' => $operator->id,
                        'name' => $operator->name,
                        'organization_name' => $operator->organization_name,
                        'parent_id' => $operator->tour_operator_parent_id,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'data' => $tourOperators,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}
