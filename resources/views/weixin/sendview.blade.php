@extends('layouts.bootstrap')

@section('content')
        <h1 align="center">和 <font color="red">{{$info->nickname}}</font>聊天界面</h1>
        <div style="margin:0 auto; width:700px;height:500px; border:solid red 1px;" id="demo">
                {{--<table  border="1" class="table table-bordered" >--}}
                    {{--@foreach($textInfo as $k=>$v)--}}
                        {{--<tr>--}}
                           {{--@if($v->openid=='1')--}}
                               {{--客服:{{$v->text}}--}}
                           {{--@else--}}
                              {{--{{$info->nickname}}:{{$v->text}}--}}
                           {{--@endif--}}
                        {{--</tr>--}}
                    {{--@endforeach--}}
                {{--</table>--}}
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
                var openid=$('#openid').val();
                var text=$('#text').val();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url     :   '/weixin/send',
                    type    :   'post',
                    data    :   {openid:openid,text:text},
                    success :   function(res){
                        alert(res.msg)
                        if(res.code=='1'){
                            var _h="<h4 color='green'>客服:"+text+"</h4>";
                            $('#demo').append(_h)
                            $('#text').val('');
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url     :   '/weixin/huifu',
                                type    :   'post',
                                data    :   {},
                                success :   function(res){
                                    var _h="<h4 color='green'>用户:"+res.text+"</h4>";
                                    $('#demo').append(_h)
                                    $('#text').val('');
                                },
                                dataType:'json',
                            });
                        }
                    },
                    dataType:'json',
                });


            })

        })
    </script>
@endsection