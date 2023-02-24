<?php

namespace App\Console\Commands\Outside\Amo;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\Outside\Amo\AmoUsers;
use App\Services\Outside\Amo\CurlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncUserCommand extends Command
{
    protected $signature = 'outside:AmoSyncUser {date?} {id?}';

    protected $description = '同步番茄用户回传数据';


    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        // 番茄0时区
        $date = Tool::get($args, 'date', date("Ymd", time() - 8 * 3600));

        $id = Tool::get($args, 'id', 1);

        $this->main($date, $id);

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($date, $id)
    {
        $startTime = strtotime($date);
        $path = 'user_relation';
        $params = [
            'begin_time' => $startTime,
            'end_time'   => $startTime + 86399,
            'page_num'   => 0,
            'page_size'  => 100

        ];
        // 通过Api获取数据
        $serv = new CurlService($id);
        do {
            $params['page_num']++;
            [$status, $msg, $rs, $code] = $serv->fetchCurl($path, $params);
            if ($status != ReturnCode::SUCCEED || empty(Tool::get($rs['result'] ?? [], 'user_infos'))) {
                return;
            }

            $lastPage = ceil($rs['result']['total_num'] / $params['page_size']);
            $allColumn = AmoUsers::getIns(0)->getTableColumn();

            foreach ($rs['result']['user_infos'] as $row) {
                $one = [];
                foreach ($row as $key => $value) {
                    if (!in_array($key, $allColumn)) {
                        continue;
                    }
                    // if (Tool::isInclude($key, '_time') && is_numeric($value)) {
                    //     $value = date("Y-m-d H:i:s", $value);
                    // }
                    if (Tool::isInclude($key, '_time') && is_numeric($value)) {
                        $keyAt = str_replace('_time', '_at', $key);
                        $one[$keyAt] = date("Y-m-d H:i:s", $value - 28800);
                    }
                    if ($key == 'promotion_url') {
                        $one['code'] = str_replace('?code=', '', $value);
                    }
                    $one[$key] = $value;
                }

                if (empty($one['create_time'])) {
                    $one['create_time'] = $one['effective_time'];
                    $one['create_at'] = $one['expire_at'];
                }

                if (empty($one)) {
                    continue;
                }

                AmoUsers::getIns(0)->updateOrInsert(
                    ['user_id' => $one['user_id']],
                    $one
                );
            }
        } while ($params['page_num'] < $lastPage);
    }
}
