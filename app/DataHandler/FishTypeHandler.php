<?php

namespace App\DataHandler;

use App\Models\FishType;

class FishTypeHandler
{
    public function __construct()
    {
    }

    public static function GetSearchableList(){
        return FishType::where("searchable",1)->get();
    }
}