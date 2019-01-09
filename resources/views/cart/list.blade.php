@extends('layouts.bootstrap')

@section('content')
    <h1>UID: <font color="red">{{$_COOKIE['uid']}}</font>欢迎回来</h1>
    <table border="1" class="table table-bordered">
        <tr>
            <td>ID</td>
            <td>商品ID</td>
            <td>购买数量</td>
            <td>添加时间</td>
            <td>操作</td>
        </tr>
        @foreach($info as $v)
            <tr>
                <td>{{$v->id}}</td>
                <td>{{$v->goods_id}}</td>
                <td>{{$v->num}}</td>
                <td>{{date("Y-m-d",$v->add_time)}}</td>
                <td><a href="/cartdel/{{$v->id}}">删除</a></td>
            </tr>
        @endforeach
    </table>
    <button class="btn btn-danger"><a href="/quit" style="text-decoration: none; color: #ffffff;" >退出</a></button>
@endsection