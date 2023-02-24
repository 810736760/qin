<?php

namespace App\Models;

class TeacherClassLog extends BaseModel
{
    protected $table = 'teacher_class_log';

    protected $guarded = ['id'];

    /**
     * @return TeacherClassLog
     */
    public static function getIns(): TeacherClassLog
    {
        return parent::getInstance();
    }
}
