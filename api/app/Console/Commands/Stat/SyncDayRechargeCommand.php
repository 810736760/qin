<?php

namespace App\Console\Commands\Stat;

use App\Models\BaseModel;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\PlatformTotal\PlatformBookUserUniqueReadChapterCount;
use App\Models\PlatformTotal\PlatformBookUserUniqueReadTime;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\BookMap;
use App\Models\PlatformTotal\PlatformBookUserUniqueRead;
use App\Models\Stat\ChannelPayGear;
use App\Models\Stat\DayAdSetsSummaryData;
use App\Models\Stat\LinkUploadConfig;
use App\Models\Stat\AbroadDayRecharge;
use App\Models\Stat\AbroadDayRechargeDiscount;
use App\Models\Stat\AbroadDayRechargePush;
use App\Models\Stat\AbroadDayRechargePayNum;
use App\Models\Stat\AbroadLinkStatistics;
use App\Models\Stat\Orders;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helper\Tool;

class SyncDayRechargeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncDayRechargeCommand {start_date?} {end_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据日期同步其他项目的表数据';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $args = $this->arguments();
        $start = Tool::get($args, 'start_date') ?: date("Ymd");
        $end = Tool::get($args, 'end_date') ?: date("Ymd");
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $systemInfos = CompanyService::systemInfo();

