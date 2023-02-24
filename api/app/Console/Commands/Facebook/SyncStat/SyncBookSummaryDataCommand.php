<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookCampaign;
use App\Models\Facebook\FacebookAd;
use App\Models\Facebook\FacebookDailyBookSummaryData;
use App\Models\Facebook\FacebookDataSnapshot;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Services\ApiService;
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

class SyncBookSummaryDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncBookSummaryData {start_date?} {end_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取其他项目书籍统计数据';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $args = $this->arguments();
        $start = Tool::get($args, 'start_date');
        $end = Tool::get($args, 'end_date');
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->main($start, $end);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($start, $end)
    {
        $params = [];
        if (!empty($start)) {
            $params = [
                'start_date' => $start,
                'end_date'   => $end
            ];
        }
        $systemInfos = CompanyService::systemInfo();
        foreach ($systemInfos as $info) {
            $rs = ApiService::getIns()->listBookSummary($info['platform'], $params);
            if (empty(Tool::get($rs, 'list'))) {
                continue;
            }
            foreach ($rs['list'] as $row) {
                $row['platform'] = $info['platform'];
                unset($row['created_at'], $row['updated_at'], $row['id']);
                FacebookDailyBookSummaryData::getIns()->updateOrInsert(
                    ['event_date' => $row['event_date'], 'type' => $row['type'], 'platform' => $row['platform']],
                    $row
                );
            }
        }
    }
}
