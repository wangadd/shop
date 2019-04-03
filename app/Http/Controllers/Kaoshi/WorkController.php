<?php

namespace App\Http\Controllers\Kaoshi;

use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkController extends Controller
{
    //登录视图
    public function index(){
        return view('kaoshi.test');
    }
    public function doLogin(Request $request){
        $username=$request->input('username');
        $pwd=$request->input('pwd');
        $data=[
            'u'=>$username,
            'p'=>$pwd,
        ];
        $url="http://hao.tactshan.com/login";
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $rs = curl_exec($ch);
        $arr=json_decode($rs);
        if($arr->code==1){
            $token=$arr->token;
            $info=[
                'code'=>1,
                'msg'=>'登录成功',
                'token'=>$token,
                'uid'=>$arr->uid
            ];
            $update=[
                'status'=>1,
                'u_time'=>time()
            ];
            UserModel::where(['username'=>$username])->update($update);
        }elseif ($arr->code==40010){
            $info=[
                'msg'=>'已在其他终端登录',
            ];
        }else{
            $info=[
                'msg'=>'登录失败',
            ];
        }
        return $info;

    }



    //展示
    public function show(){
        $data=$_GET;
        $url="http://hao.tactshan.com/show";
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $rs = curl_exec($ch);
        $arr=json_decode($rs);
        if($arr->code==1){
            $info=UserModel::all();
            $data=[
                'info'=>$info
            ];
        }
        return view('kaoshi.show',$data);
    }


    public function loginout(){
        $data=$_GET;
        $url="http://hao.tactshan.com/quit";
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $rs = curl_exec($ch);
        $arr=json_decode($rs);
        if($arr->code==1){
            echo "退出成功";

            $update=[
                'status'=>0,
                'u_time'=>time()
            ];
            UserModel::where(['id'=>$data['id']])->update($update);
        }


    }


}
