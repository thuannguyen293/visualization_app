<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\Catches;
use App\Models\WaterBodys;
use App\Models\Region;
use App\Models\Stocking;
use App\Models\FishType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB, Validator;

class StockingController extends Controller
{

    function GetChartTimeSeriesData()
    {
        $validator = Validator::make(request()->all(),
            [
                'geographic_id' => 'alpha_num',
                'waterbody_type' => 'alpha_num',
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

//        $fishtypes = ['BF_SF', 'RB', 'NC', 'SS'];
//        $stocking = Stocking::whereBetween('year', $time_range)->where('fish_total', '>', 0)->whereIn('fishtype_code', $fishtypes);
        $stocking = Stocking::whereBetween('year', $time_range)->where('fish_total', '>', 0);
        if (request()->get('geographic_id') && request()->get('geographic_id') != 'all') {
            $geographic_id = request()->get('geographic_id');
            $stocking = $stocking->where('region_code', $geographic_id);
        }
        if (request()->get('waterbody_type') && request()->get('waterbody_type') != 'all') {
            $type = request()->get('waterbody_type');
            if ($type !== 'AS') {
                $stocking = $stocking->where('waterbody_id', '<', 1000);
            }else{
                $stocking = $stocking->where('waterbody_id', '>', 999);
            }
        }
        $stocking = $stocking->select(
            'fishtype_code', 'year',
            DB::raw('COALESCE(SUM(fish_total), 0) as fish_total')
        )->groupBy('fishtype_code', 'year')->get()->toArray();
        $_stocking_data = collect($stocking)->groupBy('year')->toArray();
        $stocking_data = [];
        foreach ($_stocking_data as $key => $element) {
            $_el['year'] = $key;
            foreach ($element as $k => $item) {
                $_el[$item['fishtype_code']] = $item['fish_total'];
            }
            $stocking_data[$key] = $_el;
            $_el = [];
        }
        $fishtypes_data = collect($stocking)->groupBy('fishtype_code')->keys()->toArray();
        $fishtypes_data = FishType::whereIn('code', $fishtypes_data)->select('code', 'name')->orderBy('id','DESC')->get()->toArray();
        $response['data']['stocking'] = $stocking_data;
        $response['data']['fishtypes'] = $fishtypes_data;
        return response()->json($response);
    }

    function GetChartRegionalComparisonData()
    {
        $validator = Validator::make(request()->all(),
            [
                'waterbody_type' => 'alpha_num',
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

        $year = Carbon::now()->year - 1;
        if (request()->get('year'))
            $year = (int)request()->get('year');
        Util::CacheTimeFilter($year, false);

        $col_name = 'name_' . strtoupper(app()->getLocale());
        $stocking = Stocking::where('year', $year)->join('regions', 'stocking.region_code', '=', 'regions.region_code')->select('stocking.region_code', 'regions.' . $col_name);
        if (request()->get('waterbody_type') && request()->get('waterbody_type') != 'all') {
            $type = request()->get('waterbody_type');
            $waterbody_id = WaterBodys::where('type', $type)->pluck('code_id')->toArray();
            $stocking = $stocking->whereIn('waterbody_id', $waterbody_id);
        }
        $stocking_lake = clone $stocking;
        $stocking_stream = clone $stocking;
        $stocking_lake = $stocking_lake->where('waterbody_id','>',999)
            //->whereIn('fishtype_code', ['BF_SF', 'RB', 'NC', 'AA'])
            ->select(
            'stocking.region_code',
            DB::raw('regions.' . $col_name . ' as region_name'),
            DB::raw('COALESCE(CAST(SUM(fish_total) as UNSIGNED INTEGER), 0) as total')
        )->groupBy('stocking.region_code', 'regions.' . $col_name)->get()->toArray();
        $stocking_stream = $stocking_stream->where('waterbody_id','<',1000)
            ->where('fishtype_code', 'BF_SF')->select(
            'stocking.region_code',
            DB::raw('regions.' . $col_name . ' as region_name'),
            DB::raw('COALESCE(CAST(SUM(fish_total) as UNSIGNED INTEGER), 0) as total')
        )->groupBy('stocking.region_code', 'regions.' . $col_name)->get()->toArray();
        $response['data']['stocking']['lake'] = $stocking_lake;
        $response['data']['stocking']['stream'] = $stocking_stream;
        return response()->json($response);
    }

}