<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookCampaign;
use App\Models\Facebook\FacebookAd;
use App\Models\Facebook\FacebookSet;
use App\Models\Stat\AbroadLink;
use App\Services\ApiService;
use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
use App\Services\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncAdInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncAdInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步广告系列/广告组/广告参数';


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
            $GLOBALS['co'] = $row['id'];
            $this->main($row);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($coInfo)
    {
        $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');
        if (empty($tokenMap)) {
            return;
        }
        $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id', 'timezone_offset_hours_utc']);


        $cacheName = Tool::fmtCoIdKey('android_link_list_' . date("W"));
        $androidCache = RedisService::getIns()->get($cacheName);
        $androidList = [];
        if ($androidCache) {
            $androidList = json_decode($androidCache, true);
        }
        $dateStartTime = strtotime(date("Ymd", strtotime("-7 days")));
        $dateEndTime = time();

        $params = [
            'filtering' => [
                [
                    'field'    => 'adset.updated_time',
                    'operator' => 'IN_RANGE',
                    'value'    => [$dateStartTime, $dateEndTime]
                ]
            ],
            'limit'     => 200,
            'fields'    => 'account_id,name,smart_promotion_type,daily_budget,objective,status,created_time,start_time,stop_time,updated_time,' .
                'adsets{daily_budget,created_time,start_time,end_time,updated_time,optimization_goal,' .
                'promoted_object,is_dynamic_creative,name,objective,status,' .
                'lifetime_budget,bid_strategy,targeting},' .
                'ads{name,creative{object_story_spec{video_data{video_id},link_data{image_hash}}},' .
                'adset{is_dynamic_creative},created_time}'
        ];

        $illegalName = [];
        $linkIdList = [];
        foreach ($aidRs as $aidInfo) {
            $partIllegal = [];
            if (!isset($tokenMap[$aidInfo['bm_id']])) {
                continue;
            }
            $token = Tool::get($tokenMap, $aidInfo['bm_id']) ?: config('facebook.access_token');
            $after = '';
            $aid = $aidInfo['aid'];
            $platform = $aidInfo['platform'];
            $curTimeZone = config('app.timezone_offset');
            do {
                $params['after'] = $after;
                if (!$after) {
                    unset($params['after']);
                }

                [$status, $curlMsg, $res] = CurlService::getIns()->curlRequest(
                    Tool::fmtAid($aid) . "/campaigns",
                    $params,
                    $token
                );
                if (!Tool::get($res, 'data')) {
                    break;
                }

                foreach ($res['data'] as $row) {
                    // 填充campaign数据
                    $isMatched = preg_match('/(?<={).+(?=})/', $row['name'], $matches);
                    if (!$isMatched) {
                        $partIllegal['campaign'][] = $row['name'] . "(" . $row['id'] . ")";
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
                        FacebookCampaign::getIns()->updateOrInsert(
                            ['cid' => $row['id']],
                            [
                                'aid'           => $aid,
                                'platform'      => $platform,
                                'status'        => $row['status'] === 'ACTIVE' ? 1 : 0,
                                'name'          => $row['name'] ?? '',
                                'book_id'       => $bookId[0] ?? 0,
                                'link_id'       => $linkId[0] ?? 0,
                                'objective'     => array_search($row['objective'], BMService::AD_TYPE_MAP),
                                'user'          => strtolower($explode[2] ?? ''),
                                'union_link_id' => ApiService::buildPlatformLink($platform, $linkId[0] ?? 0),
                                'is_AAA'        => (int)Tool::isAAA($row),
                                'obj'           => strtolower(Tool::get($explode, 3)) == 'test' ? 1 : 0 // 广告是否是测试对象

                            ]
                        );
                    } catch (\Exception $e) {
                        Log::info("facebook:SyncAdInfo -updateOrInsert campaign failed -" . $row['id'], [$e->getMessage()]);
                        continue;
                    }
                    // 广告系列 END
                    // 广告组 START
                    if (Tool::get($row, 'adsets')) {
                        foreach ($row['adsets']['data'] as $adSetInfo) {
                            $isMatched = preg_match('/(?<={).+(?=})/', $adSetInfo['name'], $matchSet);
                            if (!$isMatched) {
                                $partIllegal['set'][] = $adSetInfo['name'] . "(" . $adSetInfo['id'] . ")";
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
                                $unLinkId = ApiService::buildPlatformLink($platform, $setLinkId[0] ?? 0);
                                $appId = Tool::get($adSetInfo['promoted_object'], 'pixel_id') ?
                                    0 : Tool::get($adSetInfo['promoted_object'], 'application_id', 0);
                                if ($appId) {
                                    $os = Tool::getOsByUrl(Tool::get(
                                        $adSetInfo['promoted_object'],
                                        'object_store_url'
                                    )) === 'google' ? 0 : 1;
                                } elseif (in_array($unLinkId, $androidList)) {
                                    $os = 0;
                                } else {
                                    $os = 1;
                                    $linkIdList[] = $unLinkId;
                                }
                                FacebookSet::getIns()->updateOrInsert(
                                    ['sid' => $adSetInfo['id']],
                                    [

                                        'cid'           => $row['id'] ?? 0,
                                        'status'        => $adSetInfo['status'] === 'ACTIVE' ? 1 : 0,
                                        'platform'      => $platform,
                                        'aid'           => $aid,
                                        'name'          => $adSetInfo['name'] ?? '',
                                        'os'            => $os,
                                        'start_time'    => Tool::getTodayDateWithTimeZone(
                                            $aidInfo['timezone_offset_hours_utc'],
                                            $curTimeZone,
                                            "Y-m-d H:i:s",
                                            strtotime($adSetInfo['start_time'])
                                        ),
                                        'app_id'        => $appId,
                                        'goal'          => array_search(
                                            $adSetInfo['optimization_goal'],
                                            FacebookSet::OPTIMIZATION_GOAL_MAP
                                        ),
                                        'user'          => strtolower($explodeSet[2] ?? ''),
                                        'book_id'       => $setBookId[0] ?? 0,
                                        'link_id'       => $setLinkId[0] ?? 0,
                                        'union_link_id' => $unLinkId,
                                        'sex'           => isset($adSetInfo['targeting']['genders']) ?
                                            end($adSetInfo['targeting']['genders']) : 0
                                    ]
                                );
                            } catch (\Exception $e) {
                                Log::info("facebook:SyncAdInfo -updateOrInsert adset failed -" . $adSetInfo['id']);
                                continue;
                            }
                        }
                    }
                    // 广告组 END
                    // 广告START
                    if (Tool::get($row, 'ads')) {
                        foreach ($row['ads']['data'] as $adInfo) {
                            try {
                                FacebookAd::getIns()->updateOrInsert(
                                    ['ad_id' => $adInfo['id']],
                                    [
                                        'is_dynamic' => (int)$adInfo['adset']['is_dynamic_creative'],
                                        'sid'        => $adInfo['adset']['id'],
                                        'name'       => $adInfo['name'],
                                        'cid'        => $row['id'] ?? 0,
                                        'hash_id'    => $adInfo['creative']['object_story_spec']['video_data']['video_id']
                                            ?? ($adInfo['creative']['object_story_spec']['link_data']['image_hash'] ?? ''),
                                        'platform'   => $platform,
                                        'aid'        => $aid
                                    ]
                                );
                            } catch (\Exception $e) {
                                Log::info("facebook:SyncAdInfo -updateOrInsert ad failed -" . $adInfo['id'], [$e->getMessage()]);
                                continue;
                            }
                        }
                    }
                    // 广告END
                }


                if (Tool::get($res['paging'], 'next')) {
                    $after = Tool::get($res['paging']['cursors'], 'after');
                } else {
                    $after = '';
                }
                usleep(20000);
            } while ($after);
            if (!empty($partIllegal)) {
                $partIllegal['aid'] = $aid;
                $illegalName[] = $partIllegal;
            }
        }

        if ($linkIdList) {
            $linkRs = AbroadLink::getIns()->listByCond(
                ['system' => 0, 'union_link_id' => ['in', $linkIdList]],
                ['union_link_id']
            );
            if ($linkRs) {
                $linkIds = array_column($linkRs, 'union_link_id');
                FacebookSet::getIns()->updateByCond(['union_link_id' => ['in', $linkIds]], ['os' => 0]);
                $linkIds = array_values(array_unique(array_merge($linkIds, $androidList)));
                RedisService::getIns()->set($cacheName, json_encode($linkIds));
                Log::info('归到安卓像素链接', $linkIds);
            }
        }


        if ($illegalName) {
            $msg = '';
            foreach ($illegalName as $one) {
                $msg .= '广告账户ID: ' . $one['aid'] . PHP_EOL;
                if (Tool::get($one, 'campaign')) {
                    $msg .= ' 广告系列: ' . PHP_EOL;
                    foreach ($one['campaign'] as $part) {
                        $msg .= ' ' . $part . PHP_EOL;
                    }
                }

                if (Tool::get($one, 'set')) {
                    $msg .= ' 广告组: ' . PHP_EOL;
                    foreach ($one['set'] as $part) {
                        $msg .= ' ' . $part . PHP_EOL;
                    }
                }
            }
            AlarmService::dingdingSend(
                '无法识别以下投放名称,请及时更新' . PHP_EOL .
                $msg,
                [],
                $coInfo['dingding_secret'],
                $coInfo['dingding_keyword']
            );
        }
    }
}
