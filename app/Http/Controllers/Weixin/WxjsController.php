<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WxjsController extends Controller
{
    public function test(){
        //计算签名
        $data=[
            'appid'=>env('WEIXIN_APPID_1'),
            'timestamp'=>time(),
            'nonceStr' => str_random(10), // 必填，生成签名的随机串
            'signature' => $this->getSign(),// 必填，签名
        ];
        $info=[
            'data'=>$data
        ];
        return view('weixin.jssdk',$info);
    }
    public function getSign(){
        $sign=str_random(15);
        return $sign;
    }
}
