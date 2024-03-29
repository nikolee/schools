<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::updating(function ($m) {
            if (isset($m->new_balance)) {
                if ($m->new_balance == 1) {
                    if (isset($m->new_balance_amount)) {
                        $new_balance = ((int)($m->new_balance_amount));
                        $current_balance = $m->balance();
                        $trans_amount = $new_balance - $current_balance;

                        $ent = Enterprise::find($m->enterprise_id);

                        $trans = new Transaction();
                        $trans->enterprise_id = $m->enterprise_id;
                        $trans->account_id = $m->id;
                        $trans->amount = $trans_amount;
                        if ($trans_amount < 0) {
                            $trans->description = "Credited UGX $trans_amount to meet the correct balance.";
                        } else {
                            $trans->description = "Credited UGX $trans_amount to meet the correct balance.";
                        }

                        $term = $ent->active_term();
                        $trans->academic_year_id = $term->academic_year_id;
                        $trans->term_id = $term->id;
                        $trans->school_pay_transporter_id = "";
                        $trans->created_by_id = Admin::user()->id;
                        $trans->is_contra_entry = false;
                        $bank = Enterprise::main_bank_account($ent);
                        $trans->type = 'FEES_PAYMENT';
                        $trans->contra_entry_account_id = $bank->id;
                        $trans->contra_entry_transaction_id = 0;
                        $today = Carbon::now();
                        $trans->payment_date = $today->toDateTimeString();
                        $trans->save();
                    }
                }
            }


            if (isset($m->new_balance)) {
                unset($m->new_balance);
            }
            if (isset($m->new_balance_amount)) {
                unset($m->new_balance_amount);
            }

            return $m;
            //new_balance
        });
        self::creating(function ($m) {
            if ($m->type == 'CASH_ACCOUNT') {
                $cash_acc = Account::where([
                    'type' => 'CASH_ACCOUNT',
                    'enterprise_id' => $m->enterprise_id,
                ])->first();
                if ($cash_acc != null) {
                    return false;
                }
            }
            if ($m->type == 'FEES_ACCOUNT') {
                $acc = Account::where([
                    'type' => 'FEES_ACCOUNT',
                    'enterprise_id' => $m->enterprise_id,
                ])->first();
                if ($acc != null) {
                    return false;
                }
            }
            self::deleting(function ($m) {
                die("You cannot delete this account.");
            });
        });
    }

    public static function create($administrator_id)
    {
        $admin = Administrator::where([
            'id' => $administrator_id
        ])->first();
        if ($admin == null) {
            die("Account was not created because admin account was not found.");
        }
        $acc = Account::where(['administrator_id' => $administrator_id])->first();
        if ($acc != null) {
            return $acc;
        }

        $acc =  new Account();

        $acc->enterprise_id = $admin->enterprise_id;
        $acc->name = $admin->first_name . " " . $admin->given_name . " " . $admin->last_name;
        $acc->administrator_id = $administrator_id;
        $acc->type = $administrator_id;
        $acc->balance = 0;
        $acc->type = $admin->user_type;
        if ($admin->user_type == 'student') {
            $acc->type = 'STUDENT_ACCOUNT';
        } else if ($admin->user_type == 'employee') {
            $acc->type = 'EMPLOYEE_ACCOUNT';
        }
        $acc->save();
        return $acc;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }

    function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function balance()
    {

        $payable = 0;
        $paid = 0;
        $balance = 0;
        foreach ($this->transactions as $v) {
            if ($v->amount < 0) {
                $payable += $v->amount;
            } else {
                $paid += $v->amount;
            }
        }
        $balance = $payable + $paid;
        return $balance;
    }
}
