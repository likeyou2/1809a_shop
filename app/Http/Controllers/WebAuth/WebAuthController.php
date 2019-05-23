<?php

namespace App\Http\Controllers\WebAuth;

use App\Model\MaterialModel;
use App\Model\WebUsersModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
}
