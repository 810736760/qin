<?php


namespace App\Helper;

use App\Models\Admin_Manager;
use App\Services\User\UserService;

class AlarmHelper
{

    /**
     * 获取投手的邮件
     * @param $uid
     * @return array
     */
    public static function getCreatorMailAddress($uid): array
    {
        $mail = [];
        if ($uid) {
            $mail = Admin_Manager::query()
                ->whereIn('id', $uid)
                ->orWhere('power', UserService::POWER_CREATOR_LEADER)
                ->where('email', '!=', '')
                ->get()
                ->toArray();
            $mail = array_column($mail, 'mail');
        }

        // $mail[] = 'hongrongsheng@hangzhoukuaiwan.com';
        return $mail;
    }
}
