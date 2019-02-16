@extends('layouts.bootstrap')

@section('content')
    <h1>UID: <font color="red">{{$uid}}</font>欢迎回来</h1>
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
                <td>{{date("Y-m-d H:i:s",$v->add_time)}}</td>
                <td>
                    <button class="btn btn-danger"><a href="/cartdel/{{$v->id}}" style="text-decoration: none; color: #ffffff;">删除</a></button>

                </td>
            </tr>
        @endforeach
    </table>
    <button class="btn btn-danger"><a href="/addorder" style="text-decoration: none; color: #ffffff;">立即购买</a></button>
    <button class="btn btn-danger"><a href="/quit" style="text-decoration: none; color: #ffffff;" >退出</a></button>
@endsection