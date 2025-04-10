<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Info extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'data'=>'object'
        ];
    }

    protected function data(): Attribute
    {
        $data = json_decode($this->attributes['data'], true);
        $permanent_district = isset($data['permanent_district']) ? $data['permanent_district'] : '';
        $permanent_upzilla = isset($data['permanent_upzilla']) ? $data['permanent_upzilla'] : '';
        $present_district = isset($data['present_district']) ? $data['present_district'] : '';
        $present_upzilla = isset($data['present_upzilla']) ? $data['present_upzilla'] : '';
        $district = Cache::rememberForever('districts', function () {
            return District::all();
        });
        $upazila = Cache::rememberForever('upazilas', function () {
            return Upazila::all();
        });
        if ($permanent_district) {
            $permanent_district = $district->where('id', $permanent_district)->first();
            $data['permanent_dist_name_en'] = $permanent_district?->en;
            $data['permanent_dist_name_bn'] = $permanent_district?->bn;
        }
        if ($present_district) {
            $present_district = $district->where('id', $present_district)->first();
            $data['present_dist_name_en'] = $present_district?->en;
            $data['present_dist_name_bn'] = $present_district?->bn;
        }
        if ($permanent_upzilla) {
            $permanent_upzilla = $upazila->where('id', $permanent_upzilla)->first();
            $data['permanent_upzila_name_en'] = $permanent_upzilla?->en;
            $data['permanent_upzila_name_bn'] = $permanent_upzilla?->bn;
        }
        if ($present_upzilla) {
            $present_upzilla = $upazila->where('id', $present_upzilla)->first();
            $data['present_upzila_name_en'] = $present_upzilla?->en;
            $data['present_upzila_name_bn'] = $present_upzilla?->bn;
        }
        return Attribute::make(
            get: fn() => (object)$data,
        );
    }
}
