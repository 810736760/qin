<?php

namespace App\Console\Commands\Test;

use App\Console\Commands\Stat\SyncDayRechargeCommand;
use App\Helper\EncryptionHelper;
use App\Helper\Tool;
use App\Libs\SimpleRequest;
use App\Models\Facebook\BM;
use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookDailyBookSummaryData;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookHourAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Group;
use App\Models\PackageConf;
use App\Models\Stat\AbroadDayRecharge;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\AbroadLinkStatistics;
use App\Models\Stat\Book;
use App\Models\Stat\DayAdSetsSummaryData;
use App\Models\Stat\OldAdminManager;
use App\Models\TestBook\BookTestRecord;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\CurlService;
use App\Services\RedisService;
use App\Services\Tiktok\PublicService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Facades\Schema;

class Test extends Command
{
    protected $signature = 'Test {start_date?} {end_date?} {type?}';

    protected $description = '测试';

    public function handle()
    {
        $args = $this->arguments();
        $start = 20220805;
        $end = 20220807;
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $systemInfos = CompanyService::systemInfo();
            $this->ls($systemInfos, $start, $end);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }


    public function ls($systemInfos, $start, $end)
    {
        $params = [
            'table' => 'AbroadLinkStatistics',
            'where' => 'date',
            // 'fields'     => ['link_id', 'date', 'pnumber', 'pay_num', 'oneday_pay_num', 'recharge',
            //     'oneday_money', 'primecost', 'oneday_order_num'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadLinkStatistics::getIns());
    }
    // public function abroadDayRecharge($systemInfos, $start, $end)
    // {
    //     $params = [
    //         'table' => 'AbroadDayRecharge',
    //         'where' => 'order_date',
    //         // 'fields'     => ['link_id', 'user_date', 'order_date', 'money', 'created_at', 'updated_at'],
    //     ];
    //
    //     ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadDayRecharge::getIns());
    //     dump('abroadDayRecharge is done');
    // }
}
