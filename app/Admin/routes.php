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
    $router->get('/auth/wxmedia', 'WxmediaController@index');

});
