<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\Licenses;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB, Validator;

class LicensesController extends Controller
{

    function GetChartData()
    {
        $validator = Validator::make(request()->all(),
            [
                'buyer_type' => 'alpha_num',
            ]
        );

        $response = array(
            'code' => 0,
            'message' => '',
        );

        if ($validator->fails()) {
            $response['code'] = 1;
            $response['message'] = 'Error';
            return response()->json($response);
        }
        $time_range = [2002, Carbon::now()->year - 1];
        if (request()->get('time_range'))
            $time_range = array_map('intval', request()->get('time_range'));
        Util::CacheTimeFilter($time_range);

        $license_types = Licenses::whereBetween('year', $time_range)->select('license_type');
        $licenses = Licenses::whereBetween('year', $time_range);
        $last_year = isset($time_range[1]) ? $time_range[1] : Carbon::now()->year;
        $average = Licenses::whereBetween('year', [$last_year - 9, $last_year]);
        if (request()->get('buyer_type') && request()->get('buyer_type') != 'all') {
            $buyer_type = request()->get('buyer_type');
            $license_types = $license_types->whereBuyer($buyer_type);
            $licenses = $licenses->whereBuyer($buyer_type);
            $average = $average->whereBuyer($buyer_type);
        }
        $license_types = ['Jahrespatent', 'Tagespatent'];
        $data = [];
        $data_average = [];
        foreach ($license_types as $key => $type) {
            $licenses_type = clone $licenses;
            $licenses_data = $licenses_type->where('license_type', $type)->select(
                    DB::raw("'".$type."' as license_type"),
                    'year',
                    DB::raw('CAST(SUM(license_total) as UNSIGNED INTEGER) as licenses')
                )->groupBy('year')->havingRaw('SUM(license_total) > ?',[0])->get();
            $average_type = clone $average;
            $average_type = $average_type->where('license_type', $type)->select(
                DB::raw("'".$type."' as license_type"),
                DB::raw('COALESCE(ROUND(SUM(license_total)/10,1), 0) as average')
            )->get()->toArray();

            if ($licenses_type->count()) {
                $data = array_merge($data, $licenses_data->toArray());
            }
            $data_average = array_merge($data_average, $average_type);
        }
        $licenses_young = clone $licenses;
        $licenses_young = $licenses_young->where('is_young', 1)->whereIn('license_type', ['Jahrespatent', 'Tagespatent'])->select(
                DB::raw("'Jugendpatent' as license_type"),
                'year',
                DB::raw('CAST(SUM(license_total) as UNSIGNED INTEGER) as licenses')
            )->groupBy('year')->havingRaw('SUM(license_total) > ?',[0])->get()->toArray();
        $data = array_merge($data, $licenses_young);

        $license_type_not_in = ['Jahrespatent', 'Tagespatent'];
        $licenses_other = clone $licenses;
        $licenses_other = $licenses_other->whereNotIn('license_type', $license_type_not_in)->select(
                DB::raw("'other' as license_type"),
                'year',
                DB::raw('CAST(SUM(license_total) as UNSIGNED INTEGER) as licenses')
            )->groupBy('year')->havingRaw('SUM(license_total) > ?',[0])->get()->toArray();
        $data = array_merge($data, $licenses_other);
        $data = collect($data);
        $data = $data->groupBy('year')->toArray();
        $average_other = clone $average;
        $average_other = $average_other->whereNotIn('license_type', $license_type_not_in)->select(
            DB::raw("'other' as license_type"),
            DB::raw('COALESCE(ROUND(SUM(license_total)/10,1), 0) as average')
        )->get()->toArray();
        $average_young = clone $average;
        $average_young = $average_young->where('is_young', 1)->select(
            DB::raw("'Jugendpatent' as license_type"),
            DB::raw('COALESCE(ROUND(SUM(license_total)/10,1), 0) as average')
        )->get()->toArray();
        $data_average = array_merge($data_average, $average_other, $average_young);
        $license_types = array_merge($license_types, ['other', 'Jugendpatent']);
        $response['data']['licenses'] = $data;
        $response['data']['license_types'] = $license_types;
        $response['data']['average'] = $data_average;
        return response()->json($response);
    }


}