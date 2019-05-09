<?php

namespace App\Admin\Controllers;

use App\Model\MatersModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MatersShowController extends Controller
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
        $grid = new Grid(new MatersModel);

        $grid->img_id('Img id');
        $grid->img_name('Img name');
        $grid->img_url('Img url')->display(function($img){
            return '<img src="'.$img.'"style="width:150px;height:160px;">';
        });
        $grid->img_media('Img media');
        $grid->img_time('素材添加时间')->display(function($data){
            return date("Y-m-d H:i:s");
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
}
