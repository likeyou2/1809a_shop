<?php

namespace App\Http\Controllers\WebAuth;

use App\Model\MaterialModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebAuthController extends Controller
{
    public function webLogin(){
        return view('web.webLogin');
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
