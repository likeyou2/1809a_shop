<?php

namespace App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WeixinController extends Controller
{
	//
	public function valid(){
		echo $_GET['echostr'];
	}

	public function weEvent(){
		//接受微信服务器推送
		$content = file_get_contents("php://input");

		$time = $time . $content . "\n";

		file_put_contents('logs/wx_event.log',$str,FILE_APPEND);

		echo 'SUCCESS';
	}
}
