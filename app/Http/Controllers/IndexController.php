<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoodsModel;
class IndexController extends CommonController
{
    //
    public function index(Request $request){
       $goods_new_info=GoodsModel::orderBy('add_time','desc')->limit(4)->get();
       $goods_top_info=GoodsModel::orderBy('click_count','desc')->limit(4)->get();
       $goods_info['goods_new_info']=$goods_new_info;
       $goods_info['goods_top_info']=$goods_top_info;
       return $this->responseArr(200,'ok',$goods_info);
    }
}
