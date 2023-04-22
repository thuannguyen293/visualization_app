<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Licenses extends Model
{
    protected $table = "licenses";

    protected $fillable = [
        'year', 'license_code', 'license_total', 'license_type', 'license_name', 'license_category', 'buyer', 'is_young'
    ];

    public $timestamps = false;

}
