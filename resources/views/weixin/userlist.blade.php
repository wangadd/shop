@extends('layouts.app')

@section('content')
        <table border="1" class="table table-bordered">
            <tr>
                <td>ID</td>
                <td>openid</td>
                <td>nickname</td>
                <td>sex</td>
                <td>add_time</td>
                <td>头像</td>
                <td>关注事件</td>
                <td>操作</td>
            </tr>
            @foreach($data as $v)
                <tr>
                    <td>{{$v->id}}</td>
                    <td>{{$v->openid}}</td>
                    <td>{{$v->nickname}}</td>
                    <td>
                        @if($v->sex==1)
                            男
                        @else
                            女
                        @endif
                    </td>
                    <td>{{date("Y-m-d H:i:s",$v->addtime)}}</td>
                    <td><img src="{{$v->headimgurl}}"></td>
                    <td>{{date("Y-m-d H:i:s",$v->subscribe_time)}}</td>
                    <td>
                        <button class="btn btn-danger">
                            <a href="/weixin/sendview/{{$v->id}}" style="text-decoration: none; color: #ffffff;">互动</a></button>

                    </td>
                </tr>
            @endforeach
        </table>
@endsection
@section('footer')
    @parent

@endsection