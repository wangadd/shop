@extends('layouts.bootstrap')

@section('content')
    <div class="panel-body">
        <form class="form-horizontal" method="POST" action='/weixin/groupsending'>
        {{ csrf_field() }}
            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <textarea name="group"  cols="30" rows="10"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <button type="submit" class="btn btn-primary">
                        立即群发
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('footer')
    @parent

@endsection