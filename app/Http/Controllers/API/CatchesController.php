<?php

namespace App\Http\Controllers\Api;

use App\Exports\DataExport;
use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\Catches;
use App\Models\WaterBodys;
use App\Models\Region;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB, Validator, Session;
use Maatwebsite\Excel\Facades\Excel;

class CatchesController extends Controller
{
    public function __construct()
    {
    }

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

        $catches = Catches::whereBetween(DB::raw('YEAR(fishing_session_date)'), $time_range)->select(
            DB::raw('SUM(fish_total) as catches'),
            DB::raw('YEAR(fishing_session_date) as year'),
            DB::raw('SUM(session_total) as sessions'),
            DB::raw('ROUND(SUM(fish_total)/SUM(session_total),1) as CPUE')
        )->groupBy('year');

        $last_year = isset($time_range[1]) ? $time_range[1] : Carbon::now()->year;
        $average = Catches::whereBetween(DB::raw('YEAR(fishing_session_date)'), [$last_year - 9, $last_year])->select(
            DB::raw('COALESCE(SUM(fish_total)/10, 0) as catches'),
            DB::raw('COALESCE(SUM(session_total)/10, 0) as sessions'),
            DB::raw('COALESCE(ROUND(SUM(fish_total)/SUM(session_total),1), 0) as CPUE')
        );
        $waterbody_id_geo = [];
        if (request()->get('geographic_id') && request()->get('geographic_id') != 'all') {
            $geographic_id = request()->get('geographic_id');
            $waterbody_id_geo = WaterBodys::whereRegionCode($geographic_id)->pluck('code_id')->toArray();
        }
        $waterbody_id_type = [];
        if (request()->get('waterbody_type') && request()->get('waterbody_type') != 'all') {
            $waterbody_type = request()->get('waterbody_type');
            if ($waterbody_type !== 'AS') {
                $waterbody_id_type = WaterBodys::whereType($waterbody_type);
                if ($waterbody_type == 'TS' || $waterbody_type == 'SH') {
                    $waterbody_id_type = $waterbody_id_type->orWhere('type', 'TSSH');
                }
                $waterbody_id_type = $waterbody_id_type->pluck('code_id')->toArray();
            } else {
                $catches = $catches->where('waterbody_id', '>', 999);
                $average = $average->where('waterbody_id', '>', 999);
            }
        }
        if (!empty($waterbody_id_geo) || !empty($waterbody_id_type)) {
            $waterbody_id = (!empty($waterbody_id_geo) && !empty($waterbody_id_type)) ? array_unique(array_intersect($waterbody_id_geo, $waterbody_id_type), SORT_REGULAR) : ((!empty($waterbody_id_geo)) ? $waterbody_id_geo : $waterbody_id_type);
            $catches = $catches->whereIn('waterbody_id', $waterbody_id);
            $average = $average->whereIn('waterbody_id', $waterbody_id);
        }

