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

	public function wxEvent(){
		//接受微信服务器推送
		$content = file_get_contents("php://input");

		$time = date("Y-m-d H:i:s") . $content . "\n";

		file_put_contents('logs/wx_event.log',$time,FILE_APPEND);

		$objxml = simplexml_load_string($content);  //吧XML格式转为成对象格式
		$ToUserName=$objxml->ToUserName;     //开发者微信号
		$FromUserName=$objxml->FromUserName;   //用户的微信号
		$CreateTime=$objxml->CreateTime;     //时间
		$MsgType=$objxml->MsgType;        //消息类型
		$Event=$objxml->Event;          //事件
		//调用用户的基本信息方法  返回用户信息
		$info=$this->userinfo($FromUserName);


		if($MsgType == 'event'){
				if($Event == 'subscribe'){
						$xml="<xml>
  <ToUserName><![CDATA[$FromUserName]]></ToUserName>
  <FromUserName><![CDATA[$ToUserName]]></FromUserName>
  <CreateTime>".time()."</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Event><![CDATA[欢迎,$info['nickname']关注]]></Event>
</xml>";

				}
		}
	}
	
	//获取用户的基本信息
	public function userinfo($FromUserName){
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$FromUserName.'&lang=zh_CN';
		$count = file_get_contents($url); //调用URL接口
		$info = json_decode($count,true); //XML格式转换成数组
		return $info;
	}
	



	//获取Access_token
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
