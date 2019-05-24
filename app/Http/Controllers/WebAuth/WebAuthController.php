<?php

namespace App\Http\Controllers\WebAuth;

use App\Model\MaterialModel;
use App\Model\WebUsersModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class WebAuthController extends Controller
{
    public function webAuthAdd(Request $request){
        $data = $request->input();
        $openid = Session::get('openid');
        $data = [
            'name' => $data['name'],
            'pwd' => $data['pwd'],
            'openid' => $openid
        ];
        WebUsersModel::insertGetId($data);
    }
    
    public function webCrontab(){
        $arr = [
            'type'=>'铁碎牙',
            'time'=>time()
        ];
        $res = MaterialModel::insertGetId($arr);
        var_dump($res);
    }

    //授权登录
    public function webAdmin(){
        return view('web.Admin');
    }

    public function webAdminAdd(Request $request){
        $data = $request->input();
        $webUser = WebUsersModel::where(['name'=>$data['name']])->first()->toArray();
        $code = rand(10000,99999);
        $access = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access;
        $post_data = [
            "touser"=>$webUser['openid'],
            "template_id"=>"mixmy-nmJ4krmgELEa4PbZBGYSr5aaN3T8bd3wbyvAI",
            "url"=>"http://www.1809a.com/webAdmin",
            'data' =>[
                'name'=>[
                    "value"=>$code,
                    "color"=>"#173177"
                ]
            ]
        ];
        $post_data = json_encode($post_data);
        $res = $this->curlPost($url,$post_data);
        if($res){
            Session::put('code',$code);
            echo "发送成功";
        }else{
            echo "发送失败";
        }

    }

    public function webAdminAddDo(Request $request){
        $data = $request->input();
        $code = Session::get('code');
        if($code == $data['auth_code']){
            $userData = WebUsersModel::where(['name'=>$data['name']])->first()->toArray();
            if($data['name'] == $userData['name'] && $data['pwd'] == $userData['pwd'] ){
                echo "登录成功";
            }else{
                echo "用户名密码错误";
            }
        }else{
            echo "验证码错误";
        }
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
}
