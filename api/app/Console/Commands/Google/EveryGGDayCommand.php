<?php

namespace App\Console\Commands\Google;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EveryGGDayCommand extends Command
{
    protected $signature = 'EveryGGDayCommand {date?}';

    protected $description = '之前GG的数据更新';


    public function handle()
    {
        // $date = date("Ymd", strtotime('-1 day'));
        // Artisan::call('google:SyncSetData', ['date' => $date]);
        //
        // $date = date("Ymd", strtotime('-7 day'));
        // Artisan::call('google:SyncSetData', ['date' => $date]);

        $startDate = '20220720';
        $end = '20220808';
        for ($curTime = strtotime($startDate); $curTime <= strtotime($end); $curTime += 86400) {
            echo date("Ymd", $curTime) . ' start!' . PHP_EOL;
            Artisan::call('google:SyncSetData', ['date' => date("Ymd", $curTime)]);
        }

    }
}
