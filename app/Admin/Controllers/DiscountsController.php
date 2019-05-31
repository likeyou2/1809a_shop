<?php

namespace App\Admin\Controllers;

use App\Model\DiscountsModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class DiscountsController extends Controller
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
        $grid = new Grid(new DiscountsModel);

        $grid->discounts_id('Discounts id');
        $grid->discounts_name('Discounts name');
        $grid->discounts_number('Discounts number');
        $grid->discounts_if('Discounts if');
        $grid->discounts_money('Discounts money');
        $grid->discounts_time('Discounts time');
        $grid->discounts_outtime('Discounts outtime');
        $grid->discounts_status('Discounts status');

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
        $show = new Show(DiscountsModel::findOrFail($id));

        $show->discounts_id('Discounts id');
        $show->discounts_name('Discounts name');
        $show->discounts_number('Discounts number');
        $show->discounts_if('Discounts if');
        $show->discounts_money('Discounts money');
        $show->discounts_time('Discounts time');
        $show->discounts_outtime('Discounts outtime');
        $show->discounts_status('Discounts status');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DiscountsModel);

        $form->number('discounts_id', 'Discounts id');
        $form->text('discounts_name', 'Discounts name');
        $form->number('discounts_number', 'Discounts number');
        $form->text('discounts_if', 'Discounts if');
        $form->text('discounts_money', 'Discounts money');
        $form->number('discounts_time', 'Discounts time');
        $form->number('discounts_outtime', 'Discounts outtime');
        $form->number('discounts_status', 'Discounts status');

        return $form;
    }

    public function discountsShow(Content $content)
    {
        return $content
            ->header('优惠卷添加')
            ->description('description')
            ->body(view('discounts.discountsShow'));
    }
    public function discountsAdd(Request $request){
        $discountsData = $request->input();
        $res2 = DiscountsModel::where('discounts_name',$discountsData['discounts_name'])->first();
        if($res2){
            echo "您好一天最多添加一个优惠券活动";die;
        }
        $discountsData = [
            'discounts_name' => $discountsData['discounts_name'],
            'discounts_number' => $discountsData['discounts_number'],
            'discounts_if' => $discountsData['discounts_if'],
            'discounts_money' => $discountsData['discounts_money'],
            'discounts_outtime' => strtotime($discountsData['discounts_outtime']),
            'discounts_status' => 1,
            'discounts_time' => time()
        ];
        $res = DiscountsModel::insertGetId($discountsData);
        if($res){
            echo '添加成功';
        }else{
            echo '添加失败';
        }
    }
}
