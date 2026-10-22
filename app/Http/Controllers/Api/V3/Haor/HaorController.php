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
}
