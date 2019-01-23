<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{


    /**
     * 用户列表展示
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function usershow(Request $request)
    {
            if($_COOKIE['token'] != $request->session()->get('u_token')){
                die("非法请求");
            }else{
                echo '正常请求';
            }
            if(empty($_COOKIE['uid'])){
                echo "您还没有登录，正在为您跳转至登陆页面";
                header("refresh:2;url=/userlogin");
                exit;
            }
	        $info=UserModel::all();
	        $data=[
	          'info'=>$info
            ];

	        return view('user.userlist',$data);
    }
    /**
     * 用户注册
     * 2019年1月3日14:26:56
     * liwei
     */
    public function reg()
    {
        return view('user.reg');
    }

    public function doReg(Request $request)
    {
        $pwd=$request->input('u_pwd');
        $qpwd=$request->input('u_qpwd');
        if(empty($request->input('u_name'))){
            exit('用户名不能为空');
        }else{
            //验证唯一性
            $userWhere=[
                'name'=>$request->input('u_name')
            ];
            $info=UserModel::where($userWhere)->first();
            if(!empty($info)){
                echo '该用户名已被注册';
                header("refresh:2;url=/userreg");
                exit;
            }
        }
        if(empty($request->input('u_pwd'))){
            exit('密码不能为空');
        }
        if($pwd!==$qpwd){
            echo '密码和确认密码必须一致';exit;
        }else{
            $pwd=password_hash($pwd,PASSWORD_BCRYPT);
        }
        $data = [
            'name'  => $request->input('u_name'),
            'age'  => $request->input('u_age'),
            'pwd'  => $pwd,
            'email'  => $request->input('u_email'),
            'reg_time'  => time(),
        ];

        $uid = UserModel::insertGetId($data);
        if($uid){
            echo '注册成功';
            setcookie('uid',$uid,time()+60*60*24,'/','',false,true);
            header("refresh:2;url=/userlogin");
        }else{
            echo '注册失败';
        }
    }
    /**用户登录*/
    public function loginview(){
        return view('user.login');
    }
    public function userlogin(Request $request){
        $u_name=$request->input('u_name');
        $pwd=$request->input('u_pwd');
        $key=Redis::put('key');
        $arr=unserialize($key);
        if(!empty($key)){
            echo "登录成功";
        }else{
            //从数据库中查询
            $where=[
                'name'=>$u_name,
            ];
            $data=UserModel::where($where)->first();
            $uid=$data->uid;
            if(empty($data)){
                echo '账号或密码有误';exit;
            }else{
                if( password_verify($pwd,$data->pwd) ){
                    echo "登录成功";
                    $token=substr(time().rand(0,99999),10,10);
                    setcookie('uid',$uid,time()+60*60*24,'/','',false,true);
                    setcookie('token',$token,time()+86400,'/','',false,true);
                    $request->session()->put('u_token',$token);
                    $request->session()->put('uid',$uid);
                    $str=serialize($data);
                    Redis::set('key',$str,60);
                    header("Refresh:3;url=/goods");

                }else{
                    die("密码不正确");
                }

            }
        }
    }
    /** 退出 */
    public function quit(){
        setcookie('uid','',time()-1);
        header("refresh:0;url=/userlogin");
    }
}

