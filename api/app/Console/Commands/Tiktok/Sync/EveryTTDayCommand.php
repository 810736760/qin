<?php

namespace App\Console\Commands\Tiktok\Sync;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EveryTTDayCommand extends Command
{
    protected $signature = 'EveryTTDayCommand {date?}';

    protected $description = '之前的数据更新';


    public function handle()
    {
        $date = date("Ymd", strtotime('-1 day'));
        Artisan::call('tiktok:SyncSetData', ['date' => $date]);

        $date = date("Ymd", strtotime('-7 day'));
        Artisan::call('tiktok:SyncSetData', ['date' => $date]);
    }
}
