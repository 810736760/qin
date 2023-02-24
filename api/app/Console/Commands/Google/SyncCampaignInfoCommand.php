<?php

namespace App\Console\Commands\Google;

use App\Models\Google\GoogleCampaign;
use App\Models\Google\GoogleDayAdSetData;
use App\Models\Google\GoogleSet;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Google\AdAccountService;
use App\Services\Google\BaseService;
use App\Services\Google\CurlService;
use App\Services\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncCampaignInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:SyncCampaignInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步GG广告系列/广告组参数';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id']);
            $dateStartTime = date("Y-m-d", strtotime("-1 days"));
            // $dateStartTime = '2022-07-18';
            foreach ($aidRs as $one) {
                $groupSql = "SELECT "
                    . "ad_group.id,"
                    . "ad_group.name,"
                    . "campaign.id,"
                    . "campaign.end_date,"
                    . "campaign.start_date,"
                    . "campaign.status,"
                    . "campaign.ad_serving_optimization_status,"
                    . "campaign.app_campaign_setting.bidding_strategy_goal_type,"
                    . "campaign.app_campaign_setting.app_id,"
                    . "campaign.app_campaign_setting.app_store,"
                    . "ad_group.status,"
                    . "campaign.payment_mode,"
                    . "campaign.name "
                    . "FROM "
                    . "ad_group "
                    . "WHERE "
                    . "campaign.start_date >= '{$dateStartTime}'";
                $this->main($one, $groupSql);
            }
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($aidInfo, $sql)
    {
        $rs = CurlService::getIns()->commonPostFetch($aidInfo['aid'], $sql);
        if (empty($rs)) {
            return;
        }

        foreach ($rs['results'] as $row) {
            $this->doCampaign($row, $aidInfo['aid'], $aidInfo['platform']);
            $this->doGroup($row, $aidInfo['aid'], $aidInfo['platform']);
        }
    }

    public function doCampaign($row, $aid, $platform)
    {
        global $useCampaignId;
        if (Tool::get($useCampaignId, $row['campaign']['id'])) {
            echo $row['campaign']['id'] . '->hit' . PHP_EOL;
            return;
        }
        $useCampaignId[$row['campaign']['id']] = 1;
        $isMatched = preg_match('/(?<={).+(?=})/', $row['campaign']['name'] ?? '', $matches);
        if (!$isMatched) {
            return;
        }
        $explode = explode('/', $matches[0]);
        preg_match('/\d+/', $explode[1], $linkId);
        preg_match('/\d+/', $explode[0], $bookId);
        $one = $row['campaign'];
        GoogleCampaign::getIns()->updateOrInsert(
            ['cid' => $one['id']],
            [
                'aid'           => $aid,
                'platform'      => $platform,
                'status'        => $one['status'] === 'ENABLED' ? 1 : 0,
                'name'          => $one['name'] ?? '',
                'book_id'       => $bookId[0] ?? 0,
                'link_id'       => $linkId[0] ?? 0,
                'objective'     => array_search($one['adServingOptimizationStatus'], BaseService::AD_TYPE_MAP),
                'user'          => strtolower($explode[2] ?? ''),
                'union_link_id' => ApiService::buildPlatformLink($platform, $linkId[0] ?? 0),
                'obj'           => strtolower(Tool::get($explode, 3)) == 'test' ? 1 : 0 // 广告是否是测试对象
            ]
        );
    }

    public function doGroup($row, $aid, $platform)
    {
        $isMatched = preg_match('/(?<={).+(?=})/', $row['adGroup']['name'], $matches);
        if (!$isMatched) {
            return;
        }
        $explode = explode('/', $matches[0]);
        preg_match('/\d+/', $explode[1], $linkId);
        preg_match('/\d+/', $explode[0], $bookId);
        $one = $row['adGroup'];
        $campaign = $row['campaign'];
        GoogleSet::getIns()->updateOrInsert(
            ['sid' => $one['id']],
            [

                'cid'           => $campaign['id'] ?? 0,
                'status'        => $one['status'] === 'ENABLED' ? 1 : 0,
                'platform'      => $platform,
                'aid'           => $aid,
                'name'          => $one['name'] ?? '',
                'os'            => ($campaign['appCampaignSetting']['appStore'] ?? '') == 'GOOGLE_APP_STORE' ? 0 : 1,
                'app_id'        => $campaign['appCampaignSetting']['appId'] ?? '',
                'goal'          => array_search(
                    $campaign['paymentMode'],
                    BaseService::OPTIMIZATION_GOAL_MAP
                ),
                'link_id'       => $linkId[0] ?? 0,
                'user'          => strtolower($explode[2] ?? ''),
                'union_link_id' => ApiService::buildPlatformLink($platform, $linkId[0] ?? 0),
                'book_id'       => $bookId[0] ?? 0
            ]
        );
    }
}
