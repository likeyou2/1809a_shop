<?php

namespace App\Admin\Controllers;

use App\Model\QrCodeModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class QrcodeShowController extends Controller
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



    public function qrCodeChartShow(Content $content)
    {
        $data=QrCodeModel::all()->toArray();
        $name='';
        $number='';
        foreach ($data as $key=>$values){
            $name.="'".$values['qrcode_name']."',";
            $number.=$values['qrcode_number'].",";
        }
        $name=rtrim($name,",");
        $number=rtrim($number,",");
        $dataInfo=[
            'name'=>$name,
            'number'=>$number
        ];
        return $content
            ->header('Index')
            ->description('description')
            ->body(view('qrCode.chartShow',['data'=>$dataInfo]));
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
        $grid = new Grid(new QrCodeModel);

        $grid->qrcode_id('Qrcode id');
        $grid->qrcode_name('Qrcode name');
        $grid->qrcode_cation('Qrcode cation');
        $grid->qrcode_number('Qrcode number');
        $grid->qrcode_url('Qrcode url')->view('qrCode.code');
//        $grid->qrcode_status('Qrcode status');

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
        $show = new Show(QrCodeModel::findOrFail($id));

        $show->qrcode_id('Qrcode id');
        $show->qrcode_name('Qrcode name');
        $show->qrcode_cation('Qrcode cation');
        $show->qrcode_number('Qrcode number');
        $show->qrcode_url('Qrcode url');
        $show->qrcode_status('Qrcode status');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new QrCodeModel);

        $form->number('qrcode_id', 'Qrcode id');
        $form->text('qrcode_name', 'Qrcode name');
        $form->text('qrcode_cation', 'Qrcode cation');
        $form->number('qrcode_number', 'Qrcode number');
        $form->text('qrcode_url', 'Qrcode url');
        $form->number('qrcode_status', 'Qrcode status');

        return $form;
    }
}
