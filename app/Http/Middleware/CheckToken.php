<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\TokenModel;
use Illuminate\Support\Facades\Redis;
class CheckToken
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
        if(empty($token)){
            return response()->json($this->response(50001,'token不能为空'));
        }
        $res=TokenModel::where('token',$token)->first();
        if(empty($res)||$res->expire<time()){
            return response()->json($this->response(50002,'token不正确或者已过期,请检查'));
        }
        $request->attributes->add(['user_id'=>$res->user_id]);
        return $next($request);
    }

    /**
     * 封装返回去的响应数据
     * @param $errno
     * @param $msg
     * @param array $data
     * @return array
     */
    protected function response($errno,$msg,$data=[]){
        return [
            'errno'=>$errno,
            'msg'=>$msg,
            'data'=>$data
        ];
    }

}
