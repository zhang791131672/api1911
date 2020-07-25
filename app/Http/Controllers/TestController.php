<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\UserModel;
use App\Models\TokenModel;
use App\Models\GoodsModel;
class TestController extends Controller
{
    //file_get_contents(get)
    public function getAccessToken(){
        $appid='wx5efbe8932db24806';
        $appsecret='fe8604bcaaca5d3c5fdde138be496435';
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
        $res=file_get_contents($url);
        dd($res);
    }
    //curl(get)
    public function getAccessToken2(){
        $appid='wx5efbe8932db24806';
        $appsecret='fe8604bcaaca5d3c5fdde138be496435';
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
        //开启curl
        $ch=curl_init();
        //设置参数
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);      //不输出头信息
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  //以变量的形式输出数据
        //执行
        $response=curl_exec($ch);
        curl_close($ch);
        var_dump($response);
    }
    //guzzle(get)
    public function getAccessToken3(){
        $appid='wx5efbe8932db24806';
        $appsecret='fe8604bcaaca5d3c5fdde138be496435';
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
        $client=new Client();
        $response = $client->request('GET',$url);
        $response_data=$response->getBody(); //返回的是对象数据类型,echo是把它当作字符串来输出,其他打印方式会看不到数据
        echo $response_data;
    }

    public function userInfo(){
        echo 222;die;
       // echo str::random(32);
    }
    public function index(){
        $url='http://www.1911.com/user/info';
        $response=file_get_contents($url);
        var_dump($response);
    }

    /**
     * 登录
     * @param Request $request
     * @return array
     */
    public function login(Request $request){
        $user_name=$request->post('user_name');
        $this->checkEmpty($user_name,50000,'用户名不能为空',[]);
        $user_pass=$request->post('user_pass');
        $this->checkEmpty($user_pass,50001,'密码不能为空',[]);
        $res=UserModel::where('user_name',$user_name)->first();
        if($res){
            if(password_verify($user_pass,$res->user_pass)){
                $token_info=TokenModel::where('user_id',$res->user_id)->first();
                $expire=7200;
                $token=Str::random(32);
                if(empty($token_info)){
                    $token_model=new TokenModel();
                    $token_model->token=$token;
                    $token_model->expire=time()+$expire;
                    $token_model->user_id=$res->user_id;
                    if($token_model->save()){
                        $this->userCount($res->user_id);
                        return $this->response(0,'ok',['expire'=>$expire,'token'=>$token]);
                    }else{
                        return $this->response(50006,'记录token失败');
                    }
                }else{
                    $token_info->expire=time()+$expire;
                    $token_info->token=$token;
                    if($token_info->save()){
                        $this->userCount($res->user_id);
                        return $this->response(0,'ok',['expire'=>$expire,'token'=>$token]);
                    }else{
                        return $this->response(50006,'记录token失败');
                    }
                }
            }else{
                return $this->response(50005,'密码有误');
            }
        }else{
            return $this->response(50004,'没有该用户');
        }
    }

    /**
     * 注册
     * @param Request $request
     * @return array
     */
    public function reg(Request $request){
        $user_name=$request->post('user_name');
        $this->checkEmpty($user_name,50000,'用户名不能为空',[]);
        $user_pass=$request->post('user_pass');
        $this->checkEmpty($user_pass,50001,'密码不能为空',[]);
        $user_email=$request->post('user_email');
        $this->checkEmpty($user_email,50002,'邮箱不能为空',[]);
        $res=UserModel::where('user_name',$user_name)->first();
        if($res){
            return $this->response(50003,'用户名已存在');
        }
        $user_model=new UserModel();
        $user_model->user_name=$user_name;
        $user_model->user_pass=password_hash($user_pass,PASSWORD_BCRYPT);
        $user_model->user_email=$user_email;
        $user_model->reg_time=time();
        if($user_model->save()){
            return $this->response(0,'ok');
        }else{
            return $this->response(50004,'注册失败');
        }
    }
    /**
     * 检查参数是否为空
     */
    protected function checkEmpty($field,$errno,$msg,$data=[]){
        if(empty($field)){
            echo  json_encode([
                'errno'=>$errno,
                'msg'=>$msg,
                'data'=>$data
            ]);
            die;
        }
    }

    /**
     * 封装响应回去的json数据
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

    /**
     * 个人中心
     * @param Request $request
     * @return array
     */
    public function center(Request $request){
        $user_info=UserModel::where('user_id',$request->get('user_id'))->first();
        return $this->response(0,'ok',$user_info);
//        $token=$request->get('token');
//        $this->checkEmpty($token,50007,'token不能为空');
//        $key='s:black:'.$token;
//        $user_count='string:black_count'.$token;
//        if(Redis::sismember($key,$token)){
//            echo "您已被拉黑";
//        }else{
//            $count=Redis::incr($user_count,1);
//            if($count>=5){
//                Redis::sadd($key,$token);
//            }
//        }

//        $token=$request->get('token');
//        $this->checkEmpty($token,50007,'token不能为空');
//        $res=TokenModel::where('token',$token)->first();
//        if(empty($res)||$res->expire<time()){
//            return $this->response(50008,'token不正确或者已过期,请检查');
//        }else{
//            $user_info=UserModel::where('user_id',$res->user_id)->first();
//            return $this->response(0,'ok',$user_info);
//        }
    }

    public function hash1(){
        $data=[
            'age'=>11,
            'name'=>'zhangsan',
            'email'=>'791131672@qq.com'
        ];
        $key='1911_';
        //设置多个
        Redis::hmset($key,$data);
        Redis::expire($key,100);
    }
    public function hash2(){
        $key='1911_';
        //获取全部信息
        $data=Redis::hgetall($key);
        dd($data);
    }

    public function test1(){
//        $key='shop';
//        Redis::lPush($key,1,2,3,4,5,6,7,8,9,10);
//        $goods_info=[
//            'goods_id'=>123,
//            'goods_name'=>'鞋子',
//            'goods_price'=>110,
//            'goods_desc'=>'舒服'
//        ];
//        $key='goods123_';
//        $page_incr='goods_incr';
//        Redis::hmset($key,$goods_info);
//        Redis::expire($key,60);
//        $res=Redis::incr('goods_incr');
//        if($res>10){
//            Redis::sadd('token_set','123456');
//        }
    }
    public function test2(){
//        $key='shop';
//        $llen=Redis::llen($key);
//        if($llen>0){
//            Redis::lpop($key);
//        }else{
//            echo "empty";
//        }
    }
    public function goods(Request $request){
        $goods_id=$request->get('goods_id');
        $key='h:goods_info:'.$goods_id;
        if(empty(Redis::hgetall($key))){
            echo '数据库';
            $goods_info=GoodsModel::where('goods_id',$goods_id)->first();
            Redis::hmset($key,$goods_info->toArray());
        }else{
            echo "redis";
            $goods_info=Redis::hgetall($key);
        }
        Redis::hincrby($key,'user_count',1);
        dd($goods_info);
    }
    public function userCount($user_id){
        $key='h:view_count:'.$user_id;
        $url=$_SERVER['REQUEST_URI'];
        if(strpos($url,'?')){
            $url=substr($url,0,strpos($url,'?'));
        }
        Redis::hincrby($key,$url,1);
    }

    public function encrypt(Request $request){
        $encrypt_data=file_get_contents('php://input'); //body用原生的接
//        var_dump($data) ;die;
        $key='1911';
        $iv='1234567891234567';
//        $encrypt_data=$request->post('data');         //form用这种方式
        $decrypt_data=base64_decode($encrypt_data);
        $data=openssl_decrypt($decrypt_data,'AES-256-CBC',$key,OPENSSL_RAW_DATA,$iv);
        echo $data;
    }

    public function rsaEncrypt(){
        $data='API接口';
        $key=openssl_get_publickey(file_get_contents(storage_path().'/key/pub.key'));
        openssl_public_encrypt($data,$encrypt,$key);
        $encrypt=base64_encode($encrypt);
        $url='http://www.1911.com/rsaEncrypt';
        $client=new Client();
        $response=$client->request('POST',$url,[
            'body'=>$encrypt
        ]);
        //echo $response->getBody();die;
        $encrypt_data=json_decode($response->getBody(),true);
        if($encrypt_data['errno']==0){
            $key=openssl_get_privatekey(file_get_contents(storage_path().'/key/apiPriv.key'));
            openssl_private_decrypt(base64_decode($encrypt_data['data']),$decrypt_data,$key);
            echo $decrypt_data;
        }
    }
    public function sign(){
        $data='hello';
        $key='1911';
        $sign=md5($data.$key);
        $all_data['data']=$data;
        $all_data['sign']=$sign;
        $url='http://www.1911.com/test/sign?data='.$data."&sign=".$sign;
        $response=file_get_contents($url);
        echo $response;
    }

    public function rsaSign(Request $request){
        $data=$request->get('data');
        $sign=$request->get('sign');
        $data=base64_decode($data);
        $sign=base64_decode($sign);
        $res=openssl_verify($data,$sign,openssl_get_publickey(file_get_contents(storage_path().'/key/pub.key')));
        echo $res;
    }

    public function rsaPostSign(Request $request){
        $data=$request->post('data');
        $sign=$request->post('sign');
        $res=openssl_verify($data,$sign,openssl_get_publickey(file_get_contents(storage_path().'/key/pub.key')));
        echo $res;
    }
}

