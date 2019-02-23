@extends('layouts.bootstrap')

@section('content')
        <h1 align="center">和 <font color="red" id="nickname">{{$info->nickname}}</font>聊天界面</h1>
        <div style="margin:0 auto; width:700px;height:500px; overflow-y: scroll;  border:solid red 1px;" id="demo">

        </div>
        <div style="margin:0 auto; width:700px;height:30px;">
        <form class="form-inline">
                <div class="form-group">
                        <label class="sr-only" for="goods_num">Amount (in dollars)</label>
                        <div class="input-group">
                                <input type="text" class="form-control" style="width:700px;height:50px;" id="text">
                        </div>
                </div>
                <input type="hidden" id="openid" value="{{$info->openid}}">
                <button type="submit" class="btn btn-primary" id="add_cart_btn">发送</button>
        </form>
        </div>

@endsection
@section('footer')
    @parent
    <script>
        $(function(){

            $('#add_cart_btn').click(function (e) {
                e.preventDefault();
                var nickname=$('#nickname').text();
                var openid=$('#openid').val();
                var text=$('#text').val();
                setInterval(function () {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url     :   '/weixin/getmsg',
                        type    :   'post',
                        data    :   {openid:openid},
                        success :   function(res){
                            $('#demo').empty();
                            $.each(res,function(i,n){
                                if(n['senduser']==openid){
                                    var _t="<h4 color='green'>"+nickname+":"+n['text']+"</h4>";
                                    $('#demo').append(_t)

                                }else{
                                    var _h="<h4 color='green'>客服:"+n['text']+"</h4>";
                                    $('#demo').append(_h)

                                }
                            })

                        },
                        dataType:'json',
                    });
                },5000)

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url     :   '/weixin/send',
                    type    :   'post',
                    data    :   {openid:openid,text:text},
                    success :   function(res){
                        if(res.code=='1'){
                            var _h="<h4 color='green'>客服:"+text+"</h4>";
                            $('#demo').append(_h)
                            $('#text').val('');
                        }
                    },
                    dataType:'json',
                });


            })

        })
    </script>
@endsection