<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookHourAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Stat\DayAdSetsSummaryData;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\CurlService;
use App\Services\RedisService;
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
    protected $signature = 'facebook:SyncSetDataHourly {date?}';

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

    public function syncData($date): array
    {
        $isToday = $date == date("Ymd");
        $packageInfo = BMService::getAllPackage();
        $changeTimezone = [];
        foreach ($packageInfo as $row) {
            if ($row['stat_timezone'] == $row['timezone']) {
                continue;
            }
            $changeTimezone[$row['platform']] = $row['stat_timezone'];
        }


        // 获取所有广告集ID
        $rs = FacebookDayAdSetData::getIns()
            ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setsData')
            ->leftJoin(FacebookSet::getIns()->getTableName() . ' as sets', 'setsData.sid', 'sets.sid')
            ->leftJoin(FacebookAdAccount::getIns()->getTableName() . ' as accounts', 'accounts.aid', 'sets.aid')
            ->where('setsData.event_date', $date)
            ->whereNotNull('sets.sid')
            ->select(
                'accounts.bm_id',
                'sets.sid',
                'sets.platform',
                'sets.link_id',
                'sets.union_link_id',
                'accounts.timezone_offset_hours_utc'
            )
            ->get()
            ->toArray();

        if (empty($rs)) {
            return [];
        }
        $list = [];
        foreach ($rs as $row) {
            $list[$row['bm_id']][$row['timezone_offset_hours_utc']][] = $row['sid'];
        }

        $platformMap = array_column($rs, 'platform', 'sid');
        $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');


        if (empty($tokenMap)) {
            return [];
        }

        $curTimeZone = config('app.timezone_offset');

        $baseParams = [];
        // 若是之前 取前300个
        $baseParams['fields'] = 'spend,action_values,actions,impressions,clicks,unique_actions,cpm';
        $baseParams['breakdowns'] = ['hourly_stats_aggregated_by_advertiser_time_zone'];
        $baseParams['action_attribution_windows'] = ['1d_click', '1d_view'];

        $fmtDate = date("Y-m-d", strtotime($date));

        $eDate = [];
        foreach ($list as $bmId => $sidArr) {
            if (!isset($tokenMap[$bmId])) {
                continue;
            }

            $token = Tool::get($tokenMap, $bmId) ?: config('facebook.access_token');

            foreach ($sidArr as $timezone => $sids) {
                $dateFmt = $isToday ? Tool::getTodayDateWithTimeZone($timezone, $curTimeZone) :
                    $fmtDate;
                $eDate[$dateFmt] = 1;
                $chunks = array_chunk($sids, CurlService::REQUEST_ONCE_IN_ONE_BATCH);
                $baseParams['time_range'] = ['since' => $dateFmt, 'until' => $dateFmt];
                foreach ($chunks as $oneChunk) {
                    $baseParams['ids'] = implode(',', $oneChunk);
                    [$status, $curlMsg, $data] = CurlService::getIns()->curlRequest(
                        'insights',
                        $baseParams,
                        $token
                    );
                    if (!$status) {
                        continue;
                    }

                    foreach ($data as $sid => $row) {
                        if (empty($row['data'] ?? [])) {
                            continue;
                        }

                        foreach ($row['data'] as $one) {
                            $dayV = array_column($one['action_values'] ?? [], '1d_click', 'action_type');
                            $ac = array_column($one['actions'] ?? [], 'value', 'action_type');
                            $dayAc = array_column($one['actions'] ?? [], '1d_click', 'action_type');
                            $av = array_column($one['action_values'] ?? [], 'value', 'action_type');
                            $update = [
                                'spend'           => $one['spend'] ?? 0,
                                'purchase'        => $ac['purchase'] ?? 0,
                                'purchase_0'      => $dayAc['purchase'] ?? 0,
                                'install'         => $ac['mobile_app_install'] ?? 0,
                                'install_0'       => $dayAc['mobile_app_install'] ?? 0,
                                'revenue'         => ($av['app_custom_event.fb_mobile_purchase'] ?? 0) + ($av['offsite_conversion.fb_pixel_purchase'] ?? 0),
                                'revenue_0'       => ($dayV['app_custom_event.fb_mobile_purchase'] ?? 0) + ($dayV['offsite_conversion.fb_pixel_purchase'] ?? 0),
                                'pixel_revenue'   => $av['offsite_conversion.fb_pixel_purchase'] ?? 0,
                                'pixel_revenue_0' => $dayV['offsite_conversion.fb_pixel_purchase'] ?? 0,
                                'uniq_purchase'   => 0,
                                'impressions'     => $one['impressions'] ?? 0,
                                'cpm'             => $one['cpm'] ?? 0,
                                'clicks'          => $one['clicks'] ?? 0,
                                'pixel_registration'  => $ac['offsite_conversion.fb_pixel_complete_registration'] ?? 0,
                                'mobile_registration' => $ac['app_custom_event.fb_mobile_complete_registration'] ?? 0,
                            ];
                            if (!array_sum($update)) {
                                continue;
                            }
                            $dataDate = strtotime($date . ' ' . substr($one['hourly_stats_aggregated_by_advertiser_time_zone'], 0, 8));
                            $userDate = Tool::getTodayDateWithTimeZone(
                                Tool::get($changeTimezone, $platformMap[$sid], $timezone),
                                $timezone,
                                'YmdH',
                                $dataDate
                            );
                            FacebookHourAdSetData::getIns()->updateOrInsert(
                                ['event_date_hour' => $userDate, 'sid' => $sid],
                                $update
                            );
                        }
                    }
                    usleep(20000);
                }
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
            $rs = FacebookHourAdSetData::getIns()
                ->from(FacebookHourAdSetData::getIns()->getTableName() . ' as setsData')
                ->leftJoin(FacebookSet::getIns()->getTableName() . ' as sets', 'sets.sid', 'setsData.sid')
                ->select(
                    'setsData.sid',
                    'sets.book_id',
                    'sets.platform',
                    'sets.link_id',
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
                        ['sid' => $row['sid'], 'event_date' => $date, 'from' => PublicService::PLATFORM_TYPE_FACEBOOK],
                        $row
                    );
                }
            }
        }
    }
}