        $catches = $catches->get()->toArray();
        $average = $average->first();
        $response['data']['catches'] = $catches;
        $response['data']['average'] = $average;
        return response()->json($response);
    }

    function GetChartSeasonData()
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

        $year = Carbon::now()->year - 1;
        $month_range = [5, 9];
        if (request()->get('year'))
            $year = (int)request()->get('year');
        Util::CacheTimeFilter($year, false);

        $catches = Catches::whereYear('fishing_session_date', $year)->whereBetween(DB::raw('MONTH(fishing_session_date)'), $month_range);
        $average = Catches::whereBetween(DB::raw('YEAR(fishing_session_date)'), [$year - 9, $year])->whereBetween(DB::raw('MONTH(fishing_session_date)'), $month_range);

        $waterbody_id_geo = [];
        if (request()->get('geographic_id') && request()->get('geographic_id') != 'all') {
            $geographic_id = request()->get('geographic_id');
            $waterbody_id_geo = WaterBodys::whereRegionCode($geographic_id)->pluck('code_id')->toArray();
        }
        $waterbody_id_type = [];
        if (request()->get('waterbody_type') && request()->get('waterbody_type') != 'all') {
            $waterbody_type = request()->get('waterbody_type');
            if ($waterbody_type !== 'AS') {
                $waterbody_id_type = WaterBodys::whereType($waterbody_type);
                if ($waterbody_type == 'TS' || $waterbody_type == 'SH') {
                    $waterbody_id_type = $waterbody_id_type->orWhere('type', 'TSSH');
                }
                $waterbody_id_type = $waterbody_id_type->pluck('code_id')->toArray();
            } else {
                $catches = $catches->where('waterbody_id', '>', 999);
                $average = $average->where('waterbody_id', '>', 999);
            }
        }
        if (!empty($waterbody_id_geo) || !empty($waterbody_id_type)) {
            $waterbody_id = (!empty($waterbody_id_geo) && !empty($waterbody_id_type)) ? array_unique(array_intersect($waterbody_id_geo, $waterbody_id_type), SORT_REGULAR) : ((!empty($waterbody_id_geo)) ? $waterbody_id_geo : $waterbody_id_type);
            $catches = $catches->whereIn('waterbody_id', $waterbody_id);
            $average = $average->whereIn('waterbody_id', $waterbody_id);
        }

        $catches = $catches->select(
            DB::raw('COALESCE(MONTH(fishing_session_date), 0) as m'),
            DB::raw('COALESCE(CAST(SUM(fish_total) as UNSIGNED INTEGER), 0) as catches'),
            DB::raw('COALESCE(MONTHNAME(fishing_session_date), 0) as month'),
            DB::raw('COALESCE(CAST(SUM(session_total) as UNSIGNED INTEGER), 0) as sessions'),
            DB::raw('COALESCE(ROUND(SUM(fish_total)/SUM(session_total),1), 0) as CPUE')
        )->orderBy(DB::raw('month(fishing_session_date)'))->groupBy('month', 'm')->get()->toArray();
        $average = $average->select(
            DB::raw('COALESCE(MONTH(fishing_session_date), 0) as m'),
            DB::raw('COALESCE(SUM(fish_total)/10, 0) as avg_catches'),
            DB::raw('COALESCE(MONTHNAME(fishing_session_date), 0) as month'),
            DB::raw('COALESCE(SUM(session_total)/10, 0) as avg_sessions'),
            DB::raw('COALESCE(ROUND(SUM(fish_total)/SUM(session_total),1), 0) as avg_CPUE')
        )->orderBy(DB::raw('month(fishing_session_date)'))->groupBy('month', 'm')->get()->toArray();
        $data = array();
        if (!$catches && !$average) {
            $response['data'] = $data;
            return response()->json($response);
        }
        for ($i = 5; $i < 10; $i++) {
            $key = array_search($i, array_column($catches, 'm'));
            $key_avg = array_search($i, array_column($average, 'm'));
            $monthname = date('F', mktime(0, 0, 0, $i, 10));
            $catches_item = ($key !== false) ? $catches[$key] : ['m' => $i, 'catches' => 0, 'sessions' => 0, 'CPUE' => 0, 'month' => $monthname];
            $average_item = ($key_avg !== false) ? $average[$key_avg] : ['m' => $i, 'avg_catches' => 0, 'avg_sessions' => 0, 'avg_CPUE' => 0, 'month' => $monthname];
            $data[] = array_merge($catches_item, $average_item);
        }
        foreach ($data as $id => $v) {
            $data[$id]["month"] = __('app.month_' . $v['m']);
        }
        $response['data'] = $data;
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
        $zones = Region::select('region_code', $col_name)->get()->toArray();
        $catches = [];
        $average_data = [];
        foreach ($zones as $zone) {
            $waterbody_id = WaterBodys::where('region_code', $zone['region_code']);
            $total = Catches::whereYear('fishing_session_date', $year);
            $average = Catches::whereBetween(DB::raw('YEAR(fishing_session_date)'), [$year - 9, $year]);

            if (request()->get('waterbody_type') && request()->get('waterbody_type') != 'all') {
                $waterbody_type = request()->get('waterbody_type');
                if ($waterbody_type !== 'AS') {
                    if ($waterbody_type == 'TS' || $waterbody_type == 'SH') {
                        $waterbody_id->where(function($query) use ($waterbody_type) {
                            $query->whereType($waterbody_type)
                                ->orWhere('type', 'TSSH');
                        });
                    } else {
                        $waterbody_id = $waterbody_id->whereType($waterbody_type);
                    }
                } else {
                    $waterbody_id->where('code_id', '>', 999);
                }
            }

            $waterbody_id = $waterbody_id->pluck('code_id');

            $total = $total->whereIn('waterbody_id', $waterbody_id)->select(
                DB::raw('COALESCE(CAST(SUM(fish_total) as UNSIGNED INTEGER), 0) as catches'),
                DB::raw('COALESCE(CAST(SUM(session_total) as UNSIGNED INTEGER), 0) as sessions'),
                DB::raw('COALESCE(ROUND(SUM(fish_total)/SUM(session_total),1), 0) as CPUE')
            )->first()->toArray();
            $average = $average->whereIn('waterbody_id', $waterbody_id)->select(
                DB::raw('COALESCE(SUM(fish_total)/10, 0) as avg_catches'),
                DB::raw('COALESCE(SUM(session_total)/10, 0) as avg_sessions'),
                DB::raw('COALESCE(ROUND(SUM(fish_total)/SUM(session_total),1), 0) as avg_CPUE')
            )->first()->toArray();
            if ($total['catches'] > 0) {
                $total['region_name'] = $zone[$col_name];
                $catches[$zone['region_code']] = array_merge($total, $average);
            }
        }
        $response['data'] = $catches;
        return response()->json($response);
    }

    function GetChartFishtypeComparisonData()
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

        $fishtypes = Catches::where('fish_total', '>', 0)->where('fishtype_code', '<>', '0')->whereBetween(DB::raw('YEAR(fishing_session_date)'), $time_range)->join('fishtypes', 'catches.fishtype_code', '=', 'fishtypes.code')->select('fishtype_code', 'fishtypes.name');
        $catches = Catches::where('fishtype_code', '!=', '')->where('fishtype_code', '<>', '0')->whereBetween(DB::raw('YEAR(fishing_session_date)'), $time_range);
        $last_year = isset($time_range[1]) ? $time_range[1] : Carbon::now()->year;
        $average = Catches::where('fishtype_code', '!=', '')->where('fishtype_code', '<>', '0')->whereBetween(DB::raw('YEAR(fishing_session_date)'), [$last_year - 9, $last_year]);
        //Filter
        $waterbody_id_geo = [];
        if (request()->get('geographic_id') && request()->get('geographic_id') != 'all') {
            $geographic_id = request()->get('geographic_id');
            $waterbody_id_geo = WaterBodys::whereRegionCode($geographic_id)->pluck('code_id')->toArray();
        }
        $waterbody_id_type = [];
        if (request()->get('waterbody_type') && request()->get('waterbody_type') != 'all') {
            $waterbody_type = request()->get('waterbody_type');
            if ($waterbody_type !== 'AS') {
                $waterbody_id_type = WaterBodys::whereType($waterbody_type);
                if ($waterbody_type == 'TS' || $waterbody_type == 'SH') {
                    $waterbody_id_type = $waterbody_id_type->orWhere('type', 'TSSH');
                }
                $waterbody_id_type = $waterbody_id_type->pluck('code_id')->toArray();
            } else {
                $fishtypes = $fishtypes->where('waterbody_id', '>', 999);
                $catches = $catches->where('waterbody_id', '>', 999);
                $average = $average->where('waterbody_id', '>', 999);
            }
        }
        if (!empty($waterbody_id_geo) || !empty($waterbody_id_type)) {
            $waterbody_id = (!empty($waterbody_id_geo) && !empty($waterbody_id_type)) ? array_unique(array_intersect($waterbody_id_geo, $waterbody_id_type), SORT_REGULAR) : ((!empty($waterbody_id_geo)) ? $waterbody_id_geo : $waterbody_id_type);
            $fishtypes = $fishtypes->whereIn('waterbody_id', $waterbody_id);
            $catches = $catches->whereIn('waterbody_id', $waterbody_id);
            $average = $average->whereIn('waterbody_id', $waterbody_id);
        }
        if (isset($waterbody_type) && $waterbody_type == 'FG') {
            $fishtype_code_other = ['AE', 'RB', 'BS'];
            $fishtype_code = array_merge(['BF', 'SF'], $fishtype_code_other);
        } else {
            $fishtype_code_other = ['SS', 'AE', 'RB', 'NC', 'BS'];
            $fishtype_code = array_merge(['BF', 'SF'], $fishtype_code_other);
        }
        $fishtypes = $fishtypes->whereIn('fishtype_code', $fishtype_code_other)->distinct()->get()->toArray();

        $catches_BFSF = clone $catches;
        $catches_BFSF = $catches_BFSF->whereIn('fishtype_code', ['BF', 'SF']);
        $catches_BFSF = $catches_BFSF->select(
            DB::raw('"BSF" as fishtype_code'),
            DB::raw('YEAR(fishing_session_date) as year'),
            DB::raw('CAST(SUM(fish_total) as UNSIGNED INTEGER) as catches')
        )->groupBy('year')->havingRaw('SUM(fish_total) > ?', [0])->get()->toArray();

        $catches_by_type = clone $catches;
        $catches_by_type = $catches_by_type->whereIn('fishtype_code', $fishtype_code_other);
        $catches_by_type = $catches_by_type->select(
            'fishtype_code',
            DB::raw('YEAR(fishing_session_date) as year'),
            DB::raw('CAST(SUM(fish_total) as UNSIGNED INTEGER) as catches')
        )->groupBy('fishtype_code', 'year')->havingRaw('SUM(fish_total) > ?', [0])->get()->toArray();

        $catches = $catches->whereNotIn('fishtype_code', $fishtype_code);
        $catches = $catches->select(
            DB::raw('"other" as fishtype_code'),
            DB::raw('YEAR(fishing_session_date) as year'),
            DB::raw('CAST(SUM(fish_total) as UNSIGNED INTEGER) as catches')
        )->groupBy('year')->havingRaw('SUM(fish_total) > ?', [0])->get()->toArray();

        $catches = array_merge($catches_BFSF, $catches_by_type, $catches);
        $catches = collect($catches);
        $_catches = $catches->groupBy('year')->toArray();
        $catches = [];
        foreach ($_catches as $key => $element) {
            $_el['year'] = $key;
            foreach ($element as $k => $item) {
                $_el[$item['fishtype_code']] = $item['catches'];
            }
            $catches[$key] = $_el;
        }

        $average_BFSF = clone $average;
        $average_BFSF = $average_BFSF->whereIn('fishtype_code', ['BF', 'SF'])->select(
            DB::raw('"BSF" as fishtype_code'),
            DB::raw('COALESCE(ROUND(SUM(fish_total)/10,1), 0) as CPUE')
        )->havingRaw('ROUND(SUM(fish_total)/10,1) > ?', [0])->get()->toArray();
        $average_by_type = clone $average;
        $average_by_type = $average_by_type->whereIn('fishtype_code', $fishtype_code_other)->select(
            'fishtype_code',
            DB::raw('COALESCE(ROUND(SUM(fish_total)/10,1), 0) as CPUE')
        )->groupBy('fishtype_code')->havingRaw('ROUND(SUM(fish_total)/10,1) > ?', [0])->get()->toArray();
        $average = $average->whereNotIn('fishtype_code', $fishtype_code)->select(
            DB::raw('"other" as fishtype_code'),
            DB::raw('COALESCE(ROUND(SUM(fish_total)/10,1), 0) as CPUE')
        )->havingRaw('ROUND(SUM(fish_total)/10,1) > ?', [0])->get()->toArray();
        $_average = array_merge($average_BFSF, $average_by_type, $average);
        $average = [];
        $average['year'] = 'average';
        foreach ($_average as $key => $item) {
            $average[$item['fishtype_code']] = $item['CPUE'];
        }
        $catches['average'] = $average;
        $fishtypes_data = array_merge(
            [
                ['fishtype_code' => 'BSF', 'name' => 'Bach-/Seeforelle'],
            ],
            $fishtypes,
            [
                ['fishtype_code' => 'other', 'name' => 'Andere Arten']
            ]
        );
        $response['data']['catches'] = $catches;
        $response['data']['fishtypes'] = (!empty($fishtypes))?$fishtypes_data:[];
        $response['data']['average'] = $average;
        return response()->json($response);
    }

    function DownloadImage()
    {
        $image_uri = request()->getContent();
        $image_uri = urldecode($image_uri);
        $image_uri = str_replace("image=", "", $image_uri);

        $dataImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image_uri));
        $path = public_path('upload/image/');
        $new_name = time() . ".png";
        file_put_contents($path . '/' . $new_name, $dataImage);

        return response()->download($path . '/' . $new_name);

    }

    function DownloadExcel()
    {
        $data = request()->getContent();
        parse_str($data, $request_data);

        ob_end_clean();
        ob_start();

        return Excel::download(new DataExport($request_data), 'data.xlsx');
    }

}