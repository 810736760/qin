<?php

namespace App\Services\User;

use App\Services\Service;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService extends Service
{
    const POWER_ADMIN = 1;
    const POWER_CREATOR = 2; // 投手
    const POWER_CREATOR_LEADER = 3; // 投放组长
    const POWER_DESIGNER = 4;
    const POWER_CO_ADMIN = 5; // 企业管理员
    const POWER_OPERATION = 6; // 运营
    const POWER_EDITOR = 7; // 内容编辑
    const POWER_CREATOR_ASSISTANT = 11; // 投手助理
    const POWER_ABANDON = 14; // 废弃 保留角色名
    const POWER_TEST = 15; // 测试人员

    const FB_TEST_USER_ID = 159; // FB测试ID

    // 投手
    const CREATOR_GROUP = [
        self::POWER_CREATOR,
        self::POWER_CREATOR_LEADER,
        self::POWER_CREATOR_ASSISTANT
    ];

    const ADMIN_SHOW = [
        self::POWER_ADMIN,
        self::POWER_DESIGNER,
        self::POWER_CO_ADMIN
    ];

    /**
     * @return UserService
     */
    public static function getIns(): UserService
    {
        return parent::getInstance();
    }

    public function getSelfInfo()
    {
        return JWTAuth::parseToken()->authenticate()['original'];
    }
}
