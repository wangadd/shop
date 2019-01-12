<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/adduser','User\UserController@add');

//路由跳转
Route::redirect('/hello1','/world1',301);
Route::get('/world1','Test\TestController@world1');

Route::get('hello2','Test\TestController@hello2');
Route::get('world2','Test\TestController@world2');


//路由参数
Route::get('/user/test','User\UserController@test');
Route::get('/user/{uid}','User\UserController@user');
Route::get('/month/{m}/date/{d}','Test\TestController@md');
Route::get('/name/{str}','Test\TestController@showName');



// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>40300]);


// Query Builder
Route::get('/query/get','Test\TestController@query1');
Route::get('/query/where','Test\TestController@query2');


//Route::match(['get','post'],'/test/abc','Test\TestController@abc');
Route::any('/test/abc','Test\TestController@abc');


//测试
Route::get('/data','User\UserController@data');
Route::get('/view/child','User\UserController@viewTest1');
Route::get('/view/index','User\UserController@viewTest2');


//用户注册
Route::get('/userreg','User\UserController@reg');
Route::post('/userreg','User\UserController@doReg');

//列表展示
Route::get('/userlist','User\UserController@usershow');
//登录
Route::get('/userlogin','User\UserController@loginview');
Route::post('/userlogin','User\UserController@userlogin');
Route::get('/quit','User\UserController@quit');
//购物车
Route::get('/goods','Cart\Cart@cartGoods')->middleware('check.session');
Route::get('/cartlist','Cart\Cart@cartlist')->middleware('check.session');
Route::get('/create/{goods_id}','Cart\cart@create')->middleware('check.session');
Route::post('/doadd','Cart\cart@doAdd')->middleware('check.session');
Route::get('/cartdel/{id}','Cart\cart@del')->middleware('check.session');
//订单
Route::get('/orderlist','Order\order@orderList')->middleware('check.session');
Route::get('/addorder','Order\order@reorder')->middleware('check.session');
Route::get('/orderdetail/{order_num}','Order\order@orderDetail')->middleware('check.session');
Route::get('/orderdel/{order_num}','Order\order@orderDel')->middleware('check.session');
Route::get('/recoveorder/{order_num}','Order\order@recoveOrder')->middleware('check.session');

/** 付款 */
Route::get('/pay/test/{order_num}','Pay\PayController@test');         //测试
Route::get('/pay/{order_num}','Pay\pay@orderPay')->middleware('check.login.token');         //订单支付
Route::post('/pay/alipay/notify','Pay\PayController@notify');        //支付宝支付 通知回调

//中间件测试
Route::get('/test/mid1','Test\TestController@mid1')->middleware('check.uid');        //中间件测试
Route::get('/test/check_cookie','Test\TestController@checkCookie')->middleware('check.cookie');        //中间件测试
