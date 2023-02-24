<?php

namespace App\Console\Commands\Google;

use App\Models\Google\GoogleDayAdSetData;
use App\Models\Google\GoogleSet;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Google\AdAccountService;
use App\Services\Google\CurlService;
use App\Services\RedisService;
use App\Services\Google\InsightsService;
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
    protected $signature = 'google:SyncSetData {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步GG广告组的成效';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        $args = $this->arguments();
        $date = Tool::get($args, 'date', date("Y-m-d"));
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
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
        $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id', 'timezone_offset_hours_utc']);

        // 当前时区
        $curTimeZone = config('app.timezone_offset');

        foreach ($aidRs as $aidInfo) {
            $dateFmt = $date ?: Tool::getTodayDateWithTimeZone($aidInfo['timezone_offset_hours_utc'], $curTimeZone);
            $query = "SELECT "
                . "ad_group.id,"
                . "metrics.cost_micros, "
                . "metrics.conversions_value, "
                . "metrics.impressions, "
                . "metrics.clicks, "
                . "metrics.conversions "
                . "FROM ad_group "
                . "WHERE segments.date = '{$dateFmt}' "
                . "AND metrics.cost_micros > 0";


            $rs = CurlService::getIns()->commonPostFetch($aidInfo['aid'], $query);
            if (empty($rs)) {
                continue;
            }


            $fmtDate = Tool::fmtDateString($dateFmt);
            foreach ($rs['results'] as $row) {
                $sid = $row['adGroup']['id'];
                $metrics = $row['metrics'];
                $update = [
                    'sid'           => $sid,
                    'spend'         => AdAccountService::getIns()->fmtPrice($metrics['costMicros'] ?? 0),
                    'purchase'      => 0,
                    'install'       => $metrics['conversions'] ?? 0,
                    'revenue'       => AdAccountService::getIns()->fmtPrice($metrics['conversionsValue'] ?? 0),
                    'uniq_purchase' => 0,
                    'impressions'   => $metrics['impressions'] ?? 0,
                    'cpm'           => AdAccountService::getIns()->fmtPrice($metrics['averageCpm'] ?? 0, 1000),
                    'clicks'        => $metrics['clicks'] ?? 0,
                ];
                GoogleDayAdSetData::getIns()->updateOrInsert(
                    ['event_date' => $fmtDate, 'sid' => $sid],
                    $update
                );
            }
        }
    }
}
