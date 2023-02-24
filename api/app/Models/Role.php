<?php

namespace App\Models;

use App\Helper\Tool;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\User\UserService;

class Role extends BaseModel
{
    protected $table = 'role';

    protected $fillable = [
        'is_delete', 'name', 'rights', 'channel_id'
    ];

    /**
     * @param int $businessId
     * @return Role
     */
    public static function getIns(): Role
    {
        return parent::getInstance();
    }

    public static function getAll($flag = 0, $fields = ['main.id', 'main.name', 'main.created_at'])
    {
        //不返回超级管理员
        $data = self::getIns()
            ->from(self::getIns()->getTableName() . ' as role')
            ->leftJoin(MainRole::getIns()->getTableName() . ' as main', 'main.id', 'role.id')
            ->select($fields)
            ->where('role.id', '>', 1)
            ->where('role.is_delete', 0)
            ->get()
            ->toArray();

        if ($flag == 1) {
            $rs = [];
            foreach ($data as $v) {
                $rs[$v['id']]['name'] = $v['name'];
            }
            return $rs;
        }

        return $data;
    }

    //获取角色id拥有的权限数组
    public static function getRoleRights($role_id): array
    {
        if ($role_id === UserService::POWER_ADMIN) {
            return [];
        }
        $coId = PublicService::getBusinessId();
        $co = CompanyService::getIns()->getCoInfoById($coId);
        $showRights = Tool::getArrayByComma($co['rights']);

        $rights = self::getIns()
            ->select('rights')
            ->where('id', $role_id)
            ->first()->rights;
        $rights = Tool::getArrayByComma($rights);
        if ($role_id === UserService::POWER_CO_ADMIN) {
            return Rights::query()
                ->where('is_delete', 0)
                ->pluck('path')
                ->toArray();
        }

        if (!empty($rights)) {
            return Rights::query()
                ->select('path')
                ->where('is_delete', 0)
                ->whereIn('id', $rights)
                ->pluck('path')
                ->toArray();
        }

        return [];
    }

    /**
     * 普通用户权限
     * @param $path
     * @return array
     */
    public static function getRightsByPath($path): array
    {
        return Rights::query()
            ->select('id', 'path', 'methods')
            ->where('path', $path)
            ->where('is_delete', 0)
            ->get()
            ->toArray();
    }

    public static function getRightByRole($role_id): array
    {
        $right = [];
        $rs = self::getIns()
            ->select('rights')
            ->where('id', $role_id)
            ->first();
        if (empty($rs)) {
            return $right;
        }
        return Tool::getArrayByComma($rs->rights);
    }
}
