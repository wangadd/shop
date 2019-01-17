<?php

namespace App\Admin\Controllers;


use App\Http\Controllers\Controller;
use App\Model\GoodsModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use phpDocumentor\Reflection\Location;

class GoodsController extends Controller{
    /** 商品列表页 */
    public function index(Content $content){
        return $content
            ->header('商品管理')
            ->description('商品列表')
            ->body($this->table());
    }
    /**
     *商品列表详情页
     */
    public function table(){
        $grid=new Grid(new GoodsModel());
        $grid->model()->orderBy('goods_id','desc');
        $grid->goods_id('商品id');
        $grid->goods_name('商品名称');
        $grid->goods_price('商品价格');
        $grid->goods_stock('商品库存');
        $grid->create_at('添加时间')->display(function($time){
            return date('Y-m-d H:i:s',$time);
        });
        return $grid;
    }
    /** 商品添加 */
    public function create(Content $content){
        return $content
            ->header('商品管理')
            ->description('添加')
            ->body($this->form());
    }
    /**
     * 添加内容主题
     */
    protected function form()
    {
        $form = new Form(new GoodsModel());

        $form->display('goods_id', '商品ID');
        $form->text('goods_name', '商品名称');
        $form->number('goods_stock', '库存');
        $form->currency('goods_price', '价格')->symbol('¥');
        $form->editor('goods_desc','描述');
        return $form;
    }
    /**
     *执行添加
     */
    public function store()
    {
        $data = $_POST;
        unset($data['_token']);
        unset($data['_previous_']);
        $data['goods_price'] = $data['goods_price'] * 100;
        $res=GoodsModel::insert($data);
        if($res!==false){
            header("Location:http://shop.lening.com/admin/auth/goods");
        }
    }
    /**
     *商品详情
     */
    public function show(){

    }
    /**
     *删除商品
     */
    public function destroy($id)
    {
        $where=[
            'goods_id'=>$id
        ];
        $res=GoodsModel::where($where)->delete();
        if($res){
            $response = [
                'status' => true,
                'message'   => 'ok'
            ];
            return $response;
        }

    }
    /**
     * 编辑
     */
    public function edit(Content $content,$id){
        return $content
            ->header('商品管理')
            ->description('编辑')
            ->body($this->form()->edit($id));
    }
    /**
     * 执行修改
     */
    public function update($id){
        $data=$_POST;
        unset($data['_token']);
        unset($data['_method']);
        unset($data['_previous_']);
        $data['goods_price']=$data['goods_price']*100;
        $where=[
            'goods_id'=>$id
        ];
        $res=GoodsModel::where($where)->update($data);
        if($res!==false){
            header("Location:http://shop.lening.com/admin/auth/goods");
        }
    }
}