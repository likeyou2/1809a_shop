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
Route::get('valid','Weixin\WeixinController@valid');

Route::post('valid','Weixin\WeixinController@wxEvent');

//access_token
Route::get('/weixin/access_token','Weixin\WeixinController@getAccessToken');
