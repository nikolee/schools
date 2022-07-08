<?php

namespace App\Admin\Controllers;

use App\Models\Mark;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MarkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Mark';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Mark());
        $grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('student.name', __('Student'))->sortable();
        $grid->column('exam.name', __('Exam'))->sortable();
        $grid->column('class.name', __('Class'))->sortable();
        $grid->column('subject.name', __('Subject'))->sortable();
        $grid->column('score', __('Score'))->sortable()->editable();
        $grid->column('remarks', __('Remarks'))->editable();
        $grid->column('is_missed', __('Missed'));
        $grid->column('is_submitted', __('Submitted'))->display(function ($st) {
            if ($st)
                return '<span class="bagde bagde-success">Submitted</span>';
            else
                return '<span class="bagde bagde-danger">Missing</span>';
        })->sortable();

        $grid->column('teacher.name', __('Teacher'))->sortable()->hide();

        $grid->column('updated_at', __('Updated'))->display(function ($v) {
            return Carbon::parse($v)->format('d-M-Y');
        })->sortable();

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
        $show = new Show(Mark::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('exam_id', __('Exam id'));
        $show->field('class_id', __('Class id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('student_id', __('Student id'));
        $show->field('teacher_id', __('Teacher id'));
        $show->field('score', __('Score'));
        $show->field('remarks', __('Remarks'));
        $show->field('is_submitted', __('Is submitted'));
        $show->field('is_missed', __('Is missed'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Mark());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('exam_id', __('Exam id'));
        $form->number('class_id', __('Class id'));
        $form->number('subject_id', __('Subject id'));
        $form->number('student_id', __('Student id'));
        $form->number('teacher_id', __('Teacher id'));
        $form->decimal('score', __('Score'))->default(0.00);
        $form->textarea('remarks', __('Remarks'));
        $form->switch('is_submitted', __('Is submitted'));
        $form->switch('is_missed', __('Is missed'))->default(1);

        return $form;
    }
}
