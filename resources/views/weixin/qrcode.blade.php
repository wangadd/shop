@extends('layouts.bootstrap')

@section('content')
    <div style="margin: 0 auto;  width:270px; height:300px;">
        <div id="qrcode" ></div>
        <h3 style="color:red" align="center">扫一扫立即付款</h3>
        <input type="hidden" id="code_url" value="{{$code_url}}">
        <input type="hidden" id="order_num" value="{{$order_num}}">
    </div>

@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('js/qrcode.js')}}"></script>
    <script>
        var code_url=$('#code_url').val();
        var order_num=$('#order_num').val();
        var qrcode = new QRCode('qrcode', {
            text: code_url,
            width: 256,
            height: 256,
            colorDark : '#000000',
            colorLight : '#ffffff',
            correctLevel : QRCode.CorrectLevel.H
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url     :   '/weixin/pay/find',
            type    :   'post',
            data    :   {order_num:order_num},
            success :   function(res){
                if(res.code==1){
                    alert('支付成功');
                    location.href="/orderlist";
                }
            },
            dataType:'json',
        });
    </script>
@endsection