@extends('layouts.bootstrap')

<title>用户注册</title>

@section('content')
    <body>
    <form action="/userreg" method="post">
        {{csrf_field()}}
        <table  class="table table-bordered">
            <tr>
                <td>用户名：</td>
                <td><input type="text" name="u_name"></td>
            </tr>
            <tr>
                <td>密码：</td>
                <td><input type="password" name="u_pwd"></td>
            </tr>
            <tr>
                <td>确认密码：</td>
                <td><input type="password" name="u_qpwd"></td>
            </tr>
            <tr>
                <td>Email: </td>
                <td><input type="text" name="u_email"></td>
            </tr>
            <tr>
                <td>年龄： </td>
                <td><input type="text" name="u_age"></td>
            </tr>
        </table>
        <input class="btn btn-danger" type="submit" value="提交">
    </form>
    </body>
@endsection



