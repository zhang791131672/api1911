<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Redis;
use Closure;
use App\Http\Controllers\CommonController;
class CheckApi
{
    //指定时间内访问次数和 $number_limit配合使用
    public $time_limit=60;
    //指定时间内访问次数和$time_limit配合使用
    public $number_limit=10;
    //限制加入黑名单时间  单位  秒
    public $join_black_list_time=60*30;

    protected $app_map=[
        '1911'=>'sign'
    ];
    private $app_power=[
        '1911'=>[
            'login',
            'register',
            'index'
        ]
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->_checkApiAccessLimit();
        $all_data=$request->post('all_data');
        $data=$this->aesDecrypt($all_data['data']);
        $this->checkReplayRequest($data);
        $this->checkPower($data['app_id']);
        $this->serviceSign($data,$all_data['sign']);
        //传递h5的data参数
        $request->attributes->add(['data'=>$data]);
        return $next($request);
    }

    protected function checkReplayRequest($data){
        if(isset($data['time'])&&isset($data['rand'])){
            $code=$data['time'].$data['rand'];
        }else{
            $this->commonController()->fail(1,'缺少必要参数rand和time');
        }
        $replay_quest='replay_check_request';
        if(Redis::sadd($replay_quest,$code)){
            if(Redis::scard($replay_quest)==1){
                Redis::expire($replay_quest,120);
            }
            return true;
        }else{
            $this->commonController()->fail(1,'replay request');
        }
    }

    /**
     * 接口鉴权
     */
    protected function checkPower($app_id){
        if(!array_key_exists($app_id,$this->app_power)){
            $this->commonController()->fail(1,'你没有调用该接口的权限');
        }
        $all_power=$this->app_power[$app_id];
        $url=request()->route()->uri();
        if(!in_array($url,$all_power)){
            $this->commonController()->fail(1,'请联系管理员开通该接口权限');
        }
        return true;
    }


    /**
     * 解密数据
     * @param $data
     */
    protected function aesDecrypt($data){
        $key=env('KEY');
        $iv=env('IV');
        $data=base64_decode($data);
        $data=openssl_decrypt($data,'AES-256-CBC',$key,OPENSSL_RAW_DATA,$iv);
        return json_decode($data,true);
    }

    /**
     * 服务器生成的签名验签
     */
    protected function serviceSign($data,$sign){
        if(!isset($data['app_id'])){
            echo json_encode(['errno'=>1,'msg'=>'缺少app_id']);die;
        }
        if(!array_key_exists($data['app_id'],$this->app_map)){
            echo json_encode(['errno'=>1,'msg'=>'丢失必要参数app_secret']);die;
        }
        ksort($data);
        $service_sign=http_build_query($data);
        $service_sign=$service_sign.'&app_secret='.$this->app_map[$data['app_id']];
        if($sign!==md5($service_sign)){
            echo json_encode(['errno'=>1,'msg'=>'验签失败']);die;
        }
        return true;
    }


    /**
     * new公共控制器
     */
    public function commonController(){
        $common_obj=new CommonController();
        return $common_obj;
    }

    /**
     * 检测接口访问是否太过频繁
     */
    private function _checkApiAccessLimit(){
        $common_obj=new CommonController();
        $request=request();
        //黑名单在redis中对应的key
        $black_list_key='black_list';
        //获取客户端的ip
        $client_ip=$request->ip();
        //设置对应的key
        $ip_key='ips:'.$client_ip;
        $now=time();
        //取出redis的黑名单
        $black_list=Redis::zRange($black_list_key,0,-1,true);
        //判断当前的ip是否在黑名单中
        if(array_key_exists($ip_key,$black_list)){
            //取出当前ip加入黑名单的时间
            $join_time=$black_list[$ip_key];
            //如果没有超过我们限定的时间,不允许访问接口
            if((time()-$join_time)<$this->join_black_list_time){
                $common_obj->fail(1,'你的IP已经被加入黑名单,请稍后再试');
            }else{
                //超过我们指定的时间,把IP从黑名单移除
                Redis::zRem($black_list_key,$ip_key);
                //访问次数需要从1开始累计
                Redis::del($ip_key.':'.substr($now,0,-1).'0');
            }
        }

        //当前的访问次数(每隔10s写入一个key中)
        $this_key=$ip_key.':'.substr($now,0,-1).'0';
        //当前的访问次数自增+1
        $this_number=Redis::Incr($this_key);
        //设置key的有效期为1分钟
        if($this_number==1){
            Redis::expire($this_key,60);
        }
        //取出当前50s之前的访问次数
        $i=1;
        $all_number=0;
        while($i<6){
            $arr=explode(':',$this_key);
            $time=array_pop($arr);
            $before_key=$time-$i*10;
            $all_number+=Redis::get($ip_key.':'.$before_key);
            $i++;
        }
        //累加访问次数
        $all_number=$this_number+$all_number;
        //访问次数超过限制
        if($all_number>=$this->number_limit){
            //把ip加入黑名单
            Redis::zAdd($black_list_key,time(),$ip_key);
            //提示错误信息
            $common_obj->fail(1,'访问次数过多,请稍后再试');
        }
    }
}