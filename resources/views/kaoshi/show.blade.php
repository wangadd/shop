<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table border="1">
        <tr>
            <td>id</td>
            <td>用户名</td>
            <td>是否在线</td>
            <td>操作</td>
        </tr>
        @foreach($info as $k=>$v)
            <tr>
                <td>{{$v->id}}</td>
                <td>{{$v->username}}</td>
                <td>
                    @if($v->status==1)
                        在线
                    @else
                        不在线
                    @endif
                </td>
                <td><a href="/loginout?id={{$v->id}}">强制下线</a></td>
            </tr>
        @endforeach
    </table>
</body>
</html>