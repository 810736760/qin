<?php

namespace App\Models;

use App\Helper\Tool;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\User\UserService;

class MainRole extends BaseModel
{
    protected $table = 'main_role';

    /**
     * @return MainRole
     */
    public static function getIns(): MainRole
    {
        return parent::getInstance();
    }

}
