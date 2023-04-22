<?php

namespace App\Http\Middleware;

use Closure;

class UnderConstruction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        session_start();
        $pass = $request->get('pass');
//        if (!empty($pass) && $pass == "ksd9jf!d49Y") {
//            $_SESSION['pass'] = true;
//            setcookie("pass", 1, time() + (86400 * 30), "/");
//            return $next($request);
//        }
//
//        if (isset($_SESSION['pass']) || strpos($request->url(), "under_construction")) {
//            return $next($request);
//        }


        if (!empty($pass) && $pass == "ksd9jf!d49Y") {
//            $_SESSION['pass'] = true;
            setcookie("pass", 1, time() + (10800), "/");
            return $next($request);
        }

        if (isset($_COOKIE['pass']) || strpos($request->url(), "under_construction")) {
            return $next($request);
        }

        return redirect(route('under_construction'));

    }
}
