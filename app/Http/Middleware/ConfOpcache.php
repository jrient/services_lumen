<?php

namespace App\Http\Middleware;

use Closure;

class ConfOpcache
{
    //调试时ip下关闭opcache
    public function handle($request, Closure $next)
    {
        if (in_array($_SERVER['REMOTE_ADDR'], ['101.229.18.173'])) {
            ini_set('opcache.enable', 0);
            ini_set('display_errors', "On");
            error_reporting(E_ALL | E_STRICT);
        }
        return $next($request);
    }
}
