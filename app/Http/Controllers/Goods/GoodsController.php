<?php

namespace App\Http\Controllers\Goods;

use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoodsController extends Controller
{
    public function goods(){
        $data=GoodsModel::orderBy('goods_time', 'desc')->first();
        if($data){
            $data=GoodsModel::orderBy('goods_time', 'desc')->first()->toArray();
        }else{
            $data=GoodsModel::orderBy('goods_time', 'desc')->first();
        }
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];  //获取域名

        return view('goods.goods',['data'=>$data,'url'=>$url]);
    }
}
