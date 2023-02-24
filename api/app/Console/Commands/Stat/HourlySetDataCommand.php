<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class HourlySetDataCommand extends Command
{
    protected $signature = 'HourlySetData {start?} {end?}';

    protected $description = '跑小时数据';


    public function handle()
    {
        Artisan::call('facebook:SyncSetDataHourly');
        if (date("H") < 16) {
            Artisan::call('facebook:SyncSetDataHourly', ['date' => date("Ymd", strtotime("-1 days"))]);
        }

        Artisan::call('tiktok:SyncSetDataHourly');
        Artisan::call('google:SyncSetDataHourly');
    }
}
