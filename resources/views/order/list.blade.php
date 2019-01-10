@extends('layouts.bootstrap')

@section('content')
    <h1>UID: <font color="red">{{$_COOKIE['uid']}}</font>欢迎回来</h1>
    <form>
        @foreach($data as $v)
        <table border="1" class="table table-bordered">
            <tr>
                <td>订单号</td>
                <td>{{$v->order_num}}</td>
            </tr>
            <tr>
                <td>订单价格</td>
                <td>{{$v->order_amount /100}}</td>
            </tr>
            <tr>
                <td>添加时间</td>
                <td>{{date("Y-m-d H:i:s",$v->add_time)}}</td>
            </tr>
            <tr>
                <td></td>
                <td><button class="btn btn-danger">
                        <a href="/orderdetail/{{$v->order_num}}" style="text-decoration: none; color: #ffffff;">查看订单详情</a>
                    </button></td>
            </tr>
        </table>
        @endforeach
    </form>
    <button class="btn btn-danger"><a href="/goods" style="text-decoration: none; color: #ffffff;">重新添加商品</a></button>
@endsection
@section('footer')
    @parent

@endsection