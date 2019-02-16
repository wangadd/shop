<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Test extends Controller
{
    public function test(){
        echo 'hello world';
    }
}
