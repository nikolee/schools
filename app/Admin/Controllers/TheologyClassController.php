<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\TheologyClass;
use App\Models\TheologyCourse;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TheologyClassController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology class';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TheologyClass());

        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();

        $grid->column('id', __('Id'))->sortable();
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('class_teahcer_id', __('Class teahcer id'));
        $grid->column('name', __('Name'))->sortable();
        $grid->column('short_name', __('Short name'));
        $grid->column('details', __('Details'));

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
        $show = new Show(TheologyClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('class_teahcer_id', __('Class teahcer id'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TheologyClass());

        $form->hidden('enterprise_id', __('Enterprise id'))->default(Admin::user()->enterprise_id)->rules('required');

        $form->tab('Basic info', function (Form $form) {
            $teachers = [];
            foreach (Administrator::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'employee',
            ])->get() as $key => $a) {
                if ($a->isRole('teacher')) {
                    $teachers[$a['id']] = $a['name'] . " #" . $a['id'];
                }
            }
            $form->select('academic_year_id', 'Academic year')
                ->options(
                    AcademicYear::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                        'is_active' => 1,
                    ])->get()
                        ->pluck('name', 'id')
                )->rules('required');

            $form->select('class_teahcer_id', 'Class teahcer')
                ->options(
                    $teachers
                )->rules('required');
            $form->text('name', __('Class Name'))->rules('required');
            $form->text('short_name', __('Class Name Short name'))->rules('required');
            $form->textarea('details', __('Details'));
        });



        $form->html('Click on new to add a subject to this class');
        $form->tab('Class Subjects', function (Form $form) {
            $form->morphMany('subjects', null, function (Form\NestedForm $form) {
                $u = Admin::user();

                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $u = Admin::user();
                $ent = Utils::ent();
                $teachers = [];
                foreach (Administrator::where([
                    'enterprise_id' => $u->enterprise_id,
                    'user_type' => 'employee',
                ])->get() as $key => $a) {
                    if ($a->isRole('teacher')) {
                        $teachers[$a['id']] = $a['name'] . " #" . $a['id'];
                    }
                }
                $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');



                $subjects = [];
                foreach (TheologyCourse::all() as $key => $c) {
                    $subjects[$c->id] =   $c->name . " - " . $c->short_name;
                }


                $form->select('theology_class_id', 'Subject')
                    ->options(
                        $subjects
                    )->rules('required');

                $form->select('subject_teacher', 'Subject teacher')
                    ->options(
                        $teachers
                    )->rules('required');
                $form->select('teacher_1', 'Subject teacher 2')
                    ->options(
                        $teachers
                    );
                $form->select('teacher_2', 'Subject teacher 3')
                    ->options(
                        $teachers
                    );
                $form->select('teacher_3', 'Subject teacher 4')
                    ->options(
                        $teachers
                    );

                $form->text('details', __('Details'));
            });
        });



        return $form;
    }
}
