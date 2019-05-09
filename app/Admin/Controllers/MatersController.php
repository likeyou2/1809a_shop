<?php

namespace App\Admin\Controllers;

use App\Model\MatersModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Location;

class MatersController extends Controller
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
            ->body($this->materialAdd());
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
        $grid = new Grid(new MatersModel);

        $grid->img_id('Img id');
        $grid->img_name('Img name');
        $grid->img_url('Img url');
        $grid->img_media('Img media');
        $grid->img_time('Img time');

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
        $show = new Show(MatersModel::findOrFail($id));

        $show->img_id('Img id');
        $show->img_name('Img name');
        $show->img_url('Img url');
        $show->img_media('Img media');
        $show->img_time('Img time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MatersModel);

        $form->number('img_id', 'Img id');
        $form->text('img_name', 'Img name');
        $form->text('img_url', 'Img url');
        $form->text('img_media', 'Img media');
        $form->number('img_time', 'Img time');

        return $form;
    }

    public function materialAdd(){
        return view('materialAdd.add');
    }
    public function materialAddDo(Request $request){
        if ($request->isMethod('POST')) { //判断是否是POST上传，应该不会有人用get吧，恩，不会的

            //在源生的php代码中是使用$_FILE来查看上传文件的属性
            //但是在laravel里面有更好的封装好的方法，就是下面这个
            //显示的属性更多
            $fileCharater = $request->file('fileUpload');

            if ($fileCharater->isValid()) { //括号里面的是必须加的哦
                //如果括号里面的不加上的话，下面的方法也无法调用的

                //获取文件的扩展名
                $ext = $fileCharater->getClientOriginalExtension();
                //获取文件的绝对路径
                $path = $fileCharater->getRealPath();

                //定义文件名
                $filename = date('Y-m-d-h-i-s').'.'.$ext;

                //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
                $res=Storage::disk('local')->put($filename, file_get_contents($path));
                if($res){
                    $AccessToken=$this->getAccessToken();
                    $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$AccessToken&type=image";
                    $url2 = "/wwwroot/1809a_shop/public/imgs/$filename";
                    $imgPath = new \CURLFile($url2);
                    //var_dump($imgPath);exit;
                    $data = [
                        'media'=>$imgPath
                    ];
                    $res=$this->curlPost($url,$data);
                    if($res){
                        $arr=json_decode($res,true);
                        $data = [
                            'img_name'=>$filename,
                            'img_url'=>'/imgs/'.$filename,
                            'img_media'=>$arr['media_id'],
                            'img_time'=>time(),
                        ];
                        $res=MatersModel::insertGetId($data);
                        if($res){
                            echo '上传成功';
                            return redirect('admin/materialAdd');
                        }
                    }else{
                        echo '上传失败';
                    }
                }else{
                    echo '上传失败';
                }
            }
        }

    }
    /*
     * 获取AccessToken
     */
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
