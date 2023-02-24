<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookCampaign;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Facebook\TrafficList;
use App\Services\ApiService;
use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncMissAdInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncMissAdInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步缺失的广告系列/广告组/广告参数';


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
            $this->main();
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main()
    {
        [$bmId, $token] = BMService::getRandomTrafficToken();
        echo $token . PHP_EOL;
        $ids = FacebookDayAdSetData::getIns()
            ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setsData')
            ->leftJoin(FacebookSet::getIns()->getTableName() . ' as sets', 'sets.sid', 'setsData.sid')
            ->whereNull('sets.sid')
            ->groupBy(['setsData.sid'])
            ->where('event_date', '>=', 20220101)
            ->pluck('setsData.sid')
            ->toArray();

        echo 'count->' . count($ids) . PHP_EOL;

        $params = [
            'fields' => 'account_id,campaign{name,smart_promotion_type,daily_budget,objective,status,created_time,start_time,stop_time,updated_time},' .
                'daily_budget,created_time,start_time,end_time,updated_time,optimization_goal,' .
                'promoted_object,is_dynamic_creative,name,status,lifetime_budget,bid_strategy'
        ];
        $idArr = array_chunk($ids, CurlService::RESPONSE_LIMIT_TIMES_MAX / 4);
        $account = FacebookAdAccount::getIns()->select('aid', 'platform')->get()->toArray();
        $aMap = array_column($account, null, 'aid');
        $curTimeZone = config('app.timezone_offset');
        $saveCampaign = [];
        $oldCampaignId = [];
        foreach ($idArr as $one) {
            dump($one);
            $params['ids'] = implode(',', $one);
            [$status, $curlMsg, $res] = CurlService::getIns()->curlRequest(
                '',
                $params,
                $token
            );
            dump($res);
            if (Tool::get($res, 'error')) {

                $oldCampaignId = array_merge($oldCampaignId, $one);
                continue;
            }
            if (empty($res)) {
                continue;
            }

            foreach ($res as $row) {
                $aid = $row['account_id'];
                $platform = $aMap[$aid]['platform'] ?? -1;
                $campaign = $row['campaign'];
                $isMatched = preg_match('/(?<={).+(?=})/', $campaign['name'], $matches);
                if (!$isMatched) {
                    // continue;
                    $linkId = [0];
                    $bookId = [0];
                    $explode = [];
                } else {
                    $explode = explode('/', $matches[0]);
                    preg_match('/\d+/', $explode[1], $linkId);
                    preg_match('/\d+/', $explode[0], $bookId);
                }
                // 广告系列 START
                try {
                    if (!Tool::get($saveCampaign, $campaign['id'])) {
                        FacebookCampaign::getIns()->updateOrInsert(
                            ['cid' => $campaign['id']],
                            [
                                'aid'           => $aid,
                                'platform'      => $platform,
                                'status'        => $campaign['status'] === 'ACTIVE' ? 1 : 0,
                                'name'          => $campaign['name'] ?? '',
                                'book_id'       => $bookId[0] ?? 0,
                                'link_id'       => $linkId[0] ?? 0,
                                'objective'     => array_search($campaign['objective'], BMService::AD_TYPE_MAP),
                                'user'          => strtolower($explode[2] ?? ''),
                                'union_link_id' => ApiService::buildPlatformLink($platform, $linkId[0] ?? 0),
                                'is_AAA'        => (int)Tool::isAAA($campaign),
                                'obj'           => strtolower(Tool::get($explode, 3)) == 'test' ? 1 : 0 // 广告是否是测试对象

                            ]
                        );
                        $saveCampaign[$campaign['id']] = 1;
                    }
                } catch (\Exception $e) {
                    Log::info("facebook:SyncMissAdInfo -updateOrInsert campaign failed -" . $campaign['id'], [$e->getMessage()]);
                    continue;
                }
                // 广告系列 END
                // 广告组 START
                $isMatched = preg_match('/(?<={).+(?=})/', $row['name'], $matchSet);
                if (!$isMatched) {
                    // continue;
                    $explodeSet = $explode;
                    $setBookId = $bookId;
                    $setLinkId = $linkId;
                } else {
                    $explodeSet = explode('/', $matchSet[0] ?? '');
                    preg_match('/\d+/', $explodeSet[0], $setBookId);
                    preg_match('/\d+/', $explodeSet[1], $setLinkId);
                }

                try {
                    $appId = Tool::get($row['promoted_object'], 'pixel_id') ?
                        0 : Tool::get($row['promoted_object'], 'application_id', 0);
                    if ($appId) {
                        $os = Tool::getOsByUrl(Tool::get(
                            $row['promoted_object'],
                            'object_store_url'
                        )) === 'google' ? 0 : 1;
                    } else {
                        $os = 1;
                    }
                    FacebookSet::getIns()->updateOrInsert(
                        ['sid' => $row['id']],
                        [

                            'cid'           => $campaign['id'] ?? 0,
                            'status'        => $row['status'] === 'ACTIVE' ? 1 : 0,
                            'platform'      => $platform,
                            'aid'           => $aid,
                            'name'          => $row['name'] ?? '',
                            'os'            => $os,
                            'start_time'    => Tool::getTodayDateWithTimeZone(
                                $aMap[$aid]['platform']['timezone_offset_hours_utc'] ?? 8,
                                $curTimeZone,
                                "Y-m-d H:i:s",
                                strtotime($row['start_time'])
                            ),
                            'app_id'        => $appId,
                            'goal'          => array_search(
                                $row['optimization_goal'],
                                FacebookSet::OPTIMIZATION_GOAL_MAP
                            ),
                            'user'          => strtolower($explodeSet[2] ?? ''),
                            'book_id'       => $setBookId[0] ?? 0,
                            'link_id'       => $setLinkId[0] ?? 0,
                            'union_link_id' => ApiService::buildPlatformLink($platform, $setLinkId[0] ?? 0)
                        ]
                    );
                } catch (\Exception $e) {
                    Log::info("facebook:SyncMissAdInfo -updateOrInsert adset failed -" . $row['id']);
                    continue;
                }
            }
            usleep(20000);
        }
        echo json_encode($oldCampaignId) . PHP_EOL;
    }
}
