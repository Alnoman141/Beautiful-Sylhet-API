<?php

namespace App\Http\Controllers\Api\V3\Association;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\AssociationParent;
use App\Models\Spot;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssociationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $otherLang = $this->lang == 1 ? 2 : 1;

            $associations = Association::with([
                'parent' => fn($p) => $p
                    ->select(
                        'id',
                        'spot_parent_id',
                        'logo',
                        'president_phone',
                        'secretary_phone',
                        'email',
                        'website'
                    )
                    ->with([
                        // English/other-language name
                        'association' => fn($q) => $q
                            ->select('association_parent_id', 'name')
                            ->where('language_id', $otherLang),
                    ]),
            ])
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('id'), fn($q) => $q->where('id', $request->id))
                ->when($request->filled('spot_parent_id'), fn($q) => $q->whereHas(
                    'parent',
                    fn($p) => $p->where('spot_parent_id', $request->spot_parent_id)
                ))
                ->where('language_id', $this->lang)
                ->select('id', 'association_parent_id', 'name', 'president_name', 'secretary_name', 'address')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($association) {
                    $parent = $association->parent;

                    return [
                        'id'              => $association->id,
                        'parent_id'       => $association->association_parent_id,
                        'name'            => $association->name,
                        'name_en'         => $parent?->association?->pluck('name')->first(),
                        'president_name'  => $association->president_name,
                        'secretary_name'  => $association->secretary_name,
                        'address'         => $association->address,
                        'logo'            => $parent?->logo,
                        'president_phone' => $parent?->president_phone,
                        'secretary_phone' => $parent?->secretary_phone,
                        'email'           => $parent?->email,
                        'website'         => $parent?->website,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'data' => $associations,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'spot_parent_id'  => 'nullable|exists:spot_parents,id',
            'president_phone' => 'nullable|string|max:255',
            'secretary_phone' => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|string|max:255',

            'data'                      => 'required|array|min:1',
            'data.*.language_id'        => 'required|distinct|exists:languages,id',
            'data.*.name'               => 'required|string|max:255',
            'data.*.president_name'     => 'nullable|string|max:255',
            'data.*.secretary_name'     => 'nullable|string|max:255',
            'data.*.address'            => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $associationParent = AssociationParent::create([
                'spot_parent_id'  => $request->spot_parent_id,
                'president_phone' => $request->president_phone,
                'secretary_phone' => $request->secretary_phone,
                'email'           => $request->email,
                'website'         => $request->website,
                'logo'            => $request->hasFile('logo')
                    ? Fa::uploadImage($request->file('logo'), 'associations')
                    : null,
            ]);

            foreach ($request->data as $data) {
                $data['association_parent_id'] = $associationParent->id;
                Association::create($data);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Association created successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:association_parents,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = AssociationParent::with('association')->findOrFail($request->id);

            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    public function details(Request $request)
    {
        try {
            $otherLang = $this->lang == 1 ? 2 : 1;

            $association = Association::with([
                'parent' => fn($p) => $p->select(
                    'id',
                    'spot_parent_id',
                    'logo',
                    'president_phone',
                    'secretary_phone',
                    'email',
                    'website'
                ),
            ])
                ->where('language_id', $this->lang)
                ->where('association_parent_id', $request->id) // id = parent id
                ->firstOrFail();

            $parent = $association->parent;

            // name in the other language
            $nameEn = Association::where('association_parent_id', $association->association_parent_id)
                ->where('language_id', $otherLang)
                ->value('name');

            // linked spot (name in the current language)
            $spot = $parent?->spot_parent_id
                ? Spot::select('spot_parent_id', 'name')
                ->where('spot_parent_id', $parent->spot_parent_id)
                ->where('language_id', $this->lang)
                ->first()
                : null;

            $data = [
                'id'              => $association->id,
                'parent_id'       => $association->association_parent_id,
                'name'            => $association->name,
                'name_en'         => $nameEn,
                'president_name'  => $association->president_name,
                'secretary_name'  => $association->secretary_name,
                'president_phone' => $parent?->president_phone,
                'secretary_phone' => $parent?->secretary_phone,
                'address'         => $association->address,
                'email'           => $parent?->email,
                'website'         => $parent?->website,
                'logo'            => $parent?->logo,
                'parent'          => $parent ? [
                    'id'              => $parent->id,
                    'spot_parent_id'  => $parent->spot_parent_id,
                ] : null,
                'spot'            => [
                    'spot_parent_id' => $parent?->spot_parent_id,
                    'name'      => $spot?->name,
                ],
            ];

            return response()->json([
                'status_code' => 200,
                'data' => $data,
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
            'id'              => 'required|exists:association_parents,id',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'spot_parent_id'  => 'nullable|exists:spot_parents,id',
            'president_phone' => 'nullable|string|max:255',
            'secretary_phone' => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|string|max:255',

            'data'                  => 'required|array|min:1',
            'data.*.id'             => 'required|exists:associations,id',
            'data.*.language_id'    => 'required|distinct|exists:languages,id',
            'data.*.name'           => 'required|string|max:255',
            'data.*.president_name' => 'nullable|string|max:255',
            'data.*.secretary_name' => 'nullable|string|max:255',
            'data.*.address'        => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $associationParent = AssociationParent::findOrFail($request->id);

            $associationParent->update([
                'spot_parent_id'  => $request->spot_parent_id,
                'president_phone' => $request->president_phone,
                'secretary_phone' => $request->secretary_phone,
                'email'           => $request->email,
                'website'         => $request->website,
            ]);

            if ($request->hasFile('logo')) {
                Fa::deleteImage($associationParent?->logo);
                $associationParent->update([
                    'logo' => Fa::uploadImage($request->file('logo'), 'associations'),
                ]);
            }

            foreach ($request->data as $data) {
                Association::where('id', $data['id'])
                    ->where('association_parent_id', $associationParent->id) // must belong to this parent
                    ->firstOrFail()
                    ->update([
                        'language_id'    => $data['language_id'],
                        'name'           => $data['name'],
                        'president_name' => $data['president_name'] ?? null,
                        'secretary_name' => $data['secretary_name'] ?? null,
                        'address'        => $data['address'] ?? null,
                    ]);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Association updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
