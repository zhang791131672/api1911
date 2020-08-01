<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommonController extends Controller
{
    //
    public function checkParamEmpty(Request $request,$data){
        foreach($data as $k=>$v){
            if(empty($v)){
                $this->fail(50001,$k.'参数不能为空');
            }
        }
        return true;
    }

    /**
     * 失败
     * @param int $errno
     * @param string $msg
     * @param array $data
     */
    public function fail($errno=1,$msg='fail',$data=[]){
        echo json_encode($this->responseArr($errno,$msg,$data),JSON_UNESCAPED_UNICODE);die;
    }

    /**
     * 成功
     * @param int $errno
     * @param string $msg
     * @param array $data
     */
    protected function success($errno=200,$msg='success',$data=[]){
        echo json_encode($this->responseArr($errno,$msg,$data));die;
    }

    /**
     *  拼接响应数组
     */
    protected function responseArr($errno,$msg,$data){
        return $arr=[
            'errno'=>$errno,
            'msg'=>$msg,
            'data'=>$data
        ];
    }
}
