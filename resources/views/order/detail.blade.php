@extends('layouts.app')

@section('content')
    <h1>订单号: <font color="red">{{$order_num}}</font></h1>
    <form>
        @foreach($info as $v)
        <table border="1" color="red" class="table table-bordered">
            <tr>
                <td><h5>商品名称</h5></td>
                <td>{{$v->goods_name}}</td>
            </tr>
            <tr>
                <td><h5>商品价格</h5></td>
                <td>{{$v->goods_price /100}}</td>
            </tr>
            <tr>
                <td><h5>购买数量</h5></td>
                <td>{{$v->buy_number}}</td>
            </tr>
            <tr>
                <td><h5>小计</h5></td>
                <td class="goods_amont">{{$v->buy_number*$v->goods_price / 100}}</td>
            </tr>
        </table>
        @endforeach
        <div>
            <font id="amount" style="color:#ff000a; font-size: 25px;"></font>
        </div>

    </form>
    <br/>

    @if($orderInfo->order_status==1)
        <button class="btn btn-danger"><a href="/pay/test/{{$order_num}}" style="text-decoration: none; color: #ffffff;">支付宝支付</a></button>
        <button class="btn btn-danger"><a href="/weixin/pay/test/{{$order_num}}" style="text-decoration: none; color: #ffffff;">微信支付</a></button>
        <button class="btn btn-danger"><a href="/orderlist" style="text-decoration: none; color: #ffffff;">全部订单</a></button>
        <button class="btn btn-danger"><a href="/goods" style="text-decoration: none; color: #ffffff;">继续购买商品</a></button>
        <button class="btn btn-danger"><a href="/orderdel/{{$order_num}}" style="text-decoration: none; color: #ffffff;">取消订单</a></button>
    @elseif($orderInfo->order_status==2)
        <button class="btn btn-danger">已支付</button>
        <button class="btn btn-danger"><a href="/orderlist" style="text-decoration: none; color: #ffffff;">全部订单</a></button>
        <button class="btn btn-danger"><a href="/goods" style="text-decoration: none; color: #ffffff;">继续购买商品</a></button>
    @else
        <button class="btn btn-danger"><a href="/recoveorder/{{$order_num}}" style="text-decoration: none; color: #ffffff;">恢复订单</a></button>
        <button class="btn btn-danger"><a href="/orderlist" style="text-decoration: none; color: #ffffff;">全部订单</a></button>
        <button class="btn btn-danger"><a href="/goods" style="text-decoration: none; color: #ffffff;">继续购买商品</a></button>
        <button class="btn btn-danger">订单已取消</button>
    @endif

@endsection
@section('footer')
    @parent
    <script>
        $(function(){
            var amount=0;
            $('.goods_amont').each(function(i,v){
                var _this=$(this);
                var goods_amount=parseFloat($(this).text());
                amount+=goods_amount
            })
            $('#amount').text('总价：'+amount+'￥');
        })
    </script>
@endsection
