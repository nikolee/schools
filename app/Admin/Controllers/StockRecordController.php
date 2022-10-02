<?php

namespace App\Admin\Controllers;

use App\Models\StockBatch;
use App\Models\StockRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class StockRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock out records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockRecord());
        $grid->disableActions();

        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('#ID'))->sortable();
        $grid->column('stock_batch_id', __('Stock batch'))
            ->display(function () {
                return $this->batch->cat->name . " Stock ID #" . $this->batch->id;
            })->sortable();

        $grid->column('stock_item_category_id', __('Stock category'))
            ->display(function () {
                return $this->batch->cat->name;
            })->sortable();
        $grid->column('created_by', __('Supplied by'))
            ->display(function () {
                return $this->createdBy->name . " - #" . $this->createdBy->id;
            })->sortable();
        $grid->column('received_by', __('Received by'))
            ->display(function () {
                return $this->receivedBy->name . " - #" . $this->receivedBy->id;
            })->sortable();

        $grid->column('quanity', __('Quanity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable()->totalRow(function ($x) {
                return number_format($x);
            });

        $grid->column('description', __('Description'));
        $grid->column('record_date', __('Date'));

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
        $show = new Show(StockRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('stock_batch_id', __('Stock batch id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('created_by', __('Created by'));
        $show->field('received_by', __('Received by'));
        $show->field('quanity', __('Quanity'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockRecord());


        $form->date('record_date', __('Date'))->rules('required');

        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);

        $form->hidden('created_by')->rules('required')->default(Admin::user()->id)
            ->value(Admin::user()->id);

        $cats = [];
        foreach (StockBatch::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->where('current_quantity', '>', 0)
            ->get() as $val) {
            $p = Str::plural($val->cat->measuring_unit);
            $cats[$val->id] = $val->cat->name . " " . number_format($val->current_quantity) . " $p - STOCK ID #{$val->id}";
        }

        $form->select('stock_batch_id', 'Stock batch')
            ->options($cats)->rules('required');



        $u = Admin::user();
        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=User"
        );


        $form->decimal('quanity', __('Quanity'))->rules('required');

        $form->select('received_by', "Received by")
            ->options(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $form->textarea('description', __('Description'));

        return $form;
    }
}