<?php

namespace App\Http\Controllers\Pay;

use App\Model\OrderModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Pay extends Controller
{
    /** 调用支付包接口 */
    public function orderPay(Request $request,$order_num){

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
            'uid'=>$request->session()->get('uid'),
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
