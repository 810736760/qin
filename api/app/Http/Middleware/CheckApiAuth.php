<?php

namespace App\Http\Middleware;

use App\Definition\ReturnCode;
use App\Models\Api\OrganizeConf;
use Closure;

class CheckApiAuth
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
        $token = $request->bearerToken();
        if (empty($token)) {
            echo json_encode(
                [
                    'code' => ReturnCode::ERROR_USER_PERMISSION,
                    'msg'  => '未授权'
                ]
            );
            exit();
        }

        $rs = OrganizeConf::getIns()->getByCond(['token' => $token], ['status']);

        if (empty($rs) || $rs['status'] != 1) {
            echo json_encode(
                [
                    'code' => ReturnCode::ERROR_INVALID_REFERER,
                    'msg'  => '授权已过期，请联系管理员'
                ]
            );
            exit();
        }
        return $next($request);
    }
}
