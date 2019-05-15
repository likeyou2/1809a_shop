<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    //用户
    $router->resource('/user',PostController::class);
    //素材
    $router->get('/materialAdd','MatersController@index');
    $router->post('/materialAddDo','MatersController@materialAddDo');
    $router->resource('/maters',MatersShowController::class);
    //自定义菜单
    $router->get('/MenuAdd','MenuController@index');
    $router->post('/menuAddDo','MenuController@menuAddDo');
    $router->resource('/MenuShow',MenuShowController::class);
    //渠道管理 --- 带参数的二维码
    $router->get('/qrCodeAdd','QrCodeController@index');
    $router->post('/qrCodeAddDo','QrCodeController@qrCodeAddDo');
    $router->resource('/qrCodeShow',QrcodeShowController::class);


});
