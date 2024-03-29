<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Enterprise extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            if ($m->id == 1) {
                die("Default enterprise cannot be deleted.");
                return false;
            }
        });

        self::created(function ($m) {
            Enterprise::my_update($m);
        });

        self::updated(function ($m) {
            Enterprise::my_update($m);
        });
    }

    public function active_term()
    {
        $t = Term::where([
            'enterprise_id' => $this->id,
            'is_active' => 1,
        ])->first();
        return $t;
    }

    public static function main_bank_account($m)
    {
        $fees_acc = Account::where([
            'type' => 'FEES_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($fees_acc == null) {
            $ac =  new Account();
            $ac->name = 'SCHOOL FEES ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'FEES_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }
        $fees_acc = Account::where([
            'type' => 'FEES_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($fees_acc == null) {
            die("Fees account not found");
        }
        return $fees_acc;
    }
    public static function my_update($m)
    {
        $owner = Administrator::find($m->administrator_id);
        if ($owner != null) {
            $owner->enterprise_id = $m->id;
            $owner->save();
        }

        $cash_acc = Account::where([
            'type' => 'CASH_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($cash_acc == null) {
            $ac =  new Account();
            $ac->name = 'CASH ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'CASH_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }

        $bank_acc = Account::where([
            'type' => 'BANK_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($bank_acc == null) {
            $ac =  new Account();
            $ac->name = 'MAIN BANK ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'BANK_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }

        $fees_acc = Account::where([
            'type' => 'FEES_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($fees_acc == null) {
            $ac =  new Account();
            $ac->name = 'SCHOOL FEES ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'FEES_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }

        $sql_acc = "SELECT administrator_id FROM accounts WHERE enterprise_id = $m->id";
        $sql_users = "SELECT * FROM admin_users WHERE enterprise_id = $m->id AND (user_type = 'employee' OR user_type = 'student') AND (admin_users.id NOT IN ($sql_acc)) ";
        $users_with_no_acconts = DB::select($sql_users);
        foreach ($users_with_no_acconts as $user) {
            $ac =  new Account();
            $ac->name = $user->first_name . ' ' . $user->last_name;
            if ($user->user_type == 'employee') {
                $ac->name .= " - Employee ID #$user->id";
                $ac->type = 'EMPLOYEE_ACCOUNT';
            } else {
                $ac->type = 'STUDENT_ACCOUNT';
                $ac->name .= " - Student ID #$user->id";
            }
            $ac->enterprise_id = $m->id;
            $ac->administrator_id = $user->id;
            $ac->save();
        }
    }
}
