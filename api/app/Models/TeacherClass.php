<?php

namespace App\Models;

class TeacherClass extends BaseModel
{
    protected $table = 'teacher_class';

    protected $guarded = ['id'];

    /**
     * @return TeacherClass
     */
    public static function getIns(): TeacherClass
    {
        return parent::getInstance();
    }
}
