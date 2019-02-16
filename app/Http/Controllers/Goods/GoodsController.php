<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoodsController extends Controller
{
   /**
    *
    * s上传文件视图
    */
   public function upload(){
       return view('goods.upload');
   }
   public function uploadDo(Request $request){
        $pdf=$request->file('file');
        $name=$pdf->extension();
        if($name!='pdf'){
            exit('请上传正确格式的文件');
        }
        $res=$pdf->storeAs(date('Ymd'),str_random('5').'.pdf');
        if($res){
            echo "上传成功";
        }
   }
}
