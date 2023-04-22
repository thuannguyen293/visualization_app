<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request, $locale)
    {
        $url_previous = url()->previous();
        $arr_languages = array_merge([config('app.fallback_locale')], config('app.extra_languages', []));
        if (in_array($locale, $arr_languages)) {
            $short_url_previous = str_replace(url('/'), '', $url_previous);
            foreach($arr_languages as $v) {
                $short_url_previous = str_replace('/'.$v, '', $short_url_previous);
            }
            if ($locale != config('app.fallback_locale')) {
                return redirect()->to($locale . $short_url_previous);
            } else {
                return redirect()->to($short_url_previous);
            }
        }

        return redirect($url_previous);
    }
}