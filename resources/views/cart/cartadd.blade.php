@extends('layouts.bootstrap')
@section('content')
    <div class="container">
        <h1>{{$data->goods_name}}</h1>
        <span> 价格： {{$data->goods_price / 100}}</span>
        <form class="form-inline">
            <div class="form-group">
                <label class="sr-only" for="goods_num">Amount (in dollars)</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="goods_num" value="1">
                </div>
            </div>
            <input type="hidden" id="goods_id" value="{{$data->goods_id}}">
            <button type="submit" class="btn btn-primary" id="add_cart_btn">加入购物车</button>
        </form>
    </div>
@endsection

@section('footer')
    @parent
    <script>
        $(function(){

            $('#add_cart_btn').click(function (e) {
                e.preventDefault();
                var goods_num=$('#goods_num').val();
                var goods_id=$('#goods_id').val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url     :   '/doadd',
                    type    :   'post',
                    data    :   {goods_id:goods_id,goods_num:goods_num},
                    success :   function(res){
                        alert(res.font);
                        if(res.code==1){
                            location.href="/cartlist";
                        }
                    },
                    dataType:'json',
                });
            })
        })
    </script>
@endsection