<?php

namespace App\Admin\Controllers;

use App\Model\MenuModel;
use App\Http\Controllers\Controller;
use App\Model\QrCodeModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class QrCodeController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->qrCodeAdd());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MenuModel);

        $grid->menu_id('Menu id');
        $grid->menu_name('Menu name');
        $grid->menu_type('Menu type');
        $grid->menu_key('Menu key');
        $grid->p_id('P id');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MenuModel::findOrFail($id));

        $show->menu_id('Menu id');
        $show->menu_name('Menu name');
        $show->menu_type('Menu type');
        $show->menu_key('Menu key');
        $show->p_id('P id');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MenuModel);

        $form->number('menu_id', 'Menu id');
        $form->text('menu_name', 'Menu name');
        $form->text('menu_type', 'Menu type');
        $form->text('menu_key', 'Menu key');
        $form->number('p_id', 'P id');

        return $form;
    }


    public function qrCodeAdd(){
        return view( 'qrCode.qrCodeAdd' );
    }

    public function qrCodeAddDo(Request $request){
        $data = $request->input();
        $channel_cation = $request->input('channel_cation');
        $access = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access;
        $post_data = [
                "action_name" => $data['channel_type'],
                "action_info" => [
                        "scene" => [
                                "scene_id => $channel_cation"
                            ]
                    ]
        ];
        $post_data = json_encode($post_data);
        $res = $this->curlPost($url,$post_data);
        $res = json_decode($res,true);
        var_dump($res);die;
        $url2 = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$res['ticket'];
        $filename = '/qrcode/'.date('Y-m-d-h-i-s').".jpg";
        copy($url2,"imgs$filename");
        $arr = [
            'qrcode_name' => $data['channel_name'],
            'qrcode_cation' => $channel_cation,
            'qrcode_url' =>$filename,
            'qrcode_status' => 1
        ];
        $res2 = QrCodeModel::insertGetId($arr);
        if($res2){
            echo "生成成功";
        }else{
            echo "生成失败";
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
