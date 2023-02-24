<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookCampaign;
use App\Models\Facebook\FacebookAd;
use App\Models\Facebook\FacebookDataSnapshot;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\CurlService;
use App\Services\RedisService;
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
    protected $signature = 'facebook:SyncSetData {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步广告组的成效';


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
            $GLOBALS['co'] = $row['id'];
            $this->syncData($date);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function syncData($date)
    {
        if ($date) {
            $date = date("Y-m-d", strtotime($date));
        }
        $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');
        if (empty($tokenMap)) {
            return;
        }
        $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id',
            'timezone_offset_hours_utc', 'created_at', 'created_time']);


        $baseParams = [];
        // 若是之前 取前300个
        $baseParams['sort'] = ['spend_descending'];
        // $baseParams['filtering'] = [
        //     [
        //         "field"    => "campaign.delivery_status",
        //         "operator" => "IN",
        //         "value"    => ["deleted", "active", "error", "inactive", "off", "pending", 'archived']
        //     ]
        // ];
        $baseParams['action_attribution_windows'] = ['1d_click', '1d_view'];
        $baseParams['fields'] = 'spend,adset_id,action_values,actions,impressions,clicks,unique_actions,cpm';
        $baseParams['limit'] = 500;

        // 当前时区
        $curTimeZone = config('app.timezone_offset');
        $insights = [];
        foreach ($aidRs as $aidInfo) {
            if (!isset($tokenMap[$aidInfo['bm_id']])) {
                continue;
            }

            // if (strtotime($aidInfo['created_time']) - strtotime($date) > 86400 * 3) {
            //     dump($aidInfo['aid'] . '->' . $aidInfo['created_at']);
            //     continue;
            // }
            // if (!(strtotime($aidInfo['created_at']) - strtotime($date) > 86400 * 3 && strtotime($date) > strtotime($aidInfo['created_time']))) {
            //     dump($aidInfo['aid'] . '->' . $aidInfo['created_at']);
            //     continue;
            // }


            $token = Tool::get($tokenMap, $aidInfo['bm_id']) ?: config('facebook.access_token');
            $dateFmt = $date ?: Tool::getTodayDateWithTimeZone($aidInfo['timezone_offset_hours_utc'], $curTimeZone);
            $baseParams['time_range'] = ['since' => $dateFmt, 'until' => $dateFmt];
            [$code, $data] = CurlService::getIns()->getAllSetInfoByAid($aidInfo['aid'], $baseParams, $token);

            if (!$code || empty($data)) {
                continue;
            }
            $fmtDate = Tool::fmtDateString($dateFmt);
            foreach ($data as $row) {
                $ac = array_column($row['actions'] ?? [], 'value', 'action_type');
                $dayAc = array_column($row['actions'] ?? [], '1d_click', 'action_type');
                $av = array_column($row['action_values'] ?? [], 'value', 'action_type');
                $dayV = array_column($row['action_values'] ?? [], '1d_click', 'action_type');
                $uniqV = array_column($row['unique_actions'] ?? [], '1d_click', 'action_type');
                $update = [
                    'sid'             => $row['adset_id'],
                    'spend'           => $row['spend'] ?? 0,
                    'purchase'        => $ac['purchase'] ?? 0,
                    'purchase_0'      => $dayAc['purchase'] ?? 0,
                    'install'         => $ac['omni_app_install'] ?? 0,
                    'install_0'       => $dayAc['mobile_app_install'] ?? 0,
                    'revenue'         => $av['omni_purchase'] ?? 0,
                    'revenue_0'       => $dayV['omni_purchase'] ?? 0,
                    'pixel_revenue'   => $av['offsite_conversion.fb_pixel_purchase'] ?? 0,
                    'pixel_revenue_0' => $dayV['offsite_conversion.fb_pixel_purchase'] ?? 0,
                    'uniq_purchase'   => $uniqV['omni_purchase'] ?? 0,
                    'impressions'     => $row['impressions'] ?? 0,
                    'cpm'             => $row['cpm'] ?? 0,
                    'clicks'          => $ac['link_click'] ?? 0,
                    'pixel_registration'  => $ac['offsite_conversion.fb_pixel_complete_registration'] ?? 0,
                    'mobile_registration' => $ac['app_custom_event.fb_mobile_complete_registration'] ?? 0,
                ];
                FacebookDayAdSetData::getIns()->updateOrInsert(
                    ['event_date' => $fmtDate, 'sid' => $row['adset_id']],
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
            $rs = FacebookSet::getIns()
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
                foreach (['spend', 'revenue_0', 'install'] as $index => $type) {
                    $key = implode("_", [$row['platform'], $row['os'], $index, $row['user']]);
                    $snapshot[$key] = isset($snapshot[$key]) ?
                        $snapshot[$key] + $insightRs[$row['sid']][$type] : $insightRs[$row['sid']][$type];
                }
            }
        }
        if (!empty($snapshot)) {
            foreach ($snapshot as $key => $value) {
                [$platform, $os, $type, $user] = explode("_", $key);
                FacebookDataSnapshot::getIns()->updateOrInsert(
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
            RedisService::getIns()->set(DBService::getIns()->snapshotTimeName(), $curTime, RedisService::REDIS_EXPIRE_TIME_HOUR);
        }
    }
}
