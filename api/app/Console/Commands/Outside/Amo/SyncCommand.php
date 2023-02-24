<?php

namespace App\Console\Commands\Outside\Amo;

use App\Helper\Tool;
use App\Services\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCommand extends Command
{
    protected $signature = 'outside:AmoSync {start?} {end?}';

    protected $description = '同步番茄回传数据';


    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        // // 番茄0时区
        $startDate = Tool::get($args, 'start', date("Ymd", time() - 8 * 3600));
        $endDate = Tool::get($args, 'end', date("Ymd", time()));

        $list = config('outside_amo.list');
        foreach ($list as $row) {
            for ($curTime = strtotime($startDate); $curTime <= strtotime($endDate); $curTime += 86400) {
                $date = date("Ymd", $curTime);
                dump($date);
                Artisan::call('outside:AmoSyncCode', ['date' => $date, 'id' => $row['id']]);
                Artisan::call('outside:AmoSyncUser', ['date' => $date, 'id' => $row['id']]);
                Artisan::call('outside:AmoSyncOrder', ['date' => $date, 'id' => $row['id']]);

                sleep(1);
            }
            if (date("H") == 8 && RedisService::getIns()->set(
                'lock_amo_stat' . date("Ymd"),
                1,
                RedisService::REDIS_EXPIRE_TIME_DATE,
                true
            )) {
                Artisan::call('outside:AmoSyncOrder', ['date' => date("Ymd", strtotime("-1 days")), 'id' => $row['id']]);
                sleep(1);
                Artisan::call('outside:AmoSyncOrder', ['date' => date("Ymd", strtotime("-3 days")), 'id' => $row['id']]);
                sleep(1);
                Artisan::call('outside:AmoSyncOrder', ['date' => date("Ymd", strtotime("-7 days")), 'id' => $row['id']]);
                sleep(1);
            }
        }


        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }
}
