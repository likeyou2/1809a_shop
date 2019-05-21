<?php

namespace App\Admin\Controllers;

use App\Model\LabelModel;
use App\Http\Controllers\Controller;
use App\Model\LOModel;
use App\Model\UserModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class LabelController extends Controller
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
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content )
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
        $grid = new Grid(new LabelModel);

        $grid->id('Id');
        $grid->label_id('Label id');

        $grid->label_name('Label name');
        $grid->label_count('Label count');
        $grid->label_status('Label status');
        $grid->label_time('Label time')->display(function($data){
            return date('Y-m-d H:i:s');
        });

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
        $data = UserModel::get();
        $labelData = LabelModel::where('id',$id)->first();
        if($labelData){
            $labelData = LabelModel::where('id',$id)->first()->toArray();
        }
        return view('openid.show',['data'=>$data,'labelData'=>$labelData]);

        /*$show = new Show(LabelModel::findOrFail($id));
        $show->id('Id');
        $show->label_id('Label id');

        $show->label_name('Label name');
        $show->label_count('Label count');
        $show->label_status('Label status');
        $show->label_time('Label time');

        return $show;*/
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LabelModel);

        $form->number('label_id', 'Label id');
        $form->text('label_name', 'Label name');
        $form->text('label_count', 'Label count');
        $form->number('label_status', 'Label status');
        $form->number('label_time', 'Label time');

        return $form;
    }

    //给用户添加标签
    public function openidAdd(Request $request){
        $access = $this->getAccessToken();
        $data = $request->input();
        $arr =[];
        foreach ($data['openid'] as $k=>$v){
            $arr[$k]['openid'] = $v;
            $arr[$k]['label_id'] = $data['labelId'];
        }

        $openid = [];
        foreach ($arr as $key => $value){
            $openid[] = $value['openid'];
            $res = LOModel::insert($value);
        }
        if( $res ){
            $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token='.$access;
            $post_data = [
                'openid_list' => $openid,
                'tagid'=>$data['labelId']
            ];
            $post_data = json_encode($post_data);
            $json = $this->curlPost($url,$post_data);
            $resData = json_decode($json,true);
            if($resData['errmsg'] == "ok" ){
                echo "分配成功";die;
            }else{
                echo "分配失败";die;
            }
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
