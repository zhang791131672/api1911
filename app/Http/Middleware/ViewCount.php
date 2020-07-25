<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class ViewCount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url=$_SERVER['REQUEST_URI'];
        if(strpos($url,'?')){
            $url=substr($url,0,strpos($url,'?'));
        }
        $key='z:view_count';
        Redis::zincrby($key,1,$url);
        return $next($request);
    }
}
