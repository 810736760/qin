<?php

namespace App\Models;

class UserActionLog extends BaseModel
{
    protected $table = 'user_action_log';

    protected $guarded = ['id'];

    /**
     * @return UserActionLog
     */
    public static function getIns(): UserActionLog
    {
        return parent::getInstance();
    }
}
