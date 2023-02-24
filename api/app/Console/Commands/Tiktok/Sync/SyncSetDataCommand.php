<?php

namespace App\Console\Commands\Tiktok\Sync;

use App\Definition\TikTokColumn;
use App\Models\Tiktok\TiktokDataSnapshot;
use App\Models\Tiktok\TiktokDayAdData;
use App\Models\Tiktok\TiktokDayAdSetData;
use App\Models\Tiktok\TikTokSet;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Tiktok\AdAccountService;
use App\Services\Tiktok\CurlService;
use App\Services\RedisService;
use App\Services\Tiktok\InsightsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncSetDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:SyncSetData {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步TT广告组的成效';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        $args = $this->arguments();
        $date = Tool::get($args, 'date');
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id', 'timezone_offset_hours_utc']);
            $this->syncData($date, $aidRs);
            $this->syncAdData($date, $aidRs);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function syncData($date, $aidRs)
    {
        if ($date) {
            $date = date("Y-m-d", strtotime($date));
        }


        $level = InsightsService::LEVEL_ADSET;
        $idName = InsightsService::getIns()->fmtLightPathByLevel($level, '_id');
        $params = [
            'service_type' => 'AUCTION',
            'report_type'  => 'BASIC',
            'data_level'   => 'AUCTION_ADGROUP',
            'dimensions'   => [$idName],
            'metrics'      => array_keys(TikTokColumn::METRICS_LIST),
            'query_mode'   => 'REGULAR',
            'order_field'  => 'spend',
            'order_type'   => 'DESC',
            'page'         => 1,
            'page_size'    => 1000
        ];

        // 当前时区
        $curTimeZone = config('app.timezone_offset');
        $insights = [];
        foreach ($aidRs as $aidInfo) {
            $dateFmt = $date ?: Tool::getTodayDateWithTimeZone($aidInfo['timezone_offset_hours_utc'], $curTimeZone);
            $params['advertiser_id'] = $aidInfo['aid'];
            $params['start_date'] = $dateFmt;
            $params['end_date'] = $dateFmt;


            [$status, $msg, $rs, $code] = CurlService::getIns()->tkCurl(
                'report/integrated/get/',
                $params
            );
            usleep(100000);
            if ($code) {
                continue;
            }

            $fmtDate = Tool::fmtDateString($dateFmt);
            foreach ($rs['data']['list'] as $row) {
                $sid = $row['dimensions'][$idName];
                $metrics = $row['metrics'];
                $update = [
                    'sid'           => $sid,
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
                TiktokDayAdSetData::getIns()->updateOrInsert(
                    ['event_date' => $fmtDate, 'sid' => $sid],
                    $update
                );
                $insights[] = $update;
            }
        }

        if ($date) {
            return;
        }

        // 分批次查询 快照点
        $chunks = array_chunk($insights, CurlService::RESPONSE_LIMIT_TIMES_MAX);

        $curTime = time();
        $snapshot = [];
        $dateH = date("YmdH") . Tool::getMinuteRange($curTime);
        foreach ($chunks as $chunk) {
            $insightRs = array_column($chunk, null, 'sid');
            $rs = TikTokSet::getIns()
                ->select(
                    'sid',
                    'platform',
                    'os',
                    'user'
                )
                ->whereIn('sid', array_column($chunk, 'sid'))
                ->get()
                ->toArray();

            foreach ($rs as $row) {
                foreach (['spend', 'revenue', 'install'] as $index => $type) {
                    $key = implode("_", [$row['platform'], $row['os'], $index, $row['user']]);
                    $snapshot[$key] = isset($snapshot[$key]) ?
                        $snapshot[$key] + $insightRs[$row['sid']][$type] : $insightRs[$row['sid']][$type];
                }
            }
        }
        if (!empty($snapshot)) {
            foreach ($snapshot as $key => $value) {
                [$platform, $os, $type, $user] = explode("_", $key);
                TiktokDataSnapshot::getIns()->updateOrInsert(
                    [
                        'snapshot_time' => $dateH,
                        'type'          => $type,
                        'platform'      => $platform,
                        'os'            => $os,
                        'code'          => $user
                    ],
                    ['value' => $value]
                );
            }
            RedisService::getIns()->set(DBService::getIns()->snapshotTimeName('tiktok'), $curTime, RedisService::REDIS_EXPIRE_TIME_HOUR);
        }
    }

    public function syncAdData($date, $aidRs)
    {
        // 1小时采集一次
        // $recordName = Tool::fmtCoIdKey("sync_ad_date_" . date("YmdH"));
        // $rs = RedisService::getIns()->get($recordName);
        // if ($rs) {
        //     return;
        // }
        // RedisService::getIns()->set($recordName, 1);
        if ($date) {
            $date = date("Y-m-d", strtotime($date));
        }


        $level = InsightsService::LEVEL_AD;
        $idName = InsightsService::getIns()->fmtLightPathByLevel($level, '_id');
        $params = [
            'service_type' => 'AUCTION',
            'report_type'  => 'BASIC',
            'data_level'   => 'AUCTION_AD',
            'dimensions'   => [$idName],
            'metrics'      => array_keys(TikTokColumn::METRICS_LIST),
            'query_mode'   => 'REGULAR',
            'order_field'  => 'spend',
            'order_type'   => 'DESC',
            'page'         => 1,
            'page_size'    => 1000
        ];

        // 当前时区
        $curTimeZone = config('app.timezone_offset');
        foreach ($aidRs as $aidInfo) {
            $dateFmt = $date ?: Tool::getTodayDateWithTimeZone($aidInfo['timezone_offset_hours_utc'], $curTimeZone);
            $params['advertiser_id'] = $aidInfo['aid'];
            $params['start_date'] = $dateFmt;
            $params['end_date'] = $dateFmt;

            [$status, $msg, $rs, $code] = CurlService::getIns()->tkCurl(
                'report/integrated/get/',
                $params
            );

            usleep(100000);
            if ($code) {
                continue;
            }

            $fmtDate = Tool::fmtDateString($dateFmt);
            foreach ($rs['data']['list'] as $row) {
                $sid = $row['dimensions'][$idName];
                $metrics = $row['metrics'];
                $update = [
                    'ad_id'         => $sid,
                    'spend'         => $metrics['spend'] ?? 0,
                    'purchase'      => $metrics['total_purchase'] ?? 0,
                    'install'       => $metrics['real_time_app_install'] ?? 0,
                    'revenue'       => $metrics['total_purchase_value'] ?? 0,
                    'impressions'   => $metrics['impressions'] ?? 0,
                    'cpm'           => $metrics['cpm'] ?? 0,
                    'clicks'        => $metrics['clicks'] ?? 0,
                    'uniq_purchase' => $metrics['purchase'] ?? 0,
                    'pixel_revenue' => $metrics['total_complete_payment_rate'] ?? 0,
                ];
                TiktokDayAdData::getIns()->updateOrInsert(
                    ['event_date' => $fmtDate, 'ad_id' => $sid],
                    $update
                );
            }
        }
    }
}
