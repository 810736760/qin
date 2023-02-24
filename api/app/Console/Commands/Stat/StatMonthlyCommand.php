<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Report\MonthlyReport;
use App\Services\CompanyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatMonthlyCommand extends Command
{
    protected $signature = 'StatMonthlyCommand';

    protected $description = '统计月度花费脚本';


    public function handle()
    {
        Artisan::call('StatMonthlySpendCommand'); // 先当月数据
        $dateIndex = date("d");
        if ($dateIndex < 10) {
            $date = Carbon::today()->subDay(10)->format('Ymd');
            Artisan::call('StatMonthlySpendCommand', ['date' => $date]);
        }
    }
}
