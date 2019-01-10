<?php

namespace App\Http\Controllers\Order;

use App\Model\CartModel;
use App\Model\DetailModel;
use App\Model\GoodsModel;
use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Order extends Controller
{
   /** 订单展示 */
   public function orderList(Request $request){
       $uid=$request->session()->get('uid');
       $where=[
           'uid'=>$uid
       ];
       $data=OrderModel::where($where)->orderBy('add_time','desc')->take(3)->get();
       $info=[
           'data'=>$data
       ];
        return view('order.list',$info);
   }
   /** 生成订单 */
   public function reorder(Request $request){
       $uid=$request->session()->get('uid');
       if(empty($uid)){
           exit('请选择要购买的商品');
       }
       $cartWhere=[
           'uid'=>$uid,
       ];
       $cartInfo=CartModel::where($cartWhere)->get();
       $goodsInfo=[];
       foreach ($cartInfo as $k=>$v){
           $goods_id=$v->goods_id;
           $goodsWhere=[
               'goods_id'=>$goods_id
           ];
           $goodsArr=GoodsModel::where($goodsWhere)->first();
           $goodsArr['buy_number']=$v->num;
           $goodsInfo[]=$goodsArr;
       }
       if(empty($goodsInfo)){
           exit('购物车中没有商品');
       }

       //生成订单号
       $order_sn = OrderModel::generateOrderSN();
       $order_amount = 0;
       foreach($goodsInfo as $k=>$v){
           //计算订单价格 = 商品数量 * 单价
           $order_amount += $v->goods_price * $v->buy_number;
           //减少库存
           $goodsWhere=[
               'goods_id'=>$goods_id
           ];
           $goodsUpdate=[
               'goods_stock'=>$v->goods_stock-$v->buy_number
           ];
           $res=GoodsModel::where($goodsWhere)->update($goodsUpdate);
           $arr=[
               'order_num'=>$order_sn,
               'goods_name'=>$v->goods_name,
               'goods_price'=>$v->goods_price,
               'buy_number'=>$v->buy_number,
               'uid'=>$request->session()->get('uid')
           ];
           $result=DetailModel::insert($arr);
       }
       $data=[
           'order_num'      => $order_sn,
           'uid'           => session()->get('uid'),
           'add_time'      => time(),
           'order_amount'  => $order_amount
       ];
       $oid = OrderModel::insertGetId($data);
       if($oid){
           //清空购物车
           CartModel::where(['uid'=>session()->get('uid')])->delete();
           echo "下单成功";
           header("refresh:2;url=/orderlist");
       }else{
           echo "下单失败";
       }
   }
   /** 订单详情页 */
   public function orderDetail(Request $request,$order_num){
        $where=[
            'order_num'=>$order_num,
            'uid'=>$request->session()->get('uid')
        ];
        $info=DetailModel::where($where)->get();
        $data=[
            'order_num'=>$order_num,
            'info'=>$info
        ];
        return view("order.detail",$data);
   }
}
