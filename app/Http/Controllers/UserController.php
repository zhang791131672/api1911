<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Models\TokenModel;
use Symfony\Component\CssSelector\Parser\Token;

class UserController extends CommonController
{
    //
    public function login(Request $request){
        $data=$request->get('data');
        $this->checkParamEmpty($request,$data);
        $user_obj=UserModel::where('user_name',$data['user_name'])->first();
        if($user_obj){
            if(md5($data['user_pass'].$user_obj->rand_code)==$user_obj->user_pass){
                $user_info=$user_obj->toArray();
                $user_info['token']=$this->createUserToken($user_obj->user_id);
                $user_obj->error_count=null;
                $user_obj->last_error_time=null;
                $user_obj->save();
                $this->success(200,'success',$user_info);
            }else{
                $this->fail(50003,'账号与密码不匹配');
            }
        }else{
            $this->fail(50002,'该用户不存在');
        }
    }
    /**
     * 生成用户令牌
     */
    public function createUserToken($user_id){
        $where=[
            ['user_id','=',$user_id],
            ['ctime','>',time()-60]
        ];
        if(TokenModel::where($where)->count()>=3){
            $this->fail(50004,'一分钟最多登录三次,请稍后再试');
        }
        $token=time().rand(111111,999999);
        $token_model=new TokenModel();
        $token_model->where('user_id',$user_id)->update(['status'=>2]);
        $token_model->user_id=$user_id;
        $token_model->token=$token;
        $token_model->expire=time()+7200;
        $token_model->status=1;
        $token_model->ctime=time();
        if($token_model->save()){
            return $token;
        }else{
            return false;
        }
    }


    public function register(Request $request){
        $data=$request->get('data');
        $this->checkParamEmpty($request,$data);
        $count=UserModel::where('user_name',$data['user_name'])->count();
        if($count>0){
            $this->fail(50005,'该用户名已存在');
        }
        $user_model=new UserModel();
        $rand_code=rand(111,999);
        $user_model->user_name=$data['user_name'];
        $user_model->user_pass=md5($data['user_pass'].$rand_code);
        $user_model->rand_code=$rand_code;
        $user_model->user_email=$data['user_email'];
        if($user_model->save()){
            $this->success(200,'success');
        }else{
            $this->fail(50006,'注册失败');
        }
    }
}
