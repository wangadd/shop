@extends('layouts.bootstrap')
@section('content')
    <div class="container" style="background: #01ff70; width: 300px; height:200px; margin-top:100px;">
        <form action="/goods/upload/do" method="post" enctype="multipart/form-data">
            {{csrf_field()}}
                <br>
                <br>
                <p>
                    <input type="file" name="file">
                </p>
                <br/>
                <br/>
                <br/>
                <br/>
                <p>
                    <button class="btn btn-danger">upload</button>
                </p>
        </form>
    </div>
@endsection

@section('footer')
    @parent
@endsection