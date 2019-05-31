<?php

namespace App\Http\Controllers\Weixin;

use App\Model\AchievementModel;
use App\Model\AnswerModel;
use App\Model\IncidentModel;
use App\Model\JudgeModel;
use App\Model\LoveModel;
use App\Model\MaterialModel;
use App\Model\MatersModel;
use App\Model\QrCodeModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

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
        $FromUserName=$objxml->FromUserName;   //用户opendid
        $CreateTime=$objxml->CreateTime;     //时间
        $MsgType=$objxml->MsgType;        //消息类型
        $Event=$objxml->Event;          //事件


        if($MsgType=="event" && $Event=="subscribe"){ //判断数据类型
            $userInfo=$this->userInfo($FromUserName);//获取用户昵称
            if($objxml->EventKey == ""){
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
            }else{



                $EventKey = substr($objxml->EventKey,8);
                $one=UserModel::where(['openid'=>$FromUserName])->first();//查询数据库
                if($one){
                    $res = UserModel::where('openid',$FromUserName)->update(["qrcode_cation" => $EventKey]);
                    if($res){
                        echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[你好,欢迎".$userInfo['nickname']."回归]]></Content>
                        </xml>";
                    }
                }else{
                    $array=[
                        "openid" => $userInfo['openid'],
                        "nickname" => $userInfo['nickname'],
                        "city" => $userInfo['city'],
                        "province" => $userInfo['province'],
                        "country" => $userInfo['country'],
                        "headimgurl" => $userInfo['headimgurl'],
                        "subscribe_time" => $userInfo['subscribe_time'],
                        "sex" => $userInfo['sex'],
                        "qrcode_cation" => $EventKey
                    ];//设置数组形式的数据类型
                    $res = UserModel::insertGetId($array);//存入数据库
                    if( $res ){
                        echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[你好,欢迎".$userInfo['nickname']."]]></Content>
                        </xml>";
                    }
                }
                QrCodeModel::where(['qrcode_cation'=>$EventKey])->increment('qrcode_number');
            }

        }else if ($MsgType == 'text') {//用户回复文字消息
            $Content = $objxml->Content;//获取文字内容
            $array = IncidentModel::where('openid',$FromUserName)->orderBy('time','desc')->first()->toArray();
            $judgeArray = JudgeModel::where('openid',$FromUserName)->orderBy('time','desc')->first()->toArray();
            if(time() - $array['time'] < 100 && $array['content'] == "请输入对方姓名"){
                $data = [
                    'openid' => $FromUserName,
                    'name' => $Content
                ];
                $res1 = LoveModel::insertGetId($data);
                //var_dump($res1);exit;
                if($res1){
                    echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[请输入要表白的内容]]></Content>
                        </xml>";
                    $data2 = [
                        'openid' => $FromUserName,
                        'content' => '请输入要表白的内容',
                        'time' => time()
                    ];
                    IncidentModel::insertGetId($data2);
                }
            }else if(time() - $array['time'] < 200 && $array['content'] == "请输入要表白的内容"){
                $num = 0;
                $num ++;
                $data2 = [
                    'content' => $Content,
                    'count' =>$num,
                    'time' => time()
                ];
                $loveArr = LoveModel::where('openid',$FromUserName)->orderBy('id','desc')->first()->toArray();
                $res = LoveModel::where('openid',$FromUserName)->where('id',$loveArr['id'])->update($data2);
                if($res){
                    echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[表白成功]]></Content>
                        </xml>";
                }
            }else if(time() - $array['time'] < 200 && $array['content'] == "请输入要查询名字"){
                $name = LoveModel::where('name',$Content)->first();
                $count=LoveModel::where(['name'=>$Content])->get()->count();//被表白了多少次
                if($name){
                    $text = "被表白人:".$name['name']."\n表白的次数:".$count."\n表白内容:".$name['content'];
                    echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[$text]]></Content>
                        </xml>";
                }else{
                    $text = "没人表白";
                    echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[$text]]></Content>
                        </xml>";
                }

            }else if(time() - $judgeArray['time'] < 200 && trim($array['content']) == trim($judgeArray['judge_name'])){
                if($Content == $judgeArray['judge_answer']){
                    $achievement = [
                        'openid' => $FromUserName,
                    ];
                    $achRes = AchievementModel::insertGetId($achievement);
                    if($achRes){
                        AchievementModel::increment('achievement_right');
                        echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[恭喜您回答正确]]></Content>
                        </xml>";die;
                    }
                }else{
                    AchievementModel::increment('achievement_mistak');
                    echo "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[抱歉您回答错误]]></Content>
                        </xml>";die;
                }
            }
            if(strstr($Content,'天气')){//回复天气
                $city=mb_substr($Content,0,-2);
                if($city == "天气"){
                    $city = 1;
                }else{
                    $city = $city;
                }
                $url = "http://api.k780.com/?app=weather.future&weaid=$city&&appkey=42242&sign=03ca6c0ebe8fccb9c7b9bea1213ea2b6&format=json";
                $json = file_get_contents($url);
                $data = json_decode($json,true);
                $str="";
                if($data['success'] == '1'){
                    foreach ($data['result'] as $k=>$v){
                        $str .= $v['days']." ".$v['week']." ".$v['citynm']." ".$v['weather']."\n";
                    }
                    $xml = "<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>time()</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA[$str]]></Content>
                    </xml>";
                    echo $xml;
                }

            }else if($Content =="1"){
                $xml = "<xml>
                          <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                          <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                          <CreateTime>time()</CreateTime>
                          <MsgType><![CDATA[text]]></MsgType>
                          <Content><![CDATA[2]]></Content>
                    </xml>";
                echo $xml;
            }else if($Content == "时间"){
                $date = date('Y-m-d H:i:s');
                echo $xml = "<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>time()</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA[$date]]></Content>
                    </xml>";
            }else {//消息入库
                $arr = [
                    "type" => $Content,//用户发送的消息内容
                    "FromUserName" => $FromUserName,//用户的id
                    "time" => time()//入库的时间
                ];//存成数组格式，等待入库
                $res = MaterialModel::insert($arr);//存入数据库
                /*if ($res) {//成功返回给用户结果
                    $xml = "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>time()</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[已收到]]></Content>
                        </xml>";//返回xml格式数据
                    echo $xml;//回复给用户
                }*/
                $url="http://www.tuling123.com/openapi/api?key=16388ae7824c47df833db939422ebd06&info=$Content";
                $json = file_get_contents($url);
                $data = json_decode($json , true);
                $msg = $data['text'];
                echo "<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>12345678</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA[$msg]]></Content>
                    </xml>";
            }

        }else if($MsgType == "event" && $Event == "unsubscribe"){  //取消关注
            $EventKey = UserModel::where(['openid'=>$FromUserName])->first()->toArray();
            QrCodeModel::where(['qrcode_cation'=>$EventKey['qrcode_cation']])->decrement('qrcode_number');
        }else if($MsgType == "image"){
            $data=MatersModel::orderByRaw("RAND()")->first();//随机查询一条数据
            $media_id=$data->img_media;
            if($media_id) {
                $xml = "<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>" . time() . "</CreateTime>
                            <MsgType><![CDATA[image]]></MsgType>
                            <Image>
                            <MediaId><![CDATA[$media_id]]></MediaId>
                            </Image>
                        </xml>";
                echo $xml;
            }
        }else if($MsgType == "event" && $Event == "CLICK"){
            $EventKey=$objxml->EventKey;
            if($EventKey == "sendWhite"){

                echo "<xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>time()</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[请输入对方姓名]]></Content>
                    </xml>";
                $data = [
                    'openid' => $FromUserName,
                    'content' => '请输入对方姓名',
                    'time' => time()
                ];
                $res = IncidentModel::insertGetId($data);



            }else if ($EventKey == "selectWhite"){

                echo "<xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>time()</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[请输入要查询名字]]></Content>
                    </xml>";
                $data = [
                    'openid' => $FromUserName,
                    'content' => '请输入要查询名字',
                    'time' => time()
                ];
                $res = IncidentModel::insertGetId($data);


            }else if($EventKey == "Answer"){   //发送题目
                $data = AnswerModel::orderByRaw("RAND()")->first()->toArray();
                $dataDB = [   //问题存入库中方便比对
                    'answer_id' => $data['answer_id'],
                    'openid' => $FromUserName,
                    'judge_name' =>$data['answer_name'],
                    'judge_answer' => $data['correct_answer'],
                    'time' => time()
                ];
                $incidentDB = [    //记录上一步动作
                    'openid' => $FromUserName,
                    'content' => $data['answer_name'],
                    'time' => time()
                ];
                IncidentModel::insertGetId($incidentDB);
                $res = JudgeModel::insertGetId($dataDB);
                if($res) {
                    $img = '题目:'.$data['answer_name']."\n".'选: A:'.$data['answer_a'].'  B:'.$data['answer_b'];
                    echo "<xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>time()</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[$img]]></Content>
                    </xml>";
                }
            }else if($EventKey == "Achievement"){   //我的答题成绩
                $achievementData=AchievementModel::where('openid',$FromUserName)->first()->toArray();
                $user = $this->userInfo($FromUserName);
                $img = '您好:'.$user['nickname'].'; 您共答对:'.$achievementData['achievement_right'].'道题, 答错:'.$achievementData['achievement_mistake'].'道题';
                echo "<xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>time()</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[$img]]></Content>
                    </xml>";
            }
        }

    }

    //自定义菜单
    public function CustomMenu(){
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
        $arr = [
            "button" => [
                 [
                    "name"=>"表白",
                    "sub_button"=>[
                        [
                            "type"=>"click",
                            "name"=>"♥查表白♥",
                            "key"=>"selectWhite"
                        ],
                        [
                            "type"=>"click",
                            "name"=>"♥发表白♥",
                            "key"=>"sendWhite"
                        ],
                    ],
                 ],
                [
                    "name"=>"优惠卷",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"领取优惠卷",
                            "url"=>"http://1809a.ytw00.cn/discounts"
                        ]
                    ]
                ],
                [
                    "name"=>"答题",
                    "sub_button"=>[
                        [
                            "type"=>"click",
                            "name"=>"点击答题",
                            "key"=>"Answer"
                        ],
                        [
                            "type"=>"click",
                            "name"=>"我的成绩",
                            "key"=>"Achievement"
                        ]
                    ]
                ]
            ],
        ];
        $data = json_encode($arr,JSON_UNESCAPED_UNICODE);
        //var_dump($data);die;
        $res = $this->curlPost($url,$data);
        var_dump($res);
    }




    //获取用户的基本信息
	public function userInfo($FromUserName){
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$FromUserName.'&lang=zh_CN';
		$count = file_get_contents($url); //调用URL接口
		$info = json_decode($count,true); //XML格式转换成数组
		return $info;
	}

	//临时素材
	public function material(){
	    $access=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access&type=image";

        $url2 = "/wwwroot/1809a_shop/public/img/20190222085424.jpg";
        $imgPath = new \CURLFile($url2);
        $data = [
            'media'=>$imgPath
        ];
        $res=$this->curlPost($url,$data);
        var_dump($res);exit;
    }

    //微信网页授权
    public function webAuth(){
        $value = Session::get('openid');

	    if (!empty($value)){
            return view('web.webLogin');
        }else{
            $webUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            $jump = urlencode($webUrl.'/webAuthDo');
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('WX_APPID').'&redirect_uri='.$jump.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
            header("location:".$url);
        }
    }

    public function webAuthDo(Request $request){
        $code = $request->input('code');
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $code_access = file_get_contents($url);
        $code_access = json_decode($code_access,true);
        $url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$code_access['access_token'].'&openid='.$code_access['openid'].'&lang=zh_CN';
        $userInfo = file_get_contents($url2);
        Session::put('openid', $code_access['openid']);
        return redirect('/webAuth');
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
            Redis::expire($key,7000); //缓存一小时
            $token = $arr['access_token'];
        }
        return $token;
	}

    //生成参数二维码
    public function ticket(){
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        $arr = [
            'expire_seconds' => 604800,
            'action_name' => 'QR_SCENE',
            'action_info' => [
                'scene' =>[
                    'scene_id' => 123,
                ]
            ]
        ];
        $arr = json_encode($arr , JSON_UNESCAPED_UNICODE);
        $client = new Client();
        $response = $client->request("POST",$url,[
            'body' => $arr
        ]);
        $res = $response->getBody();
        $res = json_decode($res, true);
        $url2 = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$res['ticket'];
        header("Location: $url2");
    }



    public function curlPost($url,$post_data)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL,$url);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    //优惠卷  获取用户Openid
    public function discounts(){
        $value = Session::get('code_openid');

        if (!empty($value)){
            return view('discounts.discountsAward');
        }else{
            $webUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            $jump = urlencode($webUrl.'/discountsDo');
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('WX_APPID').'&redirect_uri='.$jump.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
            header("location:".$url);
        }


    }

    public function discountsDo(Request $request){
	    $code = $request->input('code');
	    $url_access_token = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $code_access = file_get_contents($url_access_token);
        $code_access = json_decode($code_access,true);
        Session::put('code_openid', $code_access['openid']);
    }
}
