<?php

namespace App\Admin\Controllers;

use App\Models\GradingScale;
use App\Models\Term;
use App\Models\TermlyReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TermlyReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Termly report cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new TermlyReportCard());
        $x = TermlyReportCard::find(2);
        $x->report_title .= rand(1, 10);
        $x->save();


        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('has_beginning_term', __('Has beginning term'));
        $grid->column('has_mid_term', __('Has mid term'));
        $grid->column('has_end_term', __('Has end term'));
        $grid->column('report_title', __('Report title'));

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
        $show = new Show(TermlyReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('has_beginning_term', __('Has beginning term'));
        $show->field('has_mid_term', __('Has mid term'));
        $show->field('has_end_term', __('Has end term'));
        $show->field('report_title', __('Report title'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new TermlyReportCard());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('academic_year_id', __('Academic year id'));

        $_terms = Term::where([
            'enterprise_id' => $u->enterprise_id
        ])
            ->orderBy('id', 'DESC')
            ->get();
        $terms = [];
        foreach ($_terms as  $v) {
            $terms[$v->id] = $v->academic_year->name . " - " . $v->name;
        }

        $scales = [];
        foreach (GradingScale::where([])
            ->orderBy('id', 'DESC')
            ->get() as $v) {
            $scales[$v->id] =  $v->name;
        }

        $form->select('term_id', __('Term'))->options($terms)
            ->creationRules(['required', "unique:termly_report_cards"]);
        $form->radio('has_beginning_term', __('Include beginning term exams?'))->options([1 => 'Yes', 0 => 'No'])->required();
        $form->radio('has_mid_term', __('Include Mid term exams?'))->options([1 => 'Yes', 0 => 'No'])->required();
        $form->radio('has_end_term', __('Include End of term exams?'))->options([1 => 'Yes', 0 => 'No'])->required();
        $form->text('report_title', __('Report title'));

        $form->select('grading_scale_id', __('Grading scale'))->options($scales)->required();

        return $form;
    }
}
