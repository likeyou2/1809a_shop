<?php

namespace App\Admin\Controllers;

use App\Model\MenuModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class MenuController extends Controller
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
            ->body($this->MenuAdd());
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


    public function MenuAdd(){
        $data = MenuModel::where(['p_id'=>0])->get()->toArray();
        return view('Menu.MenuAdd' , ['data'=>$data]);
    }
    public function menuAddDo(Request $request){
        $data = $request->input();
//        dump($data);die;
        $array = MenuModel::where(['p_id'=>0])->get()->count();
//        var_dump($array);die;
        if($array > 4 ){
            echo '您好一级菜单最多3个';exit;
        }
        $res = MenuModel::insertGetId($data);
        if($res){
            $access = $this->getAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access;
            $typeArr = ['click'=>'key','view'=>'url']; //菜单类型
            $arr = MenuModel::where('p_id',0)->get()->toArray();
            $menu_data = [];
            foreach($arr as $k=>$v){
                if(empty($v['menu_type'])){
                    $menu_data['button'][$k]['name'] = $v['menu_name'];
                    //通过一级菜单查出二级
                    $childData = MenuModel::where(['p_id'=>$v['menu_id']])->get()->toArray();
                    foreach ($childData as $key => $value) {
                        $menu_data['button'][$k]['sub_button'][] = [
                            'type'=> $value['menu_type'],
                            'name'=> $value['menu_name'],
                            $typeArr[$value['menu_type']] => $value['menu_key']
                        ];
                    }
                }else{
                    $menu_data['button'][] = [
                        'type'=> $v['menu_type'],
                        'name'=> $v['menu_name'],
                        $typeArr[$v['menu_type']] => $v['menu_key']
                    ];
                }
            }

            //把数组转成json结果
            $post_data = json_encode($menu_data,JSON_UNESCAPED_UNICODE);
//            var_dump($post_data);die;
            //发请求
            $res = $this->curlPost($url,$post_data);
            return $res;die;
        }

    }

    //获取Access_token
    protected function getAccessToken(){
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
