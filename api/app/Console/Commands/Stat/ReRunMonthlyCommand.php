<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ReRunMonthlyCommand extends Command
{
    protected $signature = 'ReRunSetDataMonthly {start?} {end?}';

    protected $description = '重跑上月数据';


    public function handle()
    {
        $curMonthFirstDay = date("Ym01");
        $lastTimestamp = strtotime($curMonthFirstDay) - 86400;
        // $lastMonthLastDay = date("Ymd", $lastTimestamp);
        $lastMonthLastDay = date("Ymd");
        $lastMonthFirstDay = date("Ym01", $lastTimestamp);
        Log::info($this->description . "开始重跑", [$lastMonthFirstDay, $lastMonthLastDay]);


        $start = microtime(true);

        for ($curTime = strtotime($lastMonthFirstDay); $curTime <= strtotime($lastMonthLastDay); $curTime += 86400) {
            $curDate = date("Ymd", $curTime);
            Log::info($this->description . "正在重跑" . $curDate);
            // Artisan::call('facebook:SyncSetData', ['date' => $curDate]);
            Artisan::call('facebook:SyncAdData', ['date' => $curDate]);
            usleep(20000);
        }
        Log::info($this->description . "结束,user=>" . (microtime(true) - $start));
    }
}
