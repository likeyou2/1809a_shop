<?php

namespace App\Admin\Controllers;

use App\Model\AnswerModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class AnswerController extends Controller
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
        $grid = new Grid(new AnswerModel);

        $grid->answer_id('Answer id');
        $grid->answer_name('Answer name');
        $grid->answer_a('Answer a');
        $grid->answer_b('Answer b');
        $grid->correct_answer('Correct answer');
        $grid->time('Time');

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
        $show = new Show(AnswerModel::findOrFail($id));

        $show->answer_id('Answer id');
        $show->answer_name('Answer name');
        $show->answer_a('Answer a');
        $show->answer_b('Answer b');
        $show->correct_answer('Correct answer');
        $show->time('Time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AnswerModel);

        $form->number('answer_id', 'Answer id');
        $form->text('answer_name', 'Answer name');
        $form->text('answer_a', 'Answer a');
        $form->text('answer_b', 'Answer b');
        $form->text('correct_answer', 'Correct answer');
        $form->datetime('time', 'Time')->default(date('Y-m-d H:i:s'));

        return $form;
    }

    public function anAdd(Content $content)
    {
        return $content
            ->header('答题视图展示')
            ->description('description')
            ->body(view('Answer.anAdd'));
    }

    public function anAddDo(Request $request){
        $data = $request->input();
        $res = AnswerModel::insertGetId($data);
        if($res){
            echo "添加成功";
        }else{
            echo '添加失败';
        }
    }

}
