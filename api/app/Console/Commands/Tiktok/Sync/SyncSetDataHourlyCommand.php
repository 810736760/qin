<?php

namespace App\Console\Commands\Tiktok\Sync;

use App\Definition\TikTokColumn;

use App\Models\Stat\DayAdSetsSummaryData;
use App\Models\Tiktok\TiktokAdAccounts;
use App\Models\Tiktok\TiktokDataSnapshot;
use App\Models\Tiktok\TiktokDayAdSetData;
use App\Models\Tiktok\TiktokHourAdSetData;
use App\Models\Tiktok\TikTokSet;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\Facebook\BMService;
use App\Services\RedisService;
use App\Services\Tiktok\AdAccountService;
use App\Services\Tiktok\CurlService;
use App\Services\Tiktok\InsightsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncSetDataHourlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:SyncSetDataHourly {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分时同步广告组的成效';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        $args = $this->arguments();
        $date = Tool::get($args, 'date', date("Ymd"));
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $dates = $this->syncData($date);
            $this->buildDayData($dates);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    // public function syncData($date)
    // {
    //
    //     // 获取所有广告集ID
    //     $rs = TiktokDayAdSetData::getIns()
    //         ->from(TiktokDayAdSetData::getIns()->getTableName() . ' as setsData')
    //         ->leftJoin(TikTokSet::getIns()->getTableName() . ' as sets', 'setsData.sid', 'sets.sid')
    //         ->where('setsData.event_date', $date)
    //         ->whereNotNull('sets.sid')
    //         ->where('setsData.spend', '>', 0)
    //         ->pluck('sets.aid', 'sets.sid')
    //         ->toArray();
    //     if (empty($rs)) {
    //         return;
    //     }
    //
    //     $params = [
    //         'time_granularity' => 'STAT_TIME_GRANULARITY_HOURLY',
    //         'order_type'       => 'DESC',
    //         'page'             => 1,
    //         'page_size'        => 1000,
    //     ];
    //
    //     $dateFmt = date("Y-m-d", strtotime($date));
    //
    //     foreach ($rs as $sid => $aid) {
    //         $params['advertiser_id'] = $aid;
    //         $params['start_date'] = $dateFmt;
    //         $params['end_date'] = $dateFmt;
    //         $params['filtering'] = [
    //             'adgroup_ids' => [$sid]
    //         ];
    //
    //
    //         [$status, $msg, $rs, $code] = \App\Services\Tiktok\CurlService::getIns()->tkCurl(
    //             'reports/adgroup/get/',
    //             $params
    //         );
    //
    //
    //         sleep(5);
    //         if (!$status) {
    //             continue;
    //         }
    //
    //
    //         foreach ($rs['data']['list'] as $row) {
    //             $spend = $row['stat_cost'] ?? 0;
    //             if (empty($spend)) {
    //                 continue;
    //             }
    //             TiktokHourAdSetData::getIns()->updateOrInsert(
    //                 ['event_date_hour' => date("YmdH", strtotime($row['stat_datetime'])), 'sid' => $sid],
    //                 ['spend' => $row['stat_cost'] ?? 0]
    //             );
    //         }
    //     }
    // }
    // 新版
    public function syncData($date): array
    {
        $isToday = $date == date("Ymd");
        $packageInfo = BMService::getAllPackage();
        $changeTimezone = array_column($packageInfo, 'stat_timezone', 'platform');


        $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id', 'timezone_offset_hours_utc']);
        $aidRs = array_filter($aidRs, function ($v) {
            if ($v['platform'] == -1) {
                return false;
            }
            return true;
        });

        $level = InsightsService::LEVEL_ADSET;
        $idName = InsightsService::getIns()->fmtLightPathByLevel($level, '_id');
        $params = [
            'service_type' => 'AUCTION',
            'report_type'  => 'BASIC',
            'data_level'   => 'AUCTION_ADGROUP',
            'dimensions'   => [$idName, 'stat_time_hour'],
            'metrics'      => array_keys(TikTokColumn::METRICS_LIST),
            'query_mode'   => 'REGULAR',
            'order_field'  => 'spend',
            'order_type'   => 'DESC',
            'page'         => 1,
            'page_size'    => 1000
        ];

        $fmtDate = date("Y-m-d", strtotime($date));
        // 当前时区
        $curTimeZone = config('app.timezone_offset');
        $eDate = [];
        foreach ($aidRs as $aidInfo) {
            $dateFmt = $isToday ? Tool::getTodayDateWithTimeZone($aidInfo['timezone_offset_hours_utc'], $curTimeZone) :
                $fmtDate;
            $eDate[$dateFmt] = 1;
            $params['advertiser_id'] = $aidInfo['aid'];
            $params['start_date'] = $dateFmt;
            $params['end_date'] = $dateFmt;


            [$status, $msg, $rs, $code] = CurlService::getIns()->tkCurl(
                'report/integrated/get/',
                $params
            );

            sleep(1);
            if (!$status) {
                continue;
            }

            foreach ($rs['data']['list'] as $row) {
                $metrics = $row['metrics'];

                $update = [
                    'spend'         => $metrics['spend'] ?? 0,
                    'purchase'      => $metrics['total_purchase'] ?? 0,
                    'install'       => $metrics['real_time_app_install'] ?? 0,
                    'revenue'       => $metrics['total_purchase_value'] ?? 0,
                    'uniq_purchase' => $metrics['purchase'] ?? 0,
                    'pixel_revenue' => $metrics['total_complete_payment_rate'] ?? 0,
                    'impressions'   => $metrics['impressions'] ?? 0,
                    'cpm'           => $metrics['cpm'] ?? 0,
                    'clicks'        => $metrics['clicks'] ?? 0,
                ];

                if (!array_sum($update)) {
                    continue;
                }


                $dimensions = $row['dimensions'];

                $userDate = Tool::getTodayDateWithTimeZone(
                    Tool::get($changeTimezone, $aidInfo['platform'], $aidInfo['timezone_offset_hours_utc']),
                    $aidInfo['timezone_offset_hours_utc'],
                    'YmdH',
                    strtotime($dimensions['stat_time_hour'])
                );
                TiktokHourAdSetData::getIns()->updateOrInsert(
                    [
                        'event_date_hour' => $userDate,
                        'sid'             => $dimensions['adgroup_id']
                    ],
                    $update
                );
            }
        }
        return $eDate ? array_keys($eDate) : [];
    }

    // 将一日数据汇总
    public function buildDayData($dates)
    {
        if (empty($dates)) {
            return;
        }
        sort($dates);
        $start = $dates[0];
        $end = $dates[count($dates) - 1];
        $dates[] = date("Ymd", strtotime($start) - 86400);
        $dates[] = date("Ymd", strtotime($end) + 86400);
        foreach ($dates as $date) {
            $date = date("Ymd", strtotime($date));
            $rs = TiktokHourAdSetData::getIns()
                ->from(TiktokHourAdSetData::getIns()->getTableName() . ' as setsData')
                ->rightJoin(TiktokSet::getIns()->getTableName() . ' as sets', 'sets.sid', 'setsData.sid')
                ->select(
                    'setsData.sid',
                    'sets.book_id',
                    'sets.link_id',
                    'sets.platform',
                    DB::Raw("sum(spend) as spend")
                )
                ->whereIn('event_date_hour', Tool::buildDayIndex($date))
                ->groupBy(['sid'])
                ->get()
                ->toArray();

            $chunks = array_chunk($rs, 500);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $row) {
                    $row['union_link_id'] = ApiService::buildPlatformLink($row['platform'], $row['link_id']);
                    DayAdSetsSummaryData::getIns()->updateOrInsert(
                        ['sid' => $row['sid'], 'event_date' => $date, 'from' => PublicService::PLATFORM_TYPE_TIKTOK],
                        $row
                    );
                }
            }
        }
    }
}
