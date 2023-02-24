<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ReRunCommand extends Command
{
    protected $signature = 'ReRunSetData {start?} {end?}';

    protected $description = '重跑数据';


    public function handle()
    {
        // $args = $this->arguments();
        // $start = Tool::get($args, 'start');
        // $end = Tool::get($args, 'end', date("Ymd", strtotime('-1 day')));
        // if (empty($start)) {
        //     echo '请选择开始时间' . PHP_EOL;
        //     return;
        // }
        // $sT = strtotime($start);
        // $eT = strtotime($end);
        // for ($curTime = $sT; $curTime <= $eT; $curTime += 86400) {
        //     echo date("Ymd", $curTime) . PHP_EOL;
        //     Artisan::call('facebook:SyncSetDataHourly', ['date' => date("Ymd", $curTime)]);
        //     Artisan::call('tiktok:SyncSetDataHourly', ['date' => date("Ymd", $curTime)]);
        // }
        // 重跑前1天 前7天 前30天
        $dateRange = [1, 3, 7, 15, 30];
        foreach ($dateRange as $date) {
            Artisan::call('facebook:SyncSetData', ['date' => date("Ymd", strtotime("-{$date} days"))]);
            Artisan::call('tiktok:SyncSetData', ['date' => date("Ymd", strtotime("-{$date} days"))]);
            Artisan::call('google:SyncSetData', ['date' => date("Ymd", strtotime("-{$date} days"))]);
            Artisan::call('facebook:SyncSetDataHourly', ['date' => date("Ymd", strtotime("-{$date} days"))]);
            Artisan::call('tiktok:SyncSetDataHourly', ['date' => date("Ymd", strtotime("-{$date} days"))]);
            Artisan::call('google:SyncSetDataHourly', ['date' => date("Ymd", strtotime("-{$date} days"))]);
        }
    }
}
