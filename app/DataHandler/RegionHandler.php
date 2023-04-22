<?php

namespace App\DataHandler;

use App\Models\Region;

class RegionHandler
{
    public function __construct()
    {
    }

    public static function GetList(){
        return Region::all();
    }
}