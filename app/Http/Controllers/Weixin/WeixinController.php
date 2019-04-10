<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class WeixinController extends Controller
{
	//
	public function valid(){
		echo $_GET['echostr'];
	}

	public function weEvent(){
		//接受微信服务器推送
		$content = file_get_contents("php://input");

		$time = date("Y-m-d H:i:s") . $content . "\n";

		file_put_contents('logs/wx_event.log',$time,FILE_APPEND);

		echo 'SUCCESS';
	}
	public function getAccessToken(){
		//是否有缓存
		$key = 'wx_access_token';
        $token = Redis::get($key);

        if($token){
            return $token;
        }else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET');

            $response = file_get_contents($url);

            $arr = json_decode($response,true);
            //缓存 access_token
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600); //缓存一小时
            $token = $arr['access_token'];
        }
        return $token;
	}
}
