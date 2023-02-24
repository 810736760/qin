<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncCommand extends Command
{
    protected $signature = 'SyncCommand {start_date?} {end_date?}';

    protected $description = 'SyncCommand';


    public function handle()
    {
        $args = $this->arguments();
        $start = Tool::get($args, 'start_date') ?: date("Ymd", strtotime('-2 days'));
        $end = Tool::get($args, 'end_date') ?: date("Ymd", strtotime('1 days'));
        Artisan::call('HourlySetData'); // 先跑小时数据
        Artisan::call('SyncDayRechargeCommand', ['start_date' => $start, 'end_date' => $end]);
    }
}
