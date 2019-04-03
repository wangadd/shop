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
    <h1>
        登录页面
    </h1>
    <p>
        用户名：<input type="text" id="username">
    </p>
    <p>
        密码：<input type="password"  id="pwd">
    </p>
    <button id="btn">登录</button>
</body>
</html>
<script src="{{URL::asset('/js/jquery-3.2.1.min.js')}}"></script>
<script>
    $(function () {
        $('#btn').click(function () {
            var username=$('#username').val();
            var pwd=$('#pwd').val();
            $.ajax({
                url:"https://https.whandfd.com/kaoshi",
                method:'POST',
                data:{username:username,pwd:pwd},
                success:function (msg) {
                    alert(msg);
                }
            })
        })
    })
</script>