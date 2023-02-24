<?php

namespace App\Console\Commands\Google;

use App\Models\Stat\DayAdSetsSummaryData;
use App\Models\Google\GoogleDataSnapshot;
use App\Models\Google\GoogleDayAdSetData;
use App\Models\Google\GoogleHourAdSetData;
use App\Models\Google\GoogleSet;
use App\Services\ApiService;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\Facebook\BMService;
use App\Services\QN\BaseService;
use App\Services\RedisService;
use App\Services\Google\AdAccountService;
use App\Services\Google\CurlService;
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
    protected $signature = 'google:SyncSetDataHourly {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分时同步GG广告组的成效';


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
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $dates = $this->syncData($date);

            $this->buildDayData($dates);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }


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

        $fmtDate = date("Y-m-d", strtotime($date));
        // 当前时区
        $curTimeZone = config('app.timezone_offset');
        $eDate = [];
        foreach ($aidRs as $aidInfo) {
            $dateFmt = $isToday ? Tool::getTodayDateWithTimeZone($aidInfo['timezone_offset_hours_utc'], $curTimeZone) :
                $fmtDate;
            $query = "SELECT "
                . "ad_group.id,"
                . "metrics.cost_micros, "
                . "metrics.conversions_value, "
                . "metrics.impressions, "
                . "metrics.clicks, "
                . "metrics.conversions, "
                . "segments.hour "
                . "FROM ad_group "
                . "WHERE segments.date = '{$dateFmt}' "
                . "AND metrics.cost_micros > 0";
            $eDate[$dateFmt] = 1;

            $rs = CurlService::getIns()->commonPostFetch($aidInfo['aid'], $query);
            if (empty($rs)) {
                continue;
            }
            $dateTime = strtotime($dateFmt);
            foreach ($rs['results'] as $row) {
                $metrics = $row['metrics'];


                $update = [
                    'spend'         => AdAccountService::getIns()->fmtPrice($metrics['costMicros'] ?? 0),
                    'purchase'      => 0,
                    'install'       => $metrics['conversions'] ?? 0,
                    'revenue'       => AdAccountService::getIns()->fmtPrice($metrics['conversionsValue'] ?? 0),
                    'uniq_purchase' => 0,
                    'impressions'   => $metrics['impressions'] ?? 0,
                    'cpm'           => AdAccountService::getIns()->fmtPrice($metrics['averageCpm'] ?? 0, 1000),
                    'clicks'        => $metrics['clicks'] ?? 0,
                ];

                if (!array_sum($update)) {
                    continue;
                }

                $hour = $row['segments']['hour'];

                $userDate = Tool::getTodayDateWithTimeZone(
                    Tool::get($changeTimezone, $aidInfo['platform'], $aidInfo['timezone_offset_hours_utc']),
                    $aidInfo['timezone_offset_hours_utc'],
                    'YmdH',
                    $dateTime + $hour * 3600
                );
                GoogleHourAdSetData::getIns()->updateOrInsert(
                    [
                        'event_date_hour' => $userDate,
                        'sid'             => $row['adGroup']['id']
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
            $rs = GoogleHourAdSetData::getIns()
                ->from(GoogleHourAdSetData::getIns()->getTableName() . ' as setsData')
                ->rightJoin(GoogleSet::getIns()->getTableName() . ' as sets', 'sets.sid', 'setsData.sid')
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
                        ['sid' => $row['sid'], 'event_date' => $date, 'from' => PublicService::PLATFORM_TYPE_GOOGLE],
                        $row
                    );
                }
            }
        }
    }
}
