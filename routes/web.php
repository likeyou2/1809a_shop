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
//微信配置
Route::any('valid','Weixin\WeixinController@valid');

Route::any('valid','Weixin\WeixinController@wxEvent');

//access_token
Route::get('/weixin/access_token','Weixin\WeixinController@getAccessToken');

//获取用户信息
Route::get('userInfo','Weixin\WeixinController@userinfo');
//测试计划任务
Route::any('/webCrontab','WebAuth\WebAuthController@webCrontab');


//微信自定义菜单
Route::get("menu","Weixin\WeixinController@CustomMenu");

//商品展示页面
Route::get("/goods","Goods\GoodsController@goods");
//生成临时参数二维码
Route::get('/ticket','Weixin\WeixinController@ticket');
//临时素材
Route::get('/material','Weixin\WeixinController@material');
//微信网络授权
Route::get('/webAuth','Weixin\WeixinController@webAuth');
Route::get('/webAuthDo','Weixin\WeixinController@webAuthDo');
//授权执行添加
Route::any('/webAuthAdd','WebAuth\WebAuthController@webAuthAdd');
//访admin登录
Route::any('/webAdmin','WebAuth\WebAuthController@webAdmin');
//验证码发送模板消息
Route::any('/webAdminAdd','WebAuth\WebAuthController@webAdminAdd');
Route::any('/webAdminAddDo','WebAuth\WebAuthController@webAdminAddDo');
//测试接口
Route::any('/na','WebAuth\WebAuthController@na');
//JsSdk测试
Route::get('/jsSdk','JsSdk\JsSdkController@jsSdK');
//jsapi_ticket
Route::get('/getTicket','JsSdk\JsSdkController@getTicket');
//优惠卷 抽奖视图
Route::get('/discounts','Weixin\WeixinController@discounts');
Route::get('/discountsDo','Weixin\WeixinController@discountsDo');
//点击抽奖
Route::post('/discountsAward','Weixin\WeixinController@discountsAward');


