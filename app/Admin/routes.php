<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('/auth/wxuser', 'WeixinController@index');
    $router->get('/auth/goods', 'GoodsController@index');
    $router->resource('/auth/wxmedia', WxmediaController::class);

    //群发消息
    $router->get('/auth/groupsending', 'WxPmMediaController@GroupSendingView');
    $router->post('/auth', 'WxPmMediaController@GroupSending');
    //新增永久素材
    $router->get('/auth/wxpmmedia', 'WxPmMediaController@index');
    $router->get('/auth/wxpmmedia/create', 'WxPmMediaController@create');
    $router->post('/auth/wxpmmedia', 'WxPmMediaController@doCreate');

    //删除素材
    $router->get('/auth/wxpmmedia/delete/{id}', 'WxPmMediaController@delete');

    //获取素材列表
    $router->get('/auth/wxpmmedia/getmedia', 'WxPmMediaController@getPmMedia');
    $router->resource('/auth/wxmedia', WxPmMediaController::class);

});
