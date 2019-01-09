@extends('layouts.bootstrap')

@section('content')
    <h1>UID: <font color="red">{{$_COOKIE['uid']}}</font>欢迎回来</h1>
    <table border="1" class="table table-bordered">
        <tr>
            <td>商品ID</td>
            <td>商品名称</td>
            <td>商品库存</td>
            <td>商品价格</td>
            <td>操作</td>
        </tr>
        @foreach($data as $v)
        <tr>
            <td>{{$v->goods_id}}</td>
            <td>{{$v->goods_name}}</td>
            <td>{{$v->goods_stock}}</td>
            <td>{{$v->goods_price / 100}}</td>
            <td><a href="/create/{{$v->goods_id}}">添加购物车</a></td>
        </tr>
        @endforeach
    </table>
    <button class="btn btn-danger"><a href="/quit" style="text-decoration: none; color: #ffffff;" >退出</a></button>
@endsection