<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Google\GoogleDayAdSetData;
use App\Models\Google\GoogleSet;
use App\Models\Report\MonthlyReport;
use App\Models\Tiktok\TiktokDayAdSetData;
use App\Models\Tiktok\TikTokSet;
use App\Services\CompanyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatMonthlySpendCommand extends Command
{
    protected $signature = 'StatMonthlySpendCommand {date?}';

    protected $description = '统计月度花费';


    public function handle()
    {
        \Log::info($this->description . '开始');
        $startTime = microtime(true);

        $args = $this->arguments();
        $date = Tool::get($args, 'date') ?: date("Ymd");
        $startDate = Carbon::parse($date)->firstOfMonth()->format('Ymd');
        $endDate = Carbon::parse($date)->endOfMonth()->format('Ymd');

        $timeStamp = strtotime($date);

        $year = date('Y', $timeStamp);
        $month = date('m', $timeStamp);
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $this->main($startDate, $endDate, $year, $month);
        }


        Log::info($this->description . '结束,耗时：' . (microtime(true) - $startTime));
    }

    public function main($startDate, $endDate, $year, $month)
    {
        $facebookData = $this->getDataFromPlatform(FacebookDayAdSetData::getIns(), FacebookSet::getIns(), $startDate, $endDate);
        $googleData = $this->getDataFromPlatform(GoogleDayAdSetData::getIns(), GoogleSet::getIns(), $startDate, $endDate);
        $tiktokData = $this->getDataFromPlatform(TiktokDayAdSetData::getIns(), TikTokSet::getIns(), $startDate, $endDate);


        if (empty($facebookData) && empty($googleData) && $tiktokData) {
            return;
        }

        $rs = [];
        $this->fmtData($facebookData, $rs);
        $this->fmtData($googleData, $rs);
        $this->fmtData($tiktokData, $rs);

        foreach ($rs as $key => $row) {
            $keyArr = explode("_", $key);
            MonthlyReport::getIns()->updateOrInsert(
                [
                    'year'     => $year,
                    'month'    => $month,
                    'platform' => $keyArr[0],
                    'system'   => $keyArr[1] + 1,
                ],
                [
                    'spend' => $row,
                    'ack'   => 0
                ]
            );
        }
    }

    public function getDataFromPlatform($dateModel, $setModel, $startDate, $endDate)
    {
        return $dateModel
            ->from($dateModel->getTableName() . ' as setsData')
            ->leftJoin($setModel->getTableName() . ' as sets', 'sets.sid', 'setsData.sid')
            ->select(
                'platform',
                'os',
                DB::Raw('sum(spend) as spend')
            )
            ->whereBetween('event_date', [$startDate, $endDate])
            ->groupBy(['platform', 'os'])
            ->get()
            ->toArray();
    }

    public function fmtData($source, &$data)
    {
        foreach ($source as $one) {
            if (is_null($one['platform'])) {
                continue;
            }
            $key = implode("_", [$one['platform'], $one['os']]);
            $data[$key] = ($data[$key] ?? 0) + $one['spend'];
        }
    }
}
