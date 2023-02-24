<?php

namespace App\Http\Middleware;

use App\Models\UserActionLog;
use App\Services\User\UserService;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AdminLog
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
        if (env('APP_DEBUG')) {
            return $next($request);
        }

        try {
            $userInfo = UserService::getIns()->getSelfInfo();
            $admin_id = $userInfo['id'];
            $nick_name = $userInfo['nickname'];
            $insertData = [
                'admin_id'  => $admin_id,
                'nick_name' => $nick_name,
                'path'      => $request->path(),
                'ip'        => $request->ip(),
                'params'    => json_encode($request->toArray())
            ];

            UserActionLog::getIns()->insert($insertData);
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}
