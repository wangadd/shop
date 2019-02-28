<?php

namespace App\Http\Controllers\Pay;

use App\Model\OrderModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class Pay extends Controller
{
    public function __construct()
    {
        if(empty($_COOKIE['uid'])){
            $this->middleware('auth');
        }

    }
    /** 调用支付包接口 */
    public function orderPay(Request $request,$order_num){
        if(empty($_COOKIE['uid'])){
            $uid=Auth::id();
        }else{
            $uid=$_COOKIE['uid'];
        }
        echo "支付成功";

        $where=[
            'order_num'=>$order_num
        ];
        $data=[
            'order_status'=>2
        ];
        $orderInfo=OrderModel::where($where)->first();
        $amount=$orderInfo->order_amount;
        $userWhere=[
            'id'=>$uid,
        ];
        $userInfo=UserModel::where($userWhere)->first();
        $userDate=[
            'sort'=>$userInfo->sort+$amount
        ];
        $res=UserModel::where($userWhere)->update($userDate);
        OrderModel::where($where)->update($data);
        header('refresh:2;url=/orderlist');
    }
}
