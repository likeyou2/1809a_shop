<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayController extends Controller
{
    public $url="https://api.mch.weixin.qq.com/pay/unifiedorder";    // 统一下单接口
    /*
     * 支付
     */
    public function payMent(){
        $moeny = 1;
        $order_id = time().mt_rand(11111,99999);
        $arr = [

        ];

    }
}
