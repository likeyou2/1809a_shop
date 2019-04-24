<?php

namespace App\Http\Controllers\Weixin;

use App\Model\MaterialModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
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


        if($MsgType=="event"){ //判断数据类型
            if($Event=="subscribe"){ //判断事件类型

                $userInfo=$this->userInfo($FromUserName);//获取用户昵称

                $one=UserModel::where(['openid'=>$FromUserName])->first();//查询数据库
                if($one){//判断用户是否是第一次关注
                    $xml="<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>time()</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA[你好,欢迎".$userInfo['nickname']."回归]]></Content>
                    </xml>";//设置发送的xml格式
                    echo $xml;//返回结果
                }else{//如果是第一次关注
                    $array=[
                        "openid"=>$userInfo['openid'],
                        "nickname"=>$userInfo['nickname'],
                        "city"=>$userInfo['city'],
                        "province"=>$userInfo['province'],
                        "country"=>$userInfo['country'],
                        "headimgurl"=>$userInfo['headimgurl'],
                        "subscribe_time"=>$userInfo['subscribe_time'],
                        "sex"=>$userInfo['sex'],
                    ];//设置数组形式的数据类型
                    $res=UserModel::insertGetId($array);//存入数据库
                    if($res){//判断是否入库成功
                        $xml="<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>time()</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA[你好,欢迎".$userInfo['nickname']."]]></Content>
                    </xml>";//设置xml格式的数据
                        echo $xml;//返回结果
                    }
                }
            }
        }else if ($MsgType == 'text') {//用户回复文字消息
            $Content = $objxml->Content;//获取文字内容

            if(strpos($Content,"+天气")){//回复天气
                $city=mb_substr($Content,0,2);//截取城市名称
                $url="https://free-api.heweather.net/s6/weather/now?key=HE1904161039381186&location=$city";//调接口
                $json=file_get_contents($url);//获取数据
                $arr=json_decode($json,true);//变化数组形式数据
                $status=$arr['HeWeather6'][0]['status'];//判断城市是否正确的条件
                if($status=="ok"){//城市正确返回天气情况
                    $fl = $arr['HeWeather6'][0]['now']['fl'];//温度
                    $admin_area = $arr['HeWeather6'][0]['basic']['admin_area'];//城市
                    $wind_dir = $arr['HeWeather6'][0]['now']['wind_dir'];//风力
                    $cond_txt = $arr['HeWeather6'][0]['now']['cond_txt'];//天气情况
                    $str = "温度：".$fl."\n"."风力：".$wind_dir."\n"."所在城市:".$admin_area."\n"."天气实时情况:".$cond_txt;
                    $xml = "<xml>
                <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                <CreateTime>time()</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[$str]]></Content>
            </xml>";//返回xml格式数据
                    
                    echo $xml;//回复给用户
                }else{//城市错误提示
                    $xml = "<xml>
                <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                <CreateTime>time()</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[原谅不能为阁下找到城市]]></Content>
            </xml>";//返回xml格式数据
                    echo $xml;//回复给用户
                }


            }else {//消息入库
                $arr = [
                    "type" => $Content,//用户发送的消息内容
                    "FromUserName" => $FromUserName,//用户的id
                    "time" => time()//入库的时间
                ];//存成数组格式，等待入库
                $res = MaterialModel::insert($arr);//存入数据库
                if ($res) {//成功返回给用户结果
                    $xml = "<xml>
                <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                <CreateTime>time()</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[已收到]]></Content>
            </xml>";//返回xml格式数据
                    echo $xml;//回复给用户
                }
            }

        } else if ($MsgType == "image") {//图片存项目，存库
            $media_id = $objxml->MediaId;//获取图片传输的间名意
            $access = $this->getAccessToken();//获取access_token
            $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$media_id";//接口

            $client = new Client();//实例化Guzzle
            $response = $client->get($url);//调用方法
            $headers = $response->getHeaders();//获取响应头
            $file_info = $headers['Content-disposition'][0];//获取图片名
            $file_name = rtrim(substr($file_info, -20), '"');//取文件名后20位
            $img_name = 'weixin/img/' . substr(md5(time() . mt_rand()), 10, 8) . '_' . $file_name;//最后的文件名;
            $res = Storage::put($img_name, $response->getBody());//使用Storage把图片存入laravel框架中
            if ($res) {
                $arr = [
                    "type" => "storage/app/" . $file_name,//图片的路径
                    "FromUserName" => $FromUserName,//用户的id
                    "time" => time()//添加的时间
                ];
                $res = MaterialModel::insert($arr);//存入数据库
                if ($res) {
                    $xml = "<xml>
                <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                <CreateTime>time()</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[图片很完美]]></Content>
            </xml>";//返回xml格式数据
                    echo $xml;//回复给用户
                }
            }
        } else if ($MsgType == "voice") {
            $media_id = $objxml->MediaId;//语音的间名意
            $access = $this->getAccessToken();//获取access_token
            $url = "https://api.weixin.qq.com/cgi-bin/media/get/jssdk?access_token=$access&media_id=$media_id";//调接口
            $mp3 = file_get_contents($url);//存入
            $file_name = time() . mt_rand(11111, 99999) . ".amr";//语音文件名
            $res = file_put_contents('weixin/voice/' . $file_name, $mp3);//存入框架
            if ($res) {
                $arr = [
                    "type" => "public/weixin/voice" . $file_name,//语音文件路径
                    "FromUserName" => $FromUserName,//用户的id
                    "time" => time()//添加的时间
                ];
                $res = MaterialModel::insert($arr);//存入数据库
                if ($res) {
                    $xml = "<xml>
                <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                <CreateTime>time()</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[您的声音真好听]]></Content>
            </xml>";//返回xml格式数据
                    echo $xml;//回复给用户
                }
            }
        }
        echo "SUCCESS";
}
	
	//获取用户的基本信息
	public function userInfo($FromUserName){
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

    /**
     * 创建自定义菜单
     */
    public function CustomMenu(){
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
        $client = new Client(['base_uri' => $url]);
        $data = [
            "button"    => [
                [
                    "name" => "相册拍照",
                    "sub_button" => [
                        [
                            "type"  => "pic_sysphoto",      // view类型 跳转指定 URL
                            "name"  => "系统拍照发图",
                            "key"   => "rselfmenu_1_0",
                            "sub_button"=> [ ]
                        ],
                        [
                            "type" =>  "pic_photo_or_album",
                            "name" => "拍照或者相册发图",
                            "key" => "rselfmenu_1_1",
                            "sub_button" => [ ]
                        ],
                        [
                            "type" => "pic_weixin",
                            "name" => "微信相册发图",
                            "key" => "rselfmenu_1_2",
                            "sub_button" => [ ]
                        ]
                    ]
                ],
                [
                    "name" => "点击跳转",
                    "sub_button" => [
                        [
                            "name" => "发送位置",
                            "type" => "location_select",
                            "key" => "rselfmenu_2_0"
                        ],
                        [
                            "name" => "哔哩哔哩",
                            'type' => "view",
                            "url" => "https://www.bilibili.com/"
                        ]
                    ]
                ],
                [
                    "name" => "扫码功能",
                    "sub_button" => [
                        [
                            "name" => "扫码带提示",
                            "type" => "scancode_waitmsg",
                            "key" => "rselfmenu_0_0",
                            "sub_button"=>[
                                'text'=>"text"
                            ]
                        ],
                        [
                            "name" => "扫码推事件",
                            "type" => "scancode_push",
                            "key" => "rselfmenu_0_1",
                            "sub_button"=>[]
                        ],
                    ]
                ]
            ]
        ];
        $r = $client->request('POST', $url, [
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        // 3 解析微信接口返回信息
        $response_arr = json_decode($r->getBody(),true);
        if($response_arr['errcode'] == 0){
            echo "菜单创建成功";
        }else{
            echo "菜单创建失败，请重试";echo '</br>';
            echo $response_arr['errmsg'];
        }
    }
}
