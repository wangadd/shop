@extends('layouts.bootstrap')

<title>用户登录</title>

@section('content')

    <body>
        <form action="/userlogin" method="post">
            {{csrf_field()}}
            <table class="table table-bordered">
                <h2>用户登录</h2>
                <tr>
                    <td>用户名：</td>
                    <td><input type="text" name="u_name"></td>
                </tr>
                <tr>
                    <td>密码</td>
                    <td><input type="password" name="u_pwd"></td>
                </tr>
            </table>
            <input class="btn btn-danger" type="submit" value="提交">
            <button class="btn btn-danger"><a href="/userreg" style="text-decoration: none; color: #ffffff;" >注册</a></button>
            <button class="btn btn-danger"><a href="/useruppwd" style="text-decoration: none; color: #ffffff;" >忘记密码</a></button>
        </form>
    </body>
@endsection
