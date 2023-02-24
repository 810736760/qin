<?php

namespace App\Console\Commands\Stat;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TimeToCommissionCommand extends Command
{
    protected $signature = 'TimeToCommissionCommand';

    protected $description = '每月提成统计';


    public function handle()
    {
        // 数据库周日为第一天
        Log::info($this->description . '开始');
        $startTime = microtime(true);
        // Artisan::call('Commission', ['start_date' => 20220101, 'end_date' => 20220110]);
        Artisan::call('Commission', ['end_date' => 20220402]);
        Artisan::call('Commission', ['end_date' => 20220331, 'os' => 1]);
        Artisan::call('Commission', ['start_date' => 20220403, 'end_date' => 20221231, 'type' => 1]);
        Artisan::call('Commission', ['start_date' => 20220401, 'end_date' => 20221231, 'type' => 1, 'os' => 1]);
        Artisan::call('CommissionSummaryCommand');

        Log::info($this->description . '结束,耗时：' . (microtime(true) - $startTime));
    }
}
