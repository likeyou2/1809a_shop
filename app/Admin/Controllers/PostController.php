<?php

namespace App\Admin\Controllers;

use App\Model\LabelModel;
use App\Model\UserModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
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
    //标签添加视图
    public function labelAdd(Content $content)
    {
        return $content
            ->header('标签添加视图')
            ->description('description')
            ->body(view( 'label.labelAdd' ));
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
        $grid = new Grid(new UserModel);

        $grid->id('Id');
        $grid->openid('Openid');
        $grid->nickname('Nickname');
        $grid->city('City');
        $grid->province('Province');
        $grid->country('Country');
        $grid->headimgurl('Headimgurl')->display(function($img){
            return '<img src="'.$img.'">';
        });
        $grid->subscribe_time('Subscribe time');
        $grid->sex('Sex');

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
        $show = new Show(UserModel::findOrFail($id));

        $show->id('Id');
        $show->openid('Openid');
        $show->nickname('Nickname');
        $show->city('City');
        $show->province('Province');
        $show->country('Country');
        $show->headimgurl('Headimgurl');
        $show->subscribe_time('Subscribe time');
        $show->sex('Sex');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserModel);

        $form->text('openid', 'Openid');
        $form->text('nickname', 'Nickname');
        $form->text('city', 'City');
        $form->text('province', 'Province');
        $form->text('country', 'Country');
        $form->text('headimgurl', 'Headimgurl');
        $form->number('subscribe_time', 'Subscribe time');
        $form->number('sex', 'Sex');

        return $form;
    }

    //执行标签添加
    public function labelAddDo(Request $request){
        $data = $request->input('_name');
        $access = $this->getAccessToken();
        $data =[
            'tag'=>[
                'name'=>$data
            ]
        ];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/create?access_token='.$access;
        $res = $this->curlPost($url,$data);
        $res = json_decode($res,true);
        $arr = [
            'label_id' => $res['tag']['id'],
            'label_name' => $res['tag']['name'],
            'label_time' => time()
        ];
        $res = LabelModel::insertGetId($arr);
        if($res){
            echo "添加成功";exit;
        }else{
            echo "添加失败";exit;
        }
    }


    //标签删除
    public function labelDelete(){
        $access = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/delete?access_token='.$access;
        $data = [
            'tag' => [
                'id' => 108
            ]
        ];
        $data = json_encode($data);
        $res = $this->curlPost($url,$data);
        var_dump($res);
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
