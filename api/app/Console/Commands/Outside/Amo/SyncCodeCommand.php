<?php

namespace App\Console\Commands\Outside\Amo;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\Outside\Amo\AmoBooks;
use App\Models\Outside\Amo\AmoCodes;
use App\Services\Outside\Amo\CurlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCodeCommand extends Command
{
    protected $signature = 'outside:AmoSyncCode {date?} {id?}';

    protected $description = '同步番茄推广码回传数据';


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
        $path = 'promotion';
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
            if ($status != ReturnCode::SUCCEED || empty(Tool::get($rs['result'] ?? [], 'promotion_infos'))) {
                return;
            }

            $lastPage = ceil($rs['result']['total_num'] / $params['page_size']);
            $allColumn = AmoCodes::getIns(0)->getTableColumn();
            foreach ($rs['result']['promotion_infos'] as $row) {
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

                if (empty($one)) {
                    continue;
                }
                $one['from'] = $id;
                AmoCodes::getIns(0)->updateOrInsert(
                    ['code' => $one['code']],
                    $one
                );
                AmoBooks::getIns(0)->updateOrInsert(
                    ['book_id' => $one['book_id']],
                    ['book_name' => $one['book_name']]
                );
            }
        } while ($params['page_num'] < $lastPage);
    }
}
