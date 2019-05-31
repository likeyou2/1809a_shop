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
    //标签添加
    $router->get('/labelAdd','PostController@labelAdd');
    $router->post('/labelAddDo','PostController@labelAddDo');
    //标签展示
    $router->resource('/labelShow',LabelController::class);
    //给用户添加标签
    $router->post('/openidAdd','LabelController@openidAdd');
    //标签删除
    $router->any('/labelDelete','PostController@labelDelete');
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
    $router->get('/qrCodeChartShow','QrcodeShowController@qrCodeChartShow');
    $router->resource('/qrCodeShow',QrcodeShowController::class);
    //答题展示
    $router->get('/anAdd','AnswerController@anAdd');
    $router->post('/anAddDo','AnswerController@anAddDo');
    //优惠卷
    $router->get('/discounts','DiscountsController@discountsShow');
    $router->post('/discountsAdd','DiscountsController@discountsAdd');

});
