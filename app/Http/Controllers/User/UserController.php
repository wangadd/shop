<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\UserModel;

class UserController extends Controller
{
    //

	public function user($uid)
	{
		echo $uid;
	}

	public function test()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

    //用户随机注册
	public function add()
	{
		$data = [
			'name'      => str_random(5),
			'age'       => mt_rand(20,99),
			'email'     => str_random(6) . '@gmail.com',
			'reg_time'  => time()
		];

		$id = UserModel::insertGetId($data);
		var_dump($id);
	}

    /**
     * 用户列表展示
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function usershow()
    {
	        $info=UserModel::all();
	        $data=[
	          'info'=>$info
            ];
	        return view('user.userlist',$data);
    }
    public function viewTest1()
    {
        $data = [];
        return view('user.index',$data);
    }
    public function viewTest2()
    {
        $list = UserModel::all()->toArray();
        
        $data = [
            'title'     => 'XXXX',
            'list'      => $list
        ];

        return view('user.child',$data);
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
        if($pwd!==$qpwd){
            echo '密码和确认密码必须一致';exit;
        }else{
            $pwd=md5($pwd);
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
            header("refresh:2;'/userlogin'");
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
       $where=[
         'name'=>$u_name,
         'pwd'=>md5($pwd)
       ];
       $data=UserModel::where($where)->get()->toArray();

       if(empty($data)){
           echo '账号或密码有误';exit;
       }else{
           echo '登录成功';
           header("refresh:2;'/userlist'");
       }
    }
}
