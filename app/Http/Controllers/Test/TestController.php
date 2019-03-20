<?php

namespace App\Http\Controllers\Test;

use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use DB;
class TestController extends Controller
{
    public $rsaPrivateKeyFilePath = './key/vm_priv.key';
    public $aliPubKey = './key/vm_pub.key';
    public function sign()
    {
        $now=time();
        $url = "http://lara.api.com/user/sign?time=".$now;

        $str = "hello php";
        $key = "wang";
        $salt="xxxxx";
        $iv = substr(md5($now.$salt),5,16);        //固定16位          //8c8bcb75ea5062a8
        $ch = curl_init();
        //加密
        $enc_str = openssl_encrypt($str, "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv);
        $post_info=base64_encode($enc_str);

        //设置签名
        $priv_key=file_get_contents($this->rsaPrivateKeyFilePath);
        $res = openssl_get_privatekey($priv_key);
        $rs=openssl_sign($post_info, $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        $info=[
            'str'=>$post_info,
            'sign'=>$sign,
        ];
        //向服务器发送数据
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $result=curl_exec($ch);
        $response=json_decode($result);
        $time=$response->t;
        $response_str=$response->str;
        $client_str=base64_decode($response_str);
        $new_iv=substr(md5($time.$salt),5,16);
        $last_str = openssl_decrypt($client_str, "AES-128-CBC", $key, OPENSSL_RAW_DATA, $new_iv);
        echo $last_str;
        curl_close($ch);
    }


}
