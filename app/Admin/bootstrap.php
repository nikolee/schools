<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Encore\Admin\Facades\Admin;
use App\Admin\Extensions\Nav\Shortcut;
use App\Admin\Extensions\Nav\Dropdown;
use App\Models\Utils;
use Illuminate\Support\Facades\Auth;

/* Utils::students_batch_import("public/storage/files/1.xlsx");
die("DONE!"); */

Encore\Admin\Form::forget(['map', 'editor']);

$u = Auth::user();

Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    $u = Auth::user();
    Utils::system_boot($u);
    $navbar->left(view('admin.search-bar', [
        'u' => $u
    ]));
    $links = [];

    if ($u != null) {

        if ($u->isRole('super-admin')) {
            $links = [
                'Create new user' => admin_url('auth/users/create'),
                'Create new enterprise' => admin_url('enterprises/create'),
            ];
        }
        if ($u->isRole('admin')) {
            $links = [
                'Add new staff' => 'employees/create',
            ];
        }
        if ($u->isRole('bursar')) {
            $links = [
                'School fees payment' => 'school-fees-payment/create',
                'Transaction' => 'transactions/create',
            ];
        }

        if ($u->isRole('dos')) {
            $links = [
                'Admit new student' => 'students/create',
            ];
        }

        $navbar->left(Shortcut::make($links, 'fa-plus')->title('ADD NEW'));

        $navbar->left(new Dropdown());

        $check_list = [];
        $u = Auth::user();
        if ($u != null) {
            $check_list = Utils::system_checklist($u);
        }
        $navbar->right(view('widgets.admin-links', [
            'items' => $check_list
        ]));
    }
});

Admin::css('/css/jquery-confirm.min.css');
Admin::js('/js/charts.js');

Admin::css(url('/assets/bootstrap.css'));
Admin::css('/assets/styles.css');
