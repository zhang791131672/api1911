<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
class UserCount
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
        $user_id=$request->get('user_id');
        $key='h:view_count:'.$user_id;
        $url=$_SERVER['REQUEST_URI'];
        if(strpos($url,'?')){
            $url=substr($url,0,strpos($url,'?'));
        }
        Redis::hincrby($key,$url,1);
        $request->attributes->add(['user_id'=>$user_id]);
        return $next($request);
    }
}
