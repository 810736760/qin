<?php

namespace App\Console\Commands\Facebook\Material;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EveryDayCommand extends Command
{
    protected $signature = 'Facebook:EveryDayCommand {date?}';

    protected $description = 'EveryDayCommand';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $hour = date('H');
        $start_time = date('Y-m-d H:m:s');
        Artisan::call('OriginalDataCommand');
        Artisan::call('StaticsMaterialCommand');
        Artisan::call('StaticsTikTokMaterialCommand');
        Artisan::call('MaterialTotalCommand');
        $end_time = date('Y-m-d H:m:s');
        if ($hour == 17) { // 次日重跑昨日数据 和7天的数据
            $date = date("Ymd", strtotime('-1 day'));
            Artisan::call('OriginalDataCommand', ['date' => $date]);
            Artisan::call('StaticsMaterialCommand', ['date' => $date]);
            Artisan::call('MaterialTotalCommand', ['date' => $date]);

            $date = date("Ymd", strtotime('-7 day'));
            Artisan::call('OriginalDataCommand', ['date' => $date]);
            Artisan::call('StaticsMaterialCommand', ['date' => $date]);
            Artisan::call('MaterialTotalCommand', ['date' => $date]);
        }


        Log::info('素材采集时间开始：' . $start_time . '  结束时间：' . $end_time);
    }
}
