<?php

namespace App\Http\Controllers\Cart;


use DemeterChain\C;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\GoodsModel;
use App\Model\CartModel;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class Cart extends Controller
{
    /** 商品列表展示 */
    public function cartGoods(){
        $data=GoodsModel::all();
        $info=[
            'data'=>$data
        ];
        return view('cart.goodslist',$info);
    }
    /** 购物车列表展示 */
    public function cartlist(Request $request){
        $uid=$request->session()->get('uid');
        $where=[
            'uid'=>$uid
        ];
        $info=CartModel::where($where)->get();
        $data=[
            'info'=>$info
        ];
        return view('cart.list',$data);
    }
    /** 添加商品 */
    public function create($goods_id){
        $where=[
            'goods_id'=>$goods_id
        ];
        $data=GoodsModel::where($where)->first();
        $info=[
            'data'=>$data
        ];
        return view('cart.cartadd',$info);
    }
    /** 执行添加 */
    public  function doAdd(Request $request){
        $goods_id=$request->input('goods_id');
        $buy_num=$request->input('goods_num');
        $uid=$request->session()->get('uid');
        $token=$request->session()->get('u_token');

        $where=[
            'goods_id'=>$goods_id
        ];
        $arr=CartModel::where($where)->first();
        if(!empty($arr)){
            $num=$arr->num;
            $updateinfo=[
                'num'=>$num+$buy_num
            ];
            $id=CartModel::updated($where,$updateinfo);
        }else{
            $data=[
                'goods_id'=>$goods_id,
                'num'=>$buy_num,
                'add_time'=>time(),
                'uid'=>$uid,
                'session_token'=>$token
            ];
            $id=CartModel::insertGetId($data);
        }
        if($id){
            $info=[
                'code'=>1,
                'font'=>'加入购物车成功'
            ];
            echo json_encode($info);
        }else{
            $info=[
                'code'=>2,
                'font'=>'加入购物车失败'
            ];
            echo json_encode($info);
        }

    }
    /** 删除商品 */
    public function del($goods_id){
        //判断 商品是否在 购物车中
        $goods = session()->get('cart_goods');
            //执行删除
        foreach($goods as $k=>$v){
            if($goods_id == $v['goods_id']) {
                session()->pull('cart_goods.' . $k);
                echo "删除成功";
                header("refresh:2;url=/cartlist");
            }
        }
    }
}
