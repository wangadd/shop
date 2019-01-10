@extends('layouts.bootstrap')

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
    <button class="btn btn-danger"><a href="/goods" style="text-decoration: none; color: #ffffff;">去付款</a></button>
    <button class="btn btn-danger"><a href="/goods" style="text-decoration: none; color: #ffffff;">继续购买商品</a></button>

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