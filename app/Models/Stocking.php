<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stocking extends Model
{
    protected $table = "stocking";

    protected $fillable = [
        'year', 'waterbody_id', 'region_code', 'fishtype_code', 'fish_total'
    ];

}
