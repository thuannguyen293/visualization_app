<?php
namespace App\Http\Middleware;

use Closure;

class Localize
{

    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1, ''); // `en` or `fr`
//var_dump(config('app.locale'));die;
        $arr_languages = array_merge([config('app.locale')], config('app.extra_languages', []));
        if (in_array($locale, $arr_languages)) {
            app()->setLocale($locale);

        } else {
            app()->setLocale(config('app.locale'));
        }

        return $next($request);
    }
}