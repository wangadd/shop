@extends('layouts.bootstrap')

@section('content')
    <h1>UID: <font color="red">{{$_COOKIE['uid']}}</font>欢迎回来</h1>
    <table border="1" class="table table-bordered">
        <tr>
            <td>用户id</td>
            <td>用户姓名</td>
            <td>年龄</td>
            <td>邮箱号</td>
            <td>添加时间</td>
        </tr>
        @foreach($info as $v)
            <tr>
                <td>{{$v->uid}}</td>
                <td>{{$v->name}}</td>
                <td>{{$v->age}}</td>
                <td>{{$v->email}}</td>
                <td>{{date('Y-m-d H:i:s',$v->reg_time)}}</td>
            </tr>
        @endforeach
    </table>
@endsection