@extends('layouts.app')

@section('content')
    <h1>欢迎回来</h1>
    <table border="1" style="width: 700px;height:300px;">
        <tr align="center">
            <td>商品ID</td>
            <td>商品名称</td>
            <td>商品库存</td>
            <td>商品价格</td>
            <td>操作</td>
        </tr>
        @foreach($list as $v)
        <tr align="center">
            <td>{{$v->goods_id}}</td>
            <td>{{$v->goods_name}}</td>
            <td>{{$v->goods_stock}}</td>
            <td>{{$v->goods_price / 100}}</td>
            <td><a href="/create/{{$v->goods_id}}">商品信息</a></td>
        </tr>
        @endforeach
    </table>
    {{$list->links()}}
    <br/>
    <button class="btn btn-danger"><a href="/quit" style="text-decoration: none; color: #ffffff;" >退出</a></button>
@endsection