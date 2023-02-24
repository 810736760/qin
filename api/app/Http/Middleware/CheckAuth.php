<?php
/**
 * Created by PhpStorm.
 * User: xubin
 * Date: 2017/5/13
 * Time: 下午5:10
 */

namespace App\Http\Middleware;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Services\User\UserService;
use Closure;
use App\Models\Role;
use PhpParser\Node\Stmt\Continue_;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userInfo = UserService::getIns()->getSelfInfo();
        $flag = false;
        if (UserService::POWER_ADMIN == $userInfo['power']) {
            $flag = true;
        }
        if ($flag) {
            return $next($request);
        } else {
            echo json_encode(
                [
                    'code' => ReturnCode::ERROR_USER_PERMISSION,
                    'msg'  => '无权限'
                ]
            );
            exit();
        }
    }

    private function checkAuth($pathId, $roleId, $coInfo): bool
    {
        // 主企业
        if ($coInfo['id'] == 1) {
            if ($roleId == UserService::POWER_CO_ADMIN) {
                return true;
            }
            $rights = Role:: getRightByRole($roleId);
            if (in_array($pathId, $rights)) {
                return true;
            }
        } else {
            $rights = Tool::getArrayByComma($coInfo['rights']);
            if (in_array($pathId, $rights)) {
                if ($roleId == UserService::POWER_CO_ADMIN) {
                    return true;
                }
                $roleRights = Role:: getRightByRole($roleId);
                if (in_array($pathId, $roleRights)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getPathId($url, $methods)
    {
        $rights = Role::getRightsByPath($url);
        if (empty($rights)) {
            return 0;
        }

        foreach ($rights as $row) {
            if (strpos($row['methods'], strtolower($methods)) !== false) {
                return $row['id'];
            }
        }
        return 0;
    }
}
