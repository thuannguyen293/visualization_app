<?php

namespace App\Http\Controllers\App;

use App\DataHandler\FishTypeHandler;
use App\DataHandler\RegionHandler;
use App\DataHandler\WaterBodyTypeHandler;
use App\Models\FishType;
use App\Models\WaterBodyType;
use App\Models\WaterBodys;
use Session;

class SinglePageController extends AppController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function home()
    {
        return redirect(route('main_app'));
    }

    public function main_app()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = json_encode($time_range);

        // Load time_year
        $year = date('Y') - 1;
        if (Session::has('year')) {
            $year = Session::get('year');
        }
        $views["year"] = $year;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();

        return view('single_app.app', $views);
    }

    public function app_jquery()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = json_encode($time_range);

        // Load time_year
        $year = date('Y') - 1;
        if (Session::has('year')) {
            $year = Session::get('year');
        }
        $views["year"] = $year;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();

        return view('single_app.app_jquery', $views);
    }

    public function statistics()
    {
        return redirect(route('catches_time_series'));
    }

    public function CatchesTimeSeries()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();

        return view($this->view . '.catches.time_series', $views);
    }

    public function CatchesSeason()
    {
        // Load time range
        $year = date('Y') - 1;
        if (Session::has('year')) {
            $year = Session::get('year');
        }
        $views["year"] = $year;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.catches.season', $views);
    }

    public function CatchesRegionalComparison()
    {
        // Load time range
        $year = date('Y') - 1;
        if (Session::has('year')) {
            $year = Session::get('year');
        }
        $views["year"] = $year;

        $fish_types = FishType::where("searchable", 1)->get();
        $water_body_types = WaterBodyTypeHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.catches.regional_comparison', $views);
    }

    public function CatchesFishtypeComparison()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.catches.fishtype_comparison', $views);
    }

    public function StockingTimeSeries()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.stocking.time_series', $views);
    }

    public function StockingRegionalComparison()
    {
        // Load time range
        $year = date('Y') - 2;
        if (Session::has('year')) {
            $year = Session::get('year');
        }
        $views["year"] = $year;

        $fish_types = FishType::where("searchable", 1)->get();
        $water_body_types = WaterBodyType::all();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.stocking.regional_comparison', $views);
    }

    public function Licenses()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;
        return view($this->view . '.licenses.index', $views);
    }

    public function DemoCatchesTimeSeries(){
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();

        return view($this->view . '.catches.demo_time_series', $views);
    }

    public function DemoCatchesSeason()
    {
        // Load time range
        $year = date('Y') - 1;
        if (Session::has('year')) {
            $year = Session::get('year');
        }
        $views["year"] = $year;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.catches.demo_season', $views);
    }

    public function DemoCatchesFishtypeComparison()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.catches.demo_fishtype_comparison', $views);
    }

    public function DemoStockingTimeSeries()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;

        $fish_types = FishTypeHandler::GetSearchableList();
        $water_body_types = WaterBodyTypeHandler::GetList();
        $regions = RegionHandler::GetList();

        $views["fish_types"] = $fish_types;
        $views["water_body_types"] = $water_body_types;
        $views["regions"] = $regions;
        $views["waterbody"] = $this->GetWaterbodyData();
        return view($this->view . '.stocking.demo_time_series', $views);
    }

    public function DemoLicenses()
    {
        // Load time range
        $time_range = [2002, date('Y') - 1];
        if (Session::has('time_range')) {
            $time_range = Session::get('time_range');
        }
        $views["time_range"] = $time_range;
        return view($this->view . '.licenses.demo_index', $views);
    }

    public function GetWaterbodyData()
    {

        $waterbodytypes = WaterBodyType::where('active_modal',1)->get();
        $waterbody = [];

        $all_waterbody = WaterBodys::select('code_id', 'region_code', 'name', 'type')->get()->toArray();

        $format_all_waterbody = [];

        foreach ($waterbodytypes as $key => $type) {
            $format_all_waterbody[$type['code']] = [];
        }

        foreach ($all_waterbody as $v) {
            if ($v['type'] == "TSSH") {
                $format_all_waterbody["TS"][] = $v;
                $format_all_waterbody["SH"][] = $v;
            }else{
                $format_all_waterbody[$v['type']][] = $v;
            }
        }

        foreach ($waterbodytypes as $key => $type) {
            $item['name'] = $type['name'];
            $item['code'] = $type['code'];
            $item['waterbody'] = $format_all_waterbody[$type['code']];
            $waterbody[] = $item;
        }
        return [
            "all_waterbody" =>$all_waterbody,
            "group_waterbody" => $waterbody
        ];
    }

}
