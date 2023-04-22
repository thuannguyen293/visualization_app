<?php

namespace App\DataHandler;

use App\Models\WaterBodyType;

class WaterBodyTypeHandler
{
    public function __construct()
    {
    }

    public static function GetList(){
        return WaterBodyType::orderBy('sort', 'desc')->get();
    }
}