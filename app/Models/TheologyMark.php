<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyMark extends Model
{
    use HasFactory;



    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            if ($m->subject == null) {
                die("Main subject not found.");
            }
            $m->theology_subject_id = $m->subject->id;
            return $m;
        });

        self::updating(function ($m) {
            if (($m->exam->max_mark < 0) || ($m->score > $m->exam->max_mark)) {
                return false;
            }
            $m->is_submitted = 1;
            if ($m->remarks == null || (strlen($m->remarks) < 3)) {
                $m->remarks = Utils::get_automaic_mark_remarks(
                    Utils::convert_to_percentage($m->score, $m->exam->max_mark)
                );
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(TheologyExam::class, 'theology_exam_id');
    }


    public function class()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id');
    }


    public function subject()
    {
        return $this->belongsTo(TheologySubject::class, 'theology_subject_id');
    }

    function student()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }

    function teacher()
    {
        return $this->belongsTo(Administrator::class, 'teacher_id');
    }
}
