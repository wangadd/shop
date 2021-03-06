<?php

namespace App\Http\Controllers\Cart;


use DemeterChain\C;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\GoodsModel;
use App\Model\CartModel;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class Cart extends Controller
{
    public function __construct()
    {
        if(empty($_COOKIE['uid'])){
            $this->middleware('auth');
        }
    }
    /** 商品列表展示 */
    public function cartGoods(){
        if(empty($_COOKIE['uid'])){
            $uid=Auth::id();
        }else{
            $uid=$_COOKIE['uid'];
        }
        $list=GoodsModel::paginate(5);
        $info=[
            'uid'=>$uid,
            'list'=>$list
        ];
        return view('cart.goodslist',$info);
    }
    /** 购物车列表展示 */
    public function cartlist(Request $request){
        if(empty($_COOKIE['uid'])){
            $uid=Auth::id();
        }else{
            $uid=$_COOKIE['uid'];
        }
        $where=[
            'uid'=>$uid
        ];
        $info=CartModel::where($where)->get();
        $data=[
            'uid'=>$uid,
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
        if(empty($_COOKIE['uid'])){
            $uid=Auth::id();
        }else{
            $uid=$_COOKIE['uid'];
        }
        $token=$request->session()->get('u_token');
        $where=[
            'goods_id'=>$goods_id,
            'uid'=>$uid
        ];
        //根据goods_id,uid,查询购物车表
        $arr=CartModel::where($where)->first();
        if(!empty($arr)){
            //之前已经添加过   做累加
            $num=$arr->num;
            $updateinfo=[
                'num'=>$num+$buy_num
            ];
            $id=CartModel::where($where)->update($updateinfo);
        }else{
            //  直接添加
            $data=[
                'goods_id'=>$goods_id,
                'num'=>$buy_num,
                'add_time'=>time(),
                'uid'=>$uid,
                'session_token'=>$token
            ];
            $id=CartModel::insert($data);
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
    public function del(Request $request,$id){
        if(empty($id)){
            exit('此商品不在购物车中');
        }
        if(empty($_COOKIE['uid'])){
            $uid=Auth::id();
        }else{
            $uid=$_COOKIE['uid'];
        }
        $where=[
            'id'=>$id,
            'uid'=>$uid
        ];
        $arr=CartModel::where($where)->first();
        $goods_id=$arr->goods_id;
        $num=$arr->num;
        $res=CartModel::where($where)->delete();
        if($res){
            $goodsWhere=[
                'goods_id'=>$goods_id
            ];
            $goodsInfo=GoodsModel::where($goodsWhere)->first();
            $updateInfo=[
                'goods_stock'=>$goodsInfo->goods_stock+$num,
            ];
            GoodsModel::where($goodsWhere)->update($updateInfo);
            echo "删除成功";
            header("refresh:2;url=/cartlist");
        }else{
            echo "删除失败";
            header("refresh:2;url=/cartlist");
        }
    }
}