            $this->abroadDayRecharge($systemInfos, $start, $end);
            // Log::info($this->description . " abroadDayRecharge use time:" . (microtime(true) - $startTime), $args);
            $this->abroadLinkStatistics($systemInfos, $start, $end);
            // Log::info($this->description . " abroadLinkStatistics use time:" . (microtime(true) - $startTime), $args);
            $this->abroadDayRechargeDiscount($systemInfos, $start, $end);
            // Log::info($this->description . " abroadDayRechargeDiscount use time:" . (microtime(true) - $startTime), $args);
            $this->abroadDayRechargePush($systemInfos, $start, $end);
            // Log::info($this->description . " abroadDayRechargePush use time:" . (microtime(true) - $startTime), $args);
            $this->abroadDayRechargePayNum($systemInfos, $start, $end);
            // Log::info($this->description . " abroadDayRechargePayNum use time:" . (microtime(true) - $startTime), $args);
            $this->linkUploadConfig($systemInfos, $start, $end);
            // Log::info($this->description . " linkUploadConfig use time:" . (microtime(true) - $startTime), $args);
            $this->orders($systemInfos, $start, $end);
            // Log::info($this->description . " orders use time:" . (microtime(true) - $startTime), $args);
            if ($row['id'] == 1) {
                $lock = RedisService::getIns()->set(
                    Tool::fmtCoIdKey('book_sync_change_lock'),
                    1,
                    RedisService::REDIS_EXPIRE_TIME_DATE / 2,
                    true
                );
                if ($lock) {
                    $this->books($systemInfos, $start, $end);
                    Log::info($this->description . "books use time:" . (microtime(true) - $startTime), $args);
                }
            }

//            $this->platformBookUserUniqueRead($systemInfos, $start, $end);
//            $this->platformBookUserUniqueReadChapterCount($systemInfos, $start, $end);
//            $this->platformBookUserUniqueReadTime($systemInfos, $start, $end);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function abroadDayRecharge($systemInfos, $start, $end)
    {
        $params = [
            'table' => 'AbroadDayRecharge',
            'where' => 'updated_at',
            // 'fields'     => ['link_id', 'user_date', 'order_date', 'money', 'created_at', 'updated_at'],
        ];

        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadDayRecharge::getIns());
        dump('abroadDayRecharge is done');
    }

    public function linkUploadConfig($systemInfos, $start, $end)
    {
        $params = [
            'base'  => '\App\Models\\',
            'table' => 'LinkUploadConfig',
            'where' => 'updated_at',
            // 'fields'     => ['link_id', 'user_date', 'order_date', 'money', 'created_at', 'updated_at'],
        ];

        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, LinkUploadConfig::getIns());
        dump('linkUploadConfig is done');
    }


    public function books($systemInfos, $start, $end)
    {
        $params = [
            'base'  => '\App\Models\\',
            'table' => 'Book',
            'where' => 'updated_at',
            // 'fields'     => ['link_id', 'user_date', 'order_date', 'money', 'created_at', 'updated_at'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, BookMap::getIns());
        dump('books is done');
    }


    // public function requestApi($systemInfos, $start, $end, $params, $model)
    // {
    //     $keyDate = $params['where'];
    //     $curTimeZone = config('app.timezone_offset');
    //     // 当天统计的时间 需要判定时区
    //     $isToday = false;
    //     if ($start == date("Ymd")) {
    //         $isToday = true;
    //     }
    //     $params['page_size'] = 800;
    //     foreach ($systemInfos as $info) {
    //         if ($isToday) {
    //             $start = Tool::getTodayDateWithTimeZone($info['timezone'], $curTimeZone, 'Ymd');
    //             $end = Tool::getTodayDateWithTimeZone($info['timezone'], $curTimeZone, 'Ymd', strtotime($end));
    //         }
    //         $params['start_date'] = $start;
    //         $params['end_date'] = $end;
    //
    //         $page = 1;
    //         if ($keyDate != 'updated_at') {
    //             $model->delByCond([$keyDate => ['between', [$start, $end]], 'platform' => $info['platform']]);
    //         }
    //
    //         do {
    //             $params['page'] = $page;
    //             $rs = ApiService::getIns()->listDayRecharge($info['platform'], $params);
    //             if (empty(Tool::get($rs, 'list')) || empty(Tool::get($rs['list'], 'data'))) {
    //                 break;
    //             }
    //             foreach ($rs['list']['data'] as &$row) {
    //                 if (Tool::get($row, 'id')) {
    //                     unset($row['id']);
    //                 }
    //                 $row['platform'] = $info['platform'];
    //                 if (array_key_exists('link_id', $row)) {
    //                     $row['union_link_id'] = ApiService::buildPlatformLink($info['platform'], $row['link_id']);
    //                 }
    //
    //
    //                 if ($keyDate == 'updated_at') {
    //                     $keyParams = [];
    //                     foreach (['union_link_id', 'user_date', 'union_cpg_id', 'order_date'] as $item) {
    //                         if (array_key_exists($item, $row)) {
    //                             $keyParams[$item] = $row[$item];
    //                         }
    //                     }
    //                     $model->updateOrInsert($keyParams, $row);
    //                 }
    //             }
    //             if ($keyDate != 'updated_at') {
    //                 $model->insert($rs['list']['data']);
    //             }
    //             // $model->insert($rs['list']['data']);
    //
    //             $page = $rs['list']['current_page'] < $rs['list']['last_page'] ? $page + 1 : 0;
    //             usleep(10000);
    //         } while ($page);
    //     }
    // }

    public function abroadLinkStatistics($systemInfos, $start, $end)
    {
        $params = [
            'table' => 'AbroadLinkStatistics',
            'where' => 'updated_at',
            // 'fields'     => ['link_id', 'date', 'pnumber', 'pay_num', 'oneday_pay_num', 'recharge',
            //     'oneday_money', 'primecost', 'oneday_order_num'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadLinkStatistics::getIns());
        // 其他包的官方成本需要单独计算
        // $this->calcLinkCost($start, $end);
        dump('abroadLinkStatistics is done');
    }

    public function calcLinkCost($start, $end)
    {
        // if (BaseModel::isMainBusiness($GLOBALS['co'])) {
        //     return;
        // }
        $dataArr = Tool::fmtBetweenDate($start, $end);
        $data = DayAdSetsSummaryData::getIns()
            ->select(
                'event_date',
                DB::raw('sum(spend) as spend'),
                'union_link_id',
                'platform'
            )
            ->whereIn('event_date', $dataArr)
            ->groupBy(['event_date', 'union_link_id'])
            ->get()
            ->toArray();
        if (empty($data)) {
            return;
        }
        $androidRs = AbroadLink::getIns()->listByCond(
            [
                'union_link_id' => ['in', array_column($data, 'union_link_id')],
                'system'        => 0,
            ],
            ['union_link_id']
        );
        $androidLink = array_column($androidRs, 'union_link_id');
        if (empty($androidLink)) {
            return;
        }
        $usd = DBService::getIns()->getUSD2HKD() * 100;

        foreach ($data as $row) {
            if (!in_array($row['union_link_id'], $androidLink) || $row['platform'] == 6) {
                continue;
            }
            AbroadLinkStatistics::getIns()->updateByCond(
                ['union_link_id' => $row['union_link_id'], 'date' => $row['event_date']],
                ['primecost' => $row['spend'] * $usd]
            );
        }
    }

    public function abroadDayRechargeDiscount($systemInfos, $start, $end)
    {
        $params = [
            'table' => 'AbroadDayRechargeDiscount',
            'where' => 'updated_at',
            // 'fields'     => ['link_id', 'date', 'pnumber', 'pay_num', 'oneday_pay_num', 'recharge',
            //     'oneday_money', 'primecost', 'oneday_order_num'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadDayRechargeDiscount::getIns());
        dump('AbroadDayRechargeDiscount is done');
    }

    public function abroadDayRechargePush($systemInfos, $start, $end)
    {
        $params = [
            'table' => 'AbroadDayRechargePush',
            'where' => 'updated_at',
            // 'fields'     => ['link_id', 'date', 'pnumber', 'pay_num', 'oneday_pay_num', 'recharge',
            //     'oneday_money', 'primecost', 'oneday_order_num'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadDayRechargePush::getIns());
        dump('AbroadDayRechargePush is done');
    }

    public function abroadDayRechargePayNum($systemInfos, $start, $end)
    {
        $params = [
            'start_date' => $start,
            'end_date'   => date("Ymd", strtotime($end) + 86400),
            'table'      => 'AbroadDayRechargePayNum',
            'where'      => 'updated_at'
            // 'fields'     => ['link_id', 'date', 'pnumber', 'pay_num', 'oneday_pay_num', 'recharge',
            //     'oneday_money', 'primecost', 'oneday_order_num'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, AbroadDayRechargePayNum::getIns());
        dump('abroadDayRechargePayNum is done');
    }

    public function platformBookUserUniqueRead($systemInfos, $start, $end)
    {
        $params = [
            'base'  => '\App\Models\PlatformTotal\\',
            'table' => 'PlatformBookUserUniqueRead',
            'where' => 'updated_at',
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, PlatformBookUserUniqueRead::getIns());
        dump('PlatformBookUserUniqueRead is done');
    }

    public function platformBookUserUniqueReadChapterCount($systemInfos, $start, $end)
    {
        $params = [
            'base'  => '\App\Models\PlatformTotal\\',
            'table' => 'PlatformBookUserUniqueReadChapterCount',
            'where' => 'updated_at',
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, PlatformBookUserUniqueReadChapterCount::getIns());
        dump('PlatPlatformBookUserUniqueReadChapterCount is done');
    }

    public function platformBookUserUniqueReadTime($systemInfos, $start, $end)
    {
        $params = [
            'base'  => '\App\Models\PlatformTotal\\',
            'table' => 'PlatformBookUserUniqueReadTime',
            'where' => 'updated_at',
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, PlatformBookUserUniqueReadTime::getIns());
        dump('PlatformBookUserUniqueReadTime is done');
    }

    public function orders($systemInfos, $start, $end)
    {
        $params = [
            'base'  => '\App\Models\\',
            'table' => 'Orders',
            'where' => 'updated_at',
            // 'where' => 'created_at',
            // 'fields'     => ['link_id', 'user_date', 'order_date', 'money', 'created_at', 'updated_at'],
        ];
        ApiService::getIns()->requestCommonByDate($systemInfos, $start, $end, $params, Orders::getIns());
        dump('orders is done');
    }
}
