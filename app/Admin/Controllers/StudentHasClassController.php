<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\StudentHasClass;
use App\Models\User;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Redirect;

class StudentHasClassController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student\'s class';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

 
        Utils::display_checklist(Utils::students_checklist(Admin::user()));

        $grid = new Grid(new StudentHasClass());
        $grid->disableBatchActions();
        $grid->disableExport();

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $u = Admin::user();
            $filter->equal('academic_class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()->pluck('name_text', 'id'));

            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('administrator_id', 'Student')->select()->ajax($ajax_url);
        });



        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable();

        $grid->column('administrator_id', __('Student'))->display(function () {
            if (!$this->student) {
                return "-";
            }
            return  $this->student->name;
        });

        $grid->column('academic_class_id', __('Class'))->display(function () {
            if (!$this->class) {
                return "-";
            }
            return  $this->class->name_text;
        });
        $grid->column('stream_id', __('Stream'))->display(function () {
            if (!$this->stream) {
                return "-";
            }
            return  $this->stream->name;
        });
        $grid->column('academic_year_id', __('Academic year'))->display(function () {
            if (!$this->year) {
                return "-";
            }
            return  $this->year->name;
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
        $show = new Show(StudentHasClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('stream_id', __('Stream id'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));
        $show->field('academic_year_id', __('Academic year id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentHasClass());
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        $form->saving(function (Form $form) {

            if ($form->isCreating()) {
                $class = StudentHasClass::where([
                    'administrator_id' => $form->administrator_id,
                    'enterprise_id' => $form->academic_class_id,

                ])->first();
                if ($class != null) {
                    return Redirect::back()->withInput()->withErrors([
                        'academic_class_id' => 'Selected student is already registered in this class.'
                    ]);
                }
            }
        });


        $u = Admin::user();
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        if ($form->isCreating()) {

            $form->select('administrator_id', 'Student')->options(function () {
                return Administrator::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                    'user_type' => 'student',
                ])->get()->pluck('name', 'id');
            })
                ->rules('required');

            $form->select('academic_class_id', 'Class')->options(function () {
                return AcademicClass::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                ])->get()->pluck('name', 'id');
            })
                ->rules('required')->load(
                    'stream_id',
                    url('/api/streams?enterprise_id=' . $u->enterprise_id)
                );
        } else {
            $form->select('administrator_id', 'Student')->options(function () {
                return Administrator::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                    'user_type' => 'student',
                ])->get()->pluck('name', 'id');
            })
                ->readOnly()
                ->rules('required');

            $form->select('academic_class_id', 'Class')->options(function () {
                return AcademicClass::where([
                    'enterprise_id' => Admin::user()->enterprise_id,
                ])->get()->pluck('name', 'id');
            })
                ->readOnly()
                ->rules('required')->load(
                    'stream_id',
                    url('/api/streams?enterprise_id=' . $u->enterprise_id)
                );
        }





        $form->select('stream_id', __('Stream'))->options(function ($id) {
            return AcademicClassSctream::all()->pluck('name', 'id');
        });

        return $form;
    }
}
