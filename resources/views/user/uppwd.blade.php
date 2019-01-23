@extends('layouts.bootstrap')

<title>修改密码</title>

@section('content')

    <body>
        <form action="/useruppwd" method="post">
            {{csrf_field()}}
            <table class="table table-bordered">
                <h2>修改密码</h2>
                <tr>
                    <td>用户名：</td>
                    <td><input type="text" name="u_name"></td>
                </tr>
                <tr>
                    <td>新密码</td>
                    <td><input type="password" name="u_pwd"></td>
                </tr>
                <tr>
                    <td>确认密码</td>
                    <td><input type="password" name="u_pwd"></td>
                </tr>
            </table>
            <input class="btn btn-danger" type="submit" value="修改">
        </form>
    </body>
@endsection
