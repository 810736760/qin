<?php

namespace App\Console\Commands\Tiktok\Sync;

use App\Definition\TikTokColumn;
use App\Models\Stat\AbroadLink;
use App\Models\Tiktok\TikTokAd;
use App\Models\Tiktok\TikTokCampaign;
use App\Models\Tiktok\TiktokDayAdSetData;
use App\Models\Tiktok\TikTokSet;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Tiktok\AdAccountService;
use App\Services\Tiktok\CurlService;
use App\Services\RedisService;
use App\Services\Tiktok\InsightsService;
use App\Services\Tiktok\TTSdkService;
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
    protected $signature = 'tiktok:SyncCampaignInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步广告系列/广告组参数';


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
            $dateStartTime = date("Y-m-d H:i:s", strtotime("-7 days"));
            $cacheName = Tool::fmtCoIdKey('tt_android_link_list_' . date("W"));
            $androidCache = RedisService::getIns()->get($cacheName);
            $androidList = [];
            if ($androidCache) {
                $androidList = json_decode($androidCache, true);
            }
            $androidLinkIds = [];
            foreach ($aidRs as $one) {
                $this->main($one, $dateStartTime, InsightsService::LEVEL_CAMPAIGN);
                $this->main($one, $dateStartTime, InsightsService::LEVEL_ADSET, $androidList, $androidLinkIds);
                $this->main($one, $dateStartTime, InsightsService::LEVEL_AD);
            }

            if ($androidLinkIds) {
                $linkRs = AbroadLink::getIns()->listByCond(
                    ['system' => 0, 'union_link_id' => ['in', $androidLinkIds]],
                    ['union_link_id']
                );

                if ($linkRs) {
                    $linkIds = array_column($linkRs, 'union_link_id');
                    TikTokSet::getIns()->updateByCond(['union_link_id' => ['in', $linkIds]], ['os' => 0]);
                    $androidList = array_values(array_unique(array_merge($linkIds, $androidList)));
                    RedisService::getIns()->set($cacheName, json_encode($linkIds));
                    Log::info('归到TT安卓像素链接', $androidList);
                }
            }
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($aidInfo, $dateStartTime, $level, $androidCache = [], &$linkIds = [])
    {
        $rs = InsightsService::getIns()->getMatchId(
            $aidInfo['aid'],
            $level,
            ['creation_filter_start_time' => $dateStartTime],
            1,
            1000
        );


        if ($rs['code']) {
            return;
        }
        $platform = $aidInfo['platform'];
        $name = InsightsService::getIns()->fmtLightPathByLevel($level, '_name');
        $id = InsightsService::getIns()->fmtLightPathByLevel($level, '_id');
        foreach ($rs['data']['list'] as $row) {
            if ($level == InsightsService::LEVEL_AD) {
                if ($row["ad_format"] == 'SINGLE_VIDEO') {
                    $hashId = $row['video_id'] ?? 0;
                } else {
                    $hashId = $row['image_ids'][0] ?? 0;
                }
                TikTokAd::getIns()->updateOrInsert(
                    ['ad_id' => $row[$id]],
                    [
                        'cid'        => $row['campaign_id'] ?? 0,
                        'sid'        => $row['adgroup_id'] ?? 0,
                        'platform'   => $platform,
                        'aid'        => $aidInfo['aid'],
                        'name'       => $row[$name] ?? '',
                        'is_dynamic' => $row['is_aco'] ? 1 : 0,
                        'hash_id'    => $hashId,
                    ]
                );
            } else {
                $isMatched = preg_match('/(?<={).+(?=})/', $row[$name], $matches);
                if (!$isMatched) {
                    continue;
                }
                $explode = explode('/', $matches[0]);
                preg_match('/\d+/', $explode[1], $linkId);
                preg_match('/\d+/', $explode[0], $bookId);

                if ($level == InsightsService::LEVEL_CAMPAIGN) {
                    TikTokCampaign::getIns()->updateOrInsert(
                        ['cid' => $row[$id]],
                        [
                            'aid'           => $aidInfo['aid'],
                            'platform'      => $platform,
                            'status'        => $row['operation_status'] === 'ENABLE' ? 1 : 0,
                            'name'          => $row[$name] ?? '',
                            'book_id'       => $bookId[0] ?? 0,
                            'link_id'       => $linkId[0] ?? 0,
                            'objective'     => array_search($row['objective_type'], TTSdkService::AD_TYPE_MAP),
                            'user'          => strtolower($explode[2] ?? ''),
                            'union_link_id' => ApiService::buildPlatformLink($platform, $linkId[0] ?? 0),
                            'obj'           => strtolower(Tool::get($explode, 3)) == 'test' ? 1 : 0 // 广告是否是测试对象
                        ]
                    );
                } elseif ($level == InsightsService::LEVEL_ADSET) {
                    $uLinkId = ApiService::buildPlatformLink($platform, $linkId[0] ?? 0);
                    $os = Tool::get($row, 'min_android_version') ? 0 : 1;
                    if (in_array($uLinkId, $androidCache)) {
                        $os = 0;
                    }
                    if ($os == 1) {
                        $linkIds[] = $uLinkId;
                    }

                    TikTokSet::getIns()->updateOrInsert(
                        ['sid' => $row[$id]],
                        [

                            'cid'           => $row['campaign_id'] ?? 0,
                            'status'        => $row['operation_status'] === 'ENABLE' ? 1 : 0,
                            'platform'      => $platform,
                            'aid'           => $aidInfo['aid'],
                            'name'          => $row[$name] ?? '',
                            'os'            => $os,
                            'app_id'        => $row['app_id'] ?? 0,
                            'goal'          => array_search(
                                $row['optimization_goal'],
                                TikTokSet::OPTIMIZATION_GOAL_MAP
                            ),
                            // 'area'    => '',
                            'user'          => strtolower($explode[2] ?? ''),
                            'link_id'       => $linkId[0] ?? 0,
                            'union_link_id' => $uLinkId,
                            'book_id'       => $bookId[0] ?? 0,
                            'sex'           => array_search(
                                $row['gender'],
                                TikTokSet::GENDER_TYPE
                            )
                        ]
                    );
                }
            }
        }
    }
}
