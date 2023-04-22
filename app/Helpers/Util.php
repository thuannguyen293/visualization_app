<?php

namespace App\Helpers;

use Session;

class Util {
    public static function getLocale(){
        $locale = app()->getLocale();
        return ($locale == config('app.fallback_locale')) ? '' : $locale;
    }

    public static function CacheTimeFilter($years, $is_range = true)
    {
        if ($is_range) {
            Session::put('time_range', $years);
        } else {
            Session::put('year', $years);
        }
    }
}