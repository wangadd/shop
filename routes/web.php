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
Route::post('/reg','User\UserController@reg1');
Route::post('/denglu','User\UserController@doLogin');
Route::get('/mancenter','User\UserController@userinfo');
//列表展示
Route::get('/userlist','User\UserController@usershow');
//登录
Route::get('/userlogin','User\UserController@loginview');
Route::post('/userlogin','User\UserController@userlogin');
Route::get('/quit','User\UserController@quit');
//修改密码
Route::get('/useruppwd','User\UserController@updatePwd');
Route::post('/useruppwd','User\UserController@doUpdate');
//购物车
Route::get('/goods','Cart\Cart@cartGoods');
Route::get('/cartlist','Cart\Cart@cartlist');
Route::get('/create/{goods_id}','Cart\Cart@create');
Route::post('/doadd','Cart\Cart@doAdd');
Route::get('/cartdel/{id}','Cart\Cart@del');
//订单
Route::get('/orderlist','Order\Order@orderList');
Route::get('/addorder','Order\Order@reorder');
Route::get('/orderdetail/{order_num}','Order\Order@orderDetail');
Route::get('/orderdel/{order_num}','Order\Order@orderDel');
Route::get('/recoveorder/{order_num}','Order\Order@recoveOrder');

/** 付款 */
Route::get('/pay/test/{order_num}','Pay\PayController@test');         //测试
Route::post('/pay/alipay/notify_url','Pay\PayController@notify_url');       //支付宝支付 异步通知回调
Route::get('/pay/alipay/return_url','Pay\PayController@return_url');        //支付宝支付 同步通知回调

//中间件测试
Route::get('/test/mid1','Test\TestController@mid1')->middleware('check.uid');        //中间件测试
Route::get('/test/check_cookie','Test\TestController@checkCookie')->middleware('check.cookie');        //中间件测试

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
//计划任务
Route::get('/pay/delete','Pay\PayController@deleteOrder');
Route::get('/test','Test\Test@test');
//文件上传
Route::get('/goods/test','Goods\GoodsController@upload');
Route::post('/goods/upload/do','Goods\GoodsController@uploadDo');



//微信测试
Route::get('/weixin/valid','Weixin\WxController@valid');
Route::post('/weixin/valid','Weixin\WxController@wxEvent');
Route::get('/weixin/test','Weixin\WxController@test');
Route::get('/weixin/create_menu','Weixin\WxController@createMenu');
Route::get('/weixin/groupsending','Weixin\WxController@GroupSendingView');
Route::post('/weixin/groupsending','Weixin\WxController@GroupSending');
Route::get('/weixin/getmediaid','Weixin\WxController@getMediaId');//获取media_id



//微信互动    用户信息表
Route::get('/weixin/wxuser','Weixin\WxController@Wxuser');
Route::get('/weixin/sendview/{id}','Weixin\WxController@sendView');
Route::post('/weixin/send','Weixin\WxController@send');
Route::post('weixin/sendview/{id}','Weixin\WxController@huifu');
Route::post('weixin/getmsg','Weixin\WxController@getMsg');


//微信支付
Route::get('/weixin/pay/test/{order_num}','Weixin\PayController@test');
Route::any('/weixin/pay/notice','Weixin\PayController@notice');
Route::post('/weixin/pay/find','Weixin\PayController@find');



//微信登录
Route::get('/weixin/getcode','Weixin\PayController@getCode');

//jssdk
Route::get('/weixin/jssdk','Weixin\WxjsController@test');
Route::any('/curl','Weixin\WxjsController@test1');



//加密、解密 测试
Route::any('/test/sign','Test\TestController@sign');
Route::any('/test/sign1','Test\TestController@sign1');


//考试登录试图
Route::get('/kaoshi','Kaoshi\WorkController@index');

Route::post('/kaoshi','Kaoshi\WorkController@doLogin');
//展示
Route::get('/show','Kaoshi\WorkController@show');
//退出
Route::get('/loginout','Kaoshi\WorkController@loginout');






