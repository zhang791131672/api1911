<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckPri
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
        $token=$request->get('token');
        $uid=Redis::get($token);
        if(!$uid){
            $response=[
                'errno'=>50009,
                'msg'=>'鉴权失败,请重试'
            ];
            echo  json_encode($response,JSON_UNESCAPED_UNICODE);die;
        }
        return $next($request);
    }
}
