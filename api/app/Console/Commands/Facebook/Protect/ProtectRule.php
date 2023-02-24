<?php

namespace App\Console\Commands\Facebook\Protect;

use App\Definition\Filter;
use App\Helper\AlarmHelper;
use App\Helper\Tool;
use App\Mail\FacebookEmailShipped;
use App\Models\Admin_Manager;
use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookCampaign;
use App\Models\Facebook\FacebookAdLevelMap;
use App\Models\Facebook\FacebookSet;
use App\Models\Facebook\FacebookDailyBookSummaryData;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\UserAccountsMap;
use App\Models\Protect\ProtectResult;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
use App\Services\Facebook\ProtectService;
use App\Services\User\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use \App\Models\Protect\ProtectRule as ruleModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class ProtectRule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:ProtectRule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '投放预警';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */

    public function handle()
    {
        $args = $this->arguments();
        \Log::info(date('H:i:s') . ":预警开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                // 只开放主公司
                continue;
            }
            $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');
            if (empty($tokenMap)) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            // $this->main($args, $row);
            $this->doSafelyMain($row, $tokenMap);
        }
        $useTime = microtime(true) - $startTime;
        \Log::info("预警结束 use time " . $useTime, $args);
    }

    public function main($args, $coInfo)
    {

        // 1、获取所有运行的规则
        $rs = ruleModel::getIns()
            ->from(ruleModel::getIns()->getTableName() . ' as rule')
            ->leftJoin('admin_manager as admin', 'admin.id', 'rule.uid')
            ->select(
                'rule.*',
                DB::Raw("LOWER(admin.code) as code"),
                'admin.tel'
            )
            ->where('rule.status', ruleModel::TYPE_RUNNING)
            ->where('admin.code', "!=", '')
            ->get()
            ->toArray();

        if (empty($rs)) {
            \Log::info("无活跃的规则", $coInfo);
            return;
        }
        $uidMap = array_column($rs, null, 'uid');
        $uids = array_keys($uidMap);
        // 获取所有账户
        $res = UserAccountsMap::getIns()
            ->from(UserAccountsMap::getIns()->getTableName() . ' as map')
            ->leftJoin(FacebookAdAccount::getIns()->getTableName() . ' as account', 'map.aid', 'account.aid')
            ->select('account.aid', 'map.uid', 'timezone_offset_hours_utc')
            ->whereIn('map.uid', $uids)
            ->where('account.is_active', 1)
            ->get()
            ->toArray();
        if (empty($res)) {
            dump("无活跃的广告账户", $coInfo);
            return;
        }


        $accountInfo = [];
        foreach ($res as $row) {
            $accountInfo[$row['uid']][$row['timezone_offset_hours_utc']][] = $row['aid'];
        }
        // 获取今日份ios数据
        // $iosTodayData = FacebookDailyBookSummaryData::getIns()
        //     ->select(
        //         'book_info'
        //     )
        //     ->where('event_date', $curDate)
        //     ->where('type', 1)
        //     ->get()
        //     ->toArray();
        // if (!empty($iosTodayData)) {
        //     $iosTodayData = json_decode($iosTodayData[0]['book_info'], true);
        // }


        foreach ($rs as $row) {
            $filtering = json_decode($row['filtering'], true);
            if (!$filtering['os']) {
                // Android
                if (!Tool::get($accountInfo, $row['uid'])) {
                    \Log::info(sprintf("%d（%d）没有广告账户", $row['uid'], $row['id']), $args);
                    continue;
                }
                // 根据广告账户分时区统计
                foreach ($accountInfo[$row['uid']] as $timezone => $aids) {
                    $this->dealOne($row, $timezone, $aids, $uidMap[$row['uid']], $coInfo, $filtering);
                }
            }
            // elseif ($iosTodayData && Tool::get($iosTodayData, $filtering['book_id'] ?? 0)) {
            //     $this->dealOsOne($row, $iosTodayData[$filtering['book_id']], $filtering['book_id']);
            // }
        }
        // 兜底逻辑
        // $this->doSafelyLine($curDate);
        // $this->doSafelyLineV2($curDate);
        // $this->doSafelyLineV3($curDate);
    }

    public function doSafelyMain($coInfo, $tokenMap)
    {
        $aids = AdAccountService::getIns()->listActiveAidByTimezone();
        $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id']);
        $aidRsInfo = array_column($aidRs, null, 'aid');
        foreach ($aids as $timezone => $aidInfo) {
            $curTimeZone = config('app.timezone_offset');
            $fmtDate = Tool::getTodayDateWithTimeZone($timezone, $curTimeZone, 'Ymd');
            $timzones = ($curTimeZone - $timezone);
            $curTime = strtotime($fmtDate) + $timzones * 3600;
            $nowSec = time() - $curTime;
            // $this->doSafelyLineV2($fmtDate, $aidInfo, $coInfo, $nowSec);
            // $this->doSafelyLineV3($fmtDate, $aidInfo, $coInfo, $nowSec);
            $this->doSafelyLineV5($fmtDate, $aidInfo, $coInfo, $nowSec, $timzones, $aidRsInfo, $tokenMap);
            $this->doPixelSafelyMain($fmtDate, $aidInfo, $coInfo, $nowSec, $timzones, $aidRsInfo, $tokenMap);
        }
    }

    public function dealOne($row, $timezone, $accounts, $userInfo, $coInfo, $filtering)
    {
        $condition = json_decode($row['condition'], true);
        // $filtering = json_decode($row['filtering'], true);

        if (empty($filtering) || empty($condition)) {
            return;
        }

        $curTimeZone = config('app.timezone_offset');
        $fmtDate = Tool::getTodayDateWithTimeZone($timezone, $curTimeZone, 'Ymd');
        $nowSec = time() - strtotime($fmtDate) - ($curTimeZone - $timezone) * 3600;
        // get the real condition

        $curCondition = [];
        foreach ($condition as $one) {
            if ($nowSec > $one['time']) {
                continue;
            }
            $curCondition = $one;
            break;
        }
        if (empty($curCondition)) {
            return;
        }
        $accounts = array_intersect($accounts, Tool::getArrayByComma($row['effect_range'])) ?: $accounts;

        // 1、过滤条件
        $insights = FacebookDayAdSetData::getIns()
            ->from(FacebookDayAdSetData::getIns()->getTableName() . " as setData")
            ->leftJoin(FacebookSet::getIns()->getTableName() . " as set", 'set.sid', 'setData.sid')
            ->leftJoin(FacebookCampaign::getIns()->getTableName() . " as camp", 'set.cid', 'camp.cid')
            ->select(
                DB::raw('camp.aid'),
                DB::raw('camp.name as campaign_name'),
                DB::raw('set.name as adset_name'),
                DB::raw('set.cid'),
                DB::raw('set.sid'),
                DB::raw('revenue as purchaseValue'),
                DB::raw('spend'),
                DB::raw('convert(revenue*100/spend,decimal(5,2)) as roi'),
                DB::raw('convert(spend*1000/impressions,decimal(10,2)) as cpm'),
                DB::raw('impressions'),
                DB::raw('convert(clicks*100/impressions,decimal(5,2)) as ctr'),
                DB::raw('clicks'),
                DB::raw('convert(spend/clicks,decimal(10,2)) as cpc'),
                DB::raw('install'),
                DB::raw('IF(install,convert(spend/install,decimal(10,2)),spend) as cpi'),
                DB::raw('purchase'),
                DB::raw('IF(purchase,convert(spend/purchase,decimal(10,2)),spend) as cpp')
            )
            ->where('setData.event_date', $fmtDate)
            ->where('set.user', $userInfo['code'])
            ->where('camp.status', 1)
            ->where('set.status', 1)
            ->whereIn("camp.aid", $accounts)
            ->when(isset($filtering['obj']), function ($query) use ($filtering) {
                $query->where('camp.obj', $filtering['obj']);
            })
            ->when(isset($filtering['app_id']), function ($query) use ($filtering) {
                $query->where("set.app_id", $filtering['app_id']);
            })
            ->when(isset($filtering['os']), function ($query) use ($filtering) {
                $query->where("set.os", $filtering['os']);
            })
            ->when(isset($filtering['goal']), function ($query) use ($filtering) {
                $query->where("set.goal", $filtering['goal']);
            })
            ->when(isset($filtering['area']), function ($query) use ($filtering) {
                $query->where("set.area", $filtering['area']);
            })
            ->when(isset($filtering['sex']), function ($query) use ($filtering) {
                $query->where("set.sex", $filtering['sex']);
            })
            ->get()
            ->toArray();

        if (empty($insights)) {
            return;
        }
        $hit = [];
        $alarmTable = [];
        $curTime = date("Y-m-d H:i:s");
        $opr = [];
        foreach ($insights as $oneRow) {
            $hitCondition = [];
            $hitInsight = [];
            foreach ($curCondition['data'] as $oneCondition) {
                $flag = true;
                $curInsight = [];
                foreach ($oneCondition['data'] as $one) {
                    $item = $one['field'];
                    if (!$item) {
                        $flag = false;
                        break;
                    }
                    switch ($one['operator']) {
                        case Filter::OPR_GREATER_THAN:
                            $flag = $oneRow[$item] > $one['value'];
                            break;
                        case Filter::OPR_LESS_THAN:
                            $flag = $oneRow[$item] < $one['value'];
                            break;
                        case Filter::OPR_GREATER_THAN_AND_EQUAL:
                            $flag = $oneRow[$item] >= $one['value'];
                            break;
                        case Filter::OPR_LESS_THAN_AND_EQUAL:
                            $flag = $oneRow[$item] <= $one['value'];
                            break;
                        default:
                            $flag = false;
                            break;
                        // case Filter::OPR_IN_RANGE:
                        //     $flag = $oneRow[$item] >= $one['value'][0] && $oneRow[$item] <= $one['value'][1];
                        //     break;
                        // case Filter::OPR_NOT_IN_RANGE:
                        //     $flag = $oneRow[$item] < $one['value'][0] || $oneRow[$item] > $one['value'][1];
                        //     break;
                    }

                    if (!$flag) {
                        break;
                    }
                    $curInsight[] = "$item:" . $oneRow[$item] . "(" . $one['value'] . ")";
                }
                if ($flag) {
                    $hitInsight = $curInsight;
                    $hitCondition = $oneCondition;
                    break;
                }
            }
            if (empty($hitCondition)) {
                continue;
            }
            $opr = $hitCondition['opr'];
            $attributes = [
                'ad_set_id' => $oneRow['sid'],
                'rule_id'   => $row['id'],
                'hit_date'  => $fmtDate,
                'hit_time'  => $curCondition['time']
            ];
            if (ProtectResult::getIns()->where($attributes)->exists()) {
                continue;
            }
            $insightsMsg = implode(",", $hitInsight);
            ProtectResult::getIns()->insert(
                array_merge(
                    [
                        'campaign_id' => $oneRow['cid'],
                        'opr'         => json_encode($opr),
                        'insights'    => $insightsMsg
                    ],
                    $attributes
                )
            );
            $hit[] = $oneRow['sid'];

            $oprMsg = '';
            foreach ($opr as $oneOprType) {
                switch ($oneOprType) {
                    case ruleModel::OPR_TYPE_CLOSE:
                        $oprMsg .= "关闭广告组";
                        break;
                }
            }

            $tmp = [
                'set'          => $oneRow['adset_name'] . "(" . $oneRow['sid'] . ")",
                'campaign'     => $oneRow['campaign_name'] . "(" . $oneRow['cid'] . ")",
                'account'      => Tool::fmtAid($oneRow['aid']),
                'insights'     => $insightsMsg,
                'time'         => $curTime,
                'hit_time'     => ProtectService::getIns()->fmtSecond2Time($curCondition['time']),
                'opr'          => $oprMsg,
                'set_url'      => CurlService::buildFbLink(
                    $oneRow['aid'],
                    FacebookAdLevelMap::LEVEL_AD_SET,
                    $oneRow['sid']
                ),
                'campaign_url' => CurlService::buildFbLink(
                    $oneRow['aid'],
                    FacebookAdLevelMap::LEVEL_CAMPAIGN,
                    $oneRow['cid']
                ),
                'account_url'  => CurlService::buildFbLink(
                    $oneRow['aid'],
                    FacebookAdLevelMap::LEVEL_ACCOUNT,
                    0
                )
            ];
            $alarmTable[] = $tmp;
        }

        if (empty($hit) || empty($opr)) {
            return;
        }

        // 执行结果
        foreach ($opr as $oneOprType) {
            switch ($oneOprType) {
                case ruleModel::OPR_TYPE_CLOSE:
                    $update = ['status' => 'PAUSED'];
                    // foreach ($hit as $cid) {
                    //     CurlService::getIns()->curlRequest($cid, $update, '', false, CurlService::REQUEST_TYPE_POST);
                    // }

                    // FacebookSet::getIns()
                    //     ->whereIn("sid", $hit)
                    //     ->update(['status' => 0]);
                    break;
                case ruleModel::OPR_TYPE_ALARM_DINGDING:
                    $content = '';
                    foreach ($alarmTable as $table) {
                        $content .= "广告组:" . $table['set'] . PHP_EOL;
                        $content .= "广告系列:" . $table['campaign'] . PHP_EOL;
                        $content .= "广告账户:" . $table['account'] . PHP_EOL;
                        $content .= "触发成效:" . $table['insights'] . PHP_EOL;
                        $content .= "触发时段:" . $table['hit_time'] . PHP_EOL;
                        $content .= "操作:" . $table['opr'] . PHP_EOL;
                        $content .= "触发时间:" . $table['time'] . PHP_EOL;
                    }
                    AlarmService::dingdingSend(
                        $content,
                        $userInfo['tel'] ? [$userInfo['tel']] : [],
                        $coInfo['dingding_secret'],
                        $coInfo['dingding_keyword']
                    );
                    break;
                case ruleModel::OPR_TYPE_ALARM_MAIL:
                    $mailAddress = AlarmHelper::getCreatorMailAddress([$row['uid']]);
                    Mail::to($mailAddress)->send(
                        new FacebookEmailShipped(
                            $alarmTable,
                            'protect_waring',
                            '规则【' . $row['name'] . '】已触发,请及时查看'
                        )
                    );
                    break;
            }
        }
    }

    public function getAdSetsByBooksAndDate(
        $aids,
        $os,
        $date,
        $bookId,
        $status = '',
        $fields = []
    ) {
        $fields = $fields ?: [
            DB::raw('ad_set.sid'),
            DB::raw('ad_set.name'),
            DB::raw('ad_campaign.cid'),
            DB::raw('ad_campaign.name as campaign_name'),
            DB::raw('ad_campaign.aid')
        ];

        $keyCache = md5(json_encode([$aids, $os, $date, $bookId, $status, $fields]));
        $cache = Redis::get($keyCache);
        if ($cache) {
            return json_decode($cache, true);
        }

        $rs = FacebookDayAdSetData::getIns()
            ->select($fields)
            ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as set_data')
            ->leftJoin(FacebookSet::getIns()->getTableName() . ' as ad_set', 'set_data.sid', 'ad_set.sid')
            ->leftJoin(FacebookCampaign::getIns()->getTableName() . ' as ad_campaign', 'ad_set.cid', 'ad_campaign.cid')
            ->where('ad_set.os', $os)
            ->whereIn('ad_campaign.objective', BMService::GROUP_INSTALL) // 只统计APP_INSTALL
            // 只统计APP_INSTALL
            ->where('set_data.event_date', $date)
            ->where('ad_campaign.obj', 0)
            // ->where('ad_campaign.is_AAA', 0)
            ->when(!empty($aids), function ($query) use ($aids) {
                $query->whereIn('ad_campaign.aid', $aids);
            })
            ->when($status != '', function ($query) use ($status) {
                $query->where('ad_set.status', $status);
            })
            ->when($bookId, function ($query) use ($bookId) {
                $query->where('ad_set.book_id', $bookId);
            })
            ->groupBy([DB::raw('ad_set.sid')])
            ->get()
            ->toArray();
        Redis::setex($keyCache, 480, json_encode($rs));
        return $rs;
    }


    public function getAdPixelSetsByBooksAndDate(
        $aids,
        $date,
        $status = '',
        $fields = []
    ) {
        $fields = $fields ?: [
            DB::raw('ad_set.sid'),
            DB::raw('ad_set.name'),
            DB::raw('ad_campaign.cid'),
            DB::raw('ad_campaign.name as campaign_name'),
            DB::raw('ad_campaign.aid')
        ];

        $keyCache = md5(json_encode([$aids, $date, $status, $fields, 'pixel']));
        $cache = Redis::get($keyCache);
        if ($cache) {
            return json_decode($cache, true);
        }

        $rs = FacebookDayAdSetData::getIns()
            ->select($fields)
            ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as set_data')
            ->leftJoin(FacebookSet::getIns()->getTableName() . ' as ad_set', 'set_data.sid', 'ad_set.sid')
            ->leftJoin(FacebookCampaign::getIns()->getTableName() . ' as ad_campaign', 'ad_set.cid', 'ad_campaign.cid')
            ->whereIn('ad_campaign.objective', BMService::GROUP_CONVERSIONS) // 像素
            // 只统计APP_INSTALL
            ->where('set_data.event_date', $date)
            ->where('ad_set.app_id', 0)
            ->where('ad_campaign.obj', 0)
            // ->where('ad_campaign.is_AAA', 0)
            ->when(!empty($aids), function ($query) use ($aids) {
                $query->whereIn('ad_campaign.aid', $aids);
            })
            ->when($status != '', function ($query) use ($status) {
                $query->where('ad_set.status', $status);
            })
            ->groupBy([DB::raw('ad_set.sid')])
            ->get()
            ->toArray();
        Redis::setex($keyCache, 480, json_encode($rs));
        return $rs;
    }

    // public function dealOsOne($row, $insights, $bookId)
    // {
    //     $condition = json_decode($row['condition'], true);
    //     if (empty($condition)) {
    //         return;
    //     }
    //
    //     $nowDate = date("Ymd");
    //     // get the real condition
    //     $nowSec = time() - strtotime($nowDate);
    //     $curCondition = [];
    //     foreach ($condition as $one) {
    //         if ($nowSec > $one['time']) {
    //             continue;
    //         }
    //         $curCondition = $one;
    //         break;
    //     }
    //     if (empty($curCondition)) {
    //         return;
    //     }
    //
    //     $HKD = DBService::getIns()->getUSD2HKD() * 100; // 港分转美元
    //     $fmtInsights = [];
    //     $fmtInsights['spend'] = round($insights['spend'] / $HKD, 2);
    //     $fmtInsights['install'] = $insights['user']; // 付费安装
    //     $fmtInsights['revenue'] = round($insights['price'] / $HKD, 2);
    //     $fmtInsights['clicks'] = $insights['clicks'] ?? 0;
    //     $fmtInsights['roi'] = round($insights['price'] * 100 / $insights['spend'], 2);
    //     $fmtInsights['cpp'] = $insights['price'] ?
    //         round($insights['spend'] / $insights['price'], 2) : $insights['spend'];
    //     $fmtInsights['cpa'] = $fmtInsights['install'] ?
    //         round($insights['spend'] / $fmtInsights['install'], 2) : $insights['spend'];
    //     $fmtInsights['atv'] = $fmtInsights['install'] ?
    //         round($insights['price'] / $fmtInsights['install'], 2) : $insights['price'];
    //     $fmtInsights['cpc'] = $fmtInsights['clicks'] ?
    //         round($insights['spend'] / $fmtInsights['clicks'], 2) : $insights['spend'];
    //
    //     $alarmTable = [];
    //     $curTime = date("Y-m-d H:i:s");
    //     $hitCondition = [];
    //     $hitInsight = [];
    //     foreach ($curCondition['data'] as $oneCondition) {
    //         $flag = true;
    //         $curInsight = [];
    //         foreach ($oneCondition['data'] as $one) {
    //             $item = $one['field'];
    //             if (!$item) {
    //                 $flag = false;
    //                 break;
    //             }
    //             switch ($one['operator']) {
    //                 case Filter::OPR_GREATER_THAN:
    //                     $flag = $fmtInsights[$item] > $one['value'];
    //                     break;
    //                 case Filter::OPR_LESS_THAN:
    //                     $flag = $fmtInsights[$item] < $one['value'];
    //                     break;
    //                 case Filter::OPR_GREATER_THAN_AND_EQUAL:
    //                     $flag = $fmtInsights[$item] >= $one['value'];
    //                     break;
    //                 case Filter::OPR_LESS_THAN_AND_EQUAL:
    //                     $flag = $fmtInsights[$item] <= $one['value'];
    //                     break;
    //                 default:
    //                     $flag = false;
    //                     break;
    //             }
    //
    //             if (!$flag) {
    //                 break;
    //             }
    //             $curInsight[] = "$item:" . $fmtInsights[$item] . "(" . $one['value'] . ")";
    //         }
    //         if ($flag) {
    //             $hitInsight = $curInsight;
    //             $hitCondition = $oneCondition;
    //             break;
    //         }
    //     }
    //
    //     if (empty($hitCondition)) {
    //         return;
    //     }
    //     $opr = $hitCondition['opr'];
    //     if (empty($opr)) {
    //         return;
    //     }
    //     // 到这里说明 符合前面的条件 要找到ios book_id对应的广告集
    //     $setInfo = $this->getAdSetsByBooksAndDate(1, $nowDate, $bookId);
    //     // $setInfo = [
    //     //     [
    //     //         'sid'        => '23849354428700439',
    //     //         'name'          => '{b33794/l654/hj}-My Ex-11.3-(com.lmmobi.lereader)',
    //     //         'cid'   => '23849354428660439',
    //     //         'campaign_name' => '{b33794/l654/hj}-My Ex-11.3⑤-(com.lmmobi.lereader)',
    //     //         'aid'    => 'act_1090223124816824'
    //     //     ],
    //     //     [
    //     //         'sid'        => '23849375526300439',
    //     //         'name'          => '{b33794/I647/LXY}-GO-前妻再嫁我一次-1026(com.lmmobi.lereader)-2',
    //     //         'cid'   => '23849375526260439',
    //     //         'campaign_name' => '{b33794/I647/LXY}-GO-前妻再嫁我一次-1106-(com.lmmobi.lereader)-2',
    //     //         'aid'    => 'act_1090223124816824'
    //     //     ],
    //     // ];
    //
    //
    //     if (empty($setInfo)) {
    //         return;
    //     }
    //     $hit = [];
    //
    //     foreach ($setInfo as $one) {
    //         $attributes = [
    //             'ad_sid'   => $one['sid'],
    //             'rule_id'  => $row['id'],
    //             'hit_date' => $nowDate,
    //             'hit_time' => $curCondition['time']
    //         ];
    //         if (ProtectResult::getIns()->where($attributes)->exists()) {
    //             continue;
    //         }
    //         $insightsMsg = implode(",", $hitInsight);
    //         ProtectResult::getIns()->insert(
    //             array_merge(
    //                 [
    //                     'cid'      => $one['cid'],
    //                     'opr'      => json_encode($opr),
    //                     'insights' => $insightsMsg
    //                 ],
    //                 $attributes
    //             )
    //         );
    //         $hit[] = $one['sid'];
    //
    //         $oprMsg = '';
    //         foreach ($opr as $oneOprType) {
    //             switch ($oneOprType) {
    //                 case ruleModel::OPR_TYPE_CLOSE:
    //                     $oprMsg .= "关闭广告组";
    //                     break;
    //             }
    //         }
    //
    //         $tmp = [
    //             'set'          => $one['name'] . "(" . $one['sid'] . ")",
    //             'campaign'     => $one['campaign_name'] . "(" . $one['cid'] . ")",
    //             'account'      => $one['aid'],
    //             'insights'     => $insightsMsg,
    //             'time'         => $curTime,
    //             'hit_time'     => ProtectService::getIns()->fmtSecond2Time($curCondition['time']),
    //             'opr'          => $oprMsg,
    //             'set_url'      => CurlService::buildFbLink(
    //                 $one['aid'],
    //                 FacebookAdLevelMap::LEVEL_AD_SET,
    //                 $one['sid']
    //             ),
    //             'campaign_url' => CurlService::buildFbLink(
    //                 $one['aid'],
    //                 FacebookAdLevelMap::LEVEL_CAMPAIGN,
    //                 $one['cid']
    //             ),
    //             'account_url'  => CurlService::buildFbLink(
    //                 $one['aid'],
    //                 FacebookAdLevelMap::LEVEL_ACCOUNT,
    //                 0
    //             )
    //         ];
    //         $alarmTable[] = $tmp;
    //     }
    //
    //     if (empty($hit)) {
    //         return;
    //     }
    //
    //     // 执行结果
    //     foreach ($opr as $oneOprType) {
    //         switch ($oneOprType) {
    //             case ruleModel::OPR_TYPE_CLOSE:
    //                 $update = ['status' => 'PAUSED'];
    //                 foreach ($hit as $cid) {
    //                     CurlService::getIns()->curlRequest(
    //                         $cid,
    //                         $update,
    //                         '',
    //                         false,
    //                         CurlService::REQUEST_TYPE_POST
    //                     );
    //                 }
    //                 FacebookSet::getIns()
    //                     ->whereIn("sid", $hit)
    //                     ->update($update);
    //                 break;
    //             case ruleModel::OPR_TYPE_ALARM_DINGDING:
    //                 $content = '';
    //                 foreach ($alarmTable as $table) {
    //                     $content .= "广告组:" . $table['set'] . PHP_EOL;
    //                     $content .= "广告系列:" . $table['campaign'] . PHP_EOL;
    //                     $content .= "广告账户:" . $table['account'] . PHP_EOL;
    //                     $content .= "触发成效:" . $table['insights'] . PHP_EOL;
    //                     $content .= "触发时段:" . $table['hit_time'] . PHP_EOL;
    //                     $content .= "操作:" . $table['opr'] . PHP_EOL;
    //                     $content .= "触发时间:" . $table['time'] . PHP_EOL;
    //                 }
    //                 AlarmService::dingdingSend($content);
    //                 // AlarmService::dingdingSend($content, [], $bm['dingding_secret'], $bm['dingding_keyword']);
    //                 break;
    //             case ruleModel::OPR_TYPE_ALARM_MAIL:
    //                 $mailAddress = AlarmHelper::getCreatorMailAddress($row['uid']);
    //                 Mail::to($mailAddress)->send(
    //                     new FacebookEmailShipped(
    //                         $alarmTable,
    //                         'protect_waring',
    //                         '规则【' . $row['name'] . '】已触发,请及时查看'
    //                     )
    //                 );
    //                 break;
    //         }
    //     }
    // }


    // 兜底规则：
    // 计划进入兜底判断初始条件为 消耗≥50美金且 消耗时长超过2H
    // 以下条件满足其一即关闭：
    // 1；roi<1
    public function doSafelyLineV2($date, $aids, $coInfo, $nowSec)
    {
        $setInfo = $this->getAdSetsByBooksAndDate(
            $aids,
            0,
            $date,
            0,
            1,
            [
                DB::raw('ad_set.sid'),
                DB::raw('ad_set.name'),
                DB::raw('ad_set.start_time'),
                DB::raw('ad_campaign.cid'),
                DB::raw('ad_campaign.name as campaign_name'),
                DB::raw('ad_campaign.aid'),
                DB::raw('set_data.spend'),
                DB::raw('set_data.revenue'),
                DB::raw('convert(set_data.revenue*100/set_data.spend,decimal(5,2)) as roi'),
                // DB::raw('IF(set_data.install,
                //     convert(set_data.spend/set_data.install,decimal(10,2)),set_data.spend) as cpi'),
                // DB::raw('convert(set_data.clicks*1000/set_data.impressions,decimal(5,2)) as ctr'),
                // DB::raw('convert(set_data.spend/set_data.clicks,decimal(10,2)) as cpc'),
            ]
        );
        $spendLimit = 50;
        $timeLimit = 7200;
        // $cpi = 40;
        // $cpc = 5;
        // $ctr = 3;
        $roi = 1;
        $curTime = strtotime($date) + $nowSec;
        $paused = [];
        $pausedMsg = '';
        foreach ($setInfo as $one) {
            if ($one['spend'] < $spendLimit || $curTime - strtotime($one['start_time']) < $timeLimit) {
                continue;
            }
            if ($one['roi'] >= $roi) {
                continue;
            }


            $paused[] = $one['sid'];
            $pausedMsg .= $one['name'] . "(" . $one['sid'] .
                ")[花费] : " . $one['spend'] .
                " [roi] : " . $one['roi'] . "(" . $roi . ")" . PHP_EOL;
        }
        if (empty($paused)) {
            return;
        }
        $update = ['status' => 'PAUSED'];
        // foreach ($paused as $cid) {
        //     CurlService::getIns()->curlRequest($cid, $update, '', false, CurlService::REQUEST_TYPE_POST);
        // }
        // FacebookSet::getIns()
        //     ->whereIn("sid", $paused)
        //     ->update(['status' => 0]);
        AlarmService::dingdingSend(
            '兜底逻辑v2.1-关停以下广告组' . PHP_EOL . $pausedMsg,
            [],
            $coInfo['dingding_secret'],
            $coInfo['dingding_keyword']
        );
    }


    // 兜底规则：
    // 账户限制 -> 广告组限制
    public function doSafelyLineV3($date, $aids, $coInfo, $nowSec)
    {
        $accountConf = [
            4 * 3600  => [200 => ['account' => [['roi', '<', 1]]], 100 => ['account' => [['roi', '<', 1]]]],
            9 * 3600  => [
                200 =>
                    ['account' => [['roi', '<', 8]], 'camp' => [100 => [['roi', '<', 8]], 50 => [['roi', '<', 5]]]],
                100 =>
                    ['account' => [['roi', '<', 8]], 'camp' => [100 => [['roi', '<', 8]], 50 => [['roi', '<', 5]]]]
            ],
            13 * 3600 => [
                200 =>
                    [
                        'account' => [['roi', '<', 15]],
                        'camp'    => [100 => [['roi', '<', 15]], 50 => [['roi', '<', 10]]]
                    ],
                100 =>
                    [
                        'account' => [['roi', '<', 12]],
                        'camp'    => [100 => [['roi', '<', 15]], 50 => [['roi', '<', 10]]]
                    ]
            ],
            16 * 3600 => [
                200 =>
                    [
                        'account' => [['roi', '<', 20]],
                        'camp'    => [100 => [['roi', '<', 20]], 50 => [['roi', '<', 14]]]
                    ],
                100 =>
                    [
                        'account' => [['roi', '<', 16]],
                        'camp'    => [100 => [['roi', '<', 20]], 50 => [['roi', '<', 16]]]
                    ]
            ],
            19 * 3600 => [
                200 =>
                    [
                        'account' => [['roi', '<', 22]],
                        'camp'    => [100 => [['roi', '<', 23]], 50 => [['roi', '<', 16]]]
                    ],
                100 =>
                    [
                        'account' => [['roi', '<', 16]],
                        'camp'    => [100 => [['roi', '<', 23]], 50 => [['roi', '<', 20]]]
                    ]
            ],
            // 85500     => [ // 23:45
            //     200 =>
            //         [
            //             'account' => [['roi', '<', 25]],
            //             // 'camp'    => [100 => [['roi', '<', 25]], 50 => [['roi', '<', 21]]]
            //         ],
            //     100 =>
            //         [
            //             'account' => [['roi', '<', 20]],
            //             // 'camp'    => [100 => [['roi', '<', 30]], 50 => [['roi', '<', 25]]]
            //         ]
            // ],
        ];
        krsort($accountConf);

        $curCondition = [];
        $curTime = 0;
        foreach ($accountConf as $time => $one) {
            if ($nowSec < $time) {
                continue;
            }
            $curTime = $time;
            $curCondition = $one;
            break;
        }

        if (empty($curCondition)) {
            dump('当前时间没有兜底配置');
            return;
        }
        $onceCache = $this->shutdownCache($date, $curTime);
        if (Redis::get($onceCache)) {
            dump('该时段已检测过');
            return;
        }
        // 记录标签
        Redis::set($onceCache, 86400, 1);

        $setInfo = $this->getAdSetsByBooksAndDate(
            $aids,
            0,
            $date,
            0,
            'ACTIVE',
            [
                DB::raw('ad_set.sid'),
                DB::raw('ad_set.name'),
                DB::raw('ad_set.start_time'),
                DB::raw('ad_campaign.cid'),
                DB::raw('ad_campaign.name as campaign_name'),
                DB::raw('ad_campaign.aid'),
                DB::raw('set_data.spend'),
                DB::raw('set_data.revenue'),
                DB::raw('convert(set_data.revenue*100/set_data.spend,decimal(5,2)) as roi'),
            ]
        );
        $accountPurchaseValue = [];
        $accountSpend = [];
        $adSetMap = [];
        foreach ($setInfo as $row) {
            $accountPurchaseValue[$row['aid']] =
                ($accountPurchaseValue[$row['aid']] ?? 0) + $row['revenue'];
            $accountSpend[$row['aid']] =
                ($accountSpend[$row['aid']] ?? 0) + $row['spend'];
            $adSetMap[$row['aid']][] = $row['sid'];
        }
        $msg = '';
        $setMap = array_column($setInfo, null, 'sid');

        foreach ($accountSpend as $aid => $spend) {
            $config = [];
            $minSpend = 0;
            foreach ($curCondition as $minSpend => $conf) {
                if ($spend > $minSpend) {
                    $config = $conf;
                    break;
                }
            }
            if (empty($config) || empty($spend)) {
                continue;
            }
            // 检查账户是否中标
            $calc = [];
            $calc['roi'] = round(($accountPurchaseValue[$aid] ?? 0) * 100 / $spend, 2);
            $flag = true;
            $record = [];
            foreach ($config['account'] as $row) {
                if (!Tool::get($calc, $row[0], true)) {
                    continue;
                }
                $flag = $this->compareRule($calc, $row);

                if (!$flag) {
                    break;
                }
                $record[] = "{$row[0]}:" . $calc[$row[0]] . "(" . $row[1] . $row[2] . ")";
            }

            // 未触发广告账户保护 不执行检测
            if (!$flag) {
                continue;
            }

            $cids = $adSetMap[$aid];

            //未设置广告系列配置 封闭逻辑
            if (!Tool::get($config, 'camp')) {
                $accountMsg = $aid .
                    "触发广告账户逻辑:spend:{$spend}【{$minSpend}】," . implode(",", $record) . PHP_EOL;


                $accountMsg .= "关闭以下广告组" . PHP_EOL;
                $update = ['status' => 'PAUSED'];

                $turnOff = [];

                foreach ($cids as $cid) {
                    $cacheName = $this->shutdownCache($cid, $curTime);
                    if (Redis::get($cacheName)) {
                        continue;
                    }
                    $turnOff[] = $cid;
                    // CurlService::getIns()->curlRequest($cid, $update, '', false, CurlService::REQUEST_TYPE_POST);
                    $accountMsg .= ($setMap[$cid]['name'] ?? '') . "(" . $cid . ")" . PHP_EOL;
                    Redis::setex($this->shutdownCache($cid, $curTime), 86400, 1);
                }

                if ($turnOff) {
                    $msg .= $accountMsg;
                    // FacebookSet::getIns()
                    //     ->whereIn("sid", $turnOff)
                    //     ->update(['status' => 0]);
                }

                continue;
            }

            //设置广告系列配置 ->查找匹配的广告系列
            $hit = [];
            $msgHit = '';
            foreach ($cids as $id) {
                $detail = $setMap[$id];
                $campConf = [];
                $minCampSpend = 0;
                foreach ($config['camp'] as $minCampSpend => $row) {
                    if ($detail['spend'] > $minCampSpend) {
                        $campConf = $row;
                        break;
                    }
                }
                if (empty($campConf) || empty($detail['spend'])) {
                    continue;
                }

                $flag = true;
                $record = [];
                foreach ($campConf as $row) {
                    if (!Tool::get($detail, $row[0], true)) {
                        continue;
                    }
                    $flag = $this->compareRule($detail, $row);
                    if (!$flag) {
                        break;
                    }
                    $record[] = "{$row[0]}:" . $detail[$row[0]] . "(" . $row[1] . $row[2] . ")";
                }
                if ($flag) {
                    if (Redis::get($this->shutdownCache($id, $curTime))) {
                        continue;
                    }
                    $hit[] = $id;
                    $msgHit .= ($setMap[$id]['name'] ?? '') . "(" . $id . ")" .
                        ":spend:{$detail['spend']}【{$minCampSpend}】," .
                        implode(",", $record) . PHP_EOL;
                }
            }
            if (!empty($hit)) {
                $msg .= "触发Camp逻辑关闭以下广告组" . PHP_EOL . $msgHit;
                $update = ['status' => 'PAUSED'];

                foreach ($hit as $cid) {
                    Redis::setex($this->shutdownCache($cid, $curTime), 86400, 1);
                    // CurlService::getIns()->curlRequest($cid, $update, '', false, CurlService::REQUEST_TYPE_POST);
                }
                // FacebookSet::getIns()
                //     ->whereIn("sid", $hit)
                //     ->update(['status' => 0]);
                $msg .= PHP_EOL;
            }
        }


        if ($msg) {
            AlarmService::dingdingSend(
                '兜底逻辑v3.11(测试)' . PHP_EOL .
                '触发时段:' . ProtectService::getIns()->fmtSecond2Time($curTime) . PHP_EOL .
                $msg,
                [],
                $coInfo['dingding_secret'],
                $coInfo['dingding_keyword']
            );
        }
    }


    public function updateRecord($row, $ruleId, $fmtDate, $fmtTime, $hitInsight, $opr)
    {
        $attributes = [
            'ad_set_id' => $row['sid'],
            'rule_id'   => $ruleId,
            'hit_date'  => $fmtDate,
            'hit_time'  => $fmtTime,
        ];
        if (ProtectResult::getIns()->where($attributes)->exists()) {
            return;
        }
        $insightsMsg = implode(",", $hitInsight);
        ProtectResult::getIns()->insert(
            array_merge(
                [
                    'campaign_id' => $row['cid'],
                    'opr'         => json_encode($opr),
                    'insights'    => $insightsMsg,
                    'code'        => $row['user'],
                    'is_notice'   => 0
                ],
                $attributes
            )
        );
    }

    public function doSafelyLineV5($date, $aids, $coInfo, $nowSec, $timzones, $aidRsInfo, $tokenMap)
    {
        // 将同一时区的广告账户分为不同平台的数组
        $recordHit = [];
        $aidPlatformArr = [];
        $tokenList = [];
        foreach ($aids as $aid) {
            $detail = Tool::get($aidRsInfo, $aid);

            if (empty($detail) || !isset($tokenMap[$detail['bm_id']])) {
                continue;
            }
            $tokenList[$detail['aid']] = $tokenMap[$detail['bm_id']];
            $aidPlatformArr[$detail['platform']][] = $detail['aid'];
        }

        if (empty($aidPlatformArr)) {
            return;
        }

        $conf = [
            0   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],
            6   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],
            7   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]
            ],
            11   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],
            15   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],
            20   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],
            19   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 20],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],


            5   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 5]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 10],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],

                ]
            ],
            16   => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 5]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 10],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],

                ]
            ],
            100 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10]
                    ],
                    [
                        ['roi', '=', 0],
                        ['cpi', '>=', 25],
                    ],
                    // [
                    //     ['roi', '=', 0],
                    //     ['spend', '>', 5],
                    //     ['ctr', '<', 0.3]
                    // ],
                ]

            ],
        ];

        $curTime = time() - $timzones;
        $phpEol = "\n";
        foreach ($aidPlatformArr as $platform => $aidList) {
            $curConf = Tool::get($conf, $platform);
            if (empty($curConf)) {
                continue;
            }

            $setInfo = $this->getAdSetsByBooksAndDate(
                $aidList,
                0,
                $date,
                0,
                1,
                [
                    DB::raw('ad_set.sid'),
                    DB::raw('ad_set.user'),
                    DB::raw('ad_set.name'),
                    DB::raw('ad_set.start_time'),
                    DB::raw('ad_campaign.cid'),
                    DB::raw('ad_campaign.name as campaign_name'),
                    DB::raw('ad_campaign.aid'),
                    DB::raw('set_data.spend'),
                    DB::raw('set_data.revenue'),
                    DB::raw('convert(set_data.revenue*100/set_data.spend,decimal(5,2)) as roi'),
                    DB::raw('IF(set_data.install,
                        convert(set_data.spend/set_data.install,decimal(10,2)),set_data.spend) as cpi'),
                    DB::raw('convert(set_data.clicks*100/set_data.impressions,decimal(5,2)) as ctr'),

                    DB::raw('IF(set_data.clicks,
                        convert(set_data.spend/set_data.clicks,decimal(10,2)),set_data.spend) as cpc'),

                ]
            );
            // 凌晨4点不执行
            if ($nowSec < $curConf['next_day_time']) {
                continue;
            }

            $paused = [];
            $pausedMsg = '';
            $cidList = [];
            foreach ($setInfo as $one) {
                // 时间判定
                $startTime = strtotime($one['start_time']);
                // 4小时以内跳过
                if ($curTime - $startTime < $curConf['spend_time']) {
                    continue;
                }

                if (!$one['spend'] || $one['spend'] == '0.00') {
                    continue;
                }

                $record = [];
                $flag = true;
                foreach ($curConf['limit'] as $row) {
                    $record = [];
                    foreach ($row as $detail) {
                        $flag = $this->compareRule($one, $detail);
                        if (!$flag) {
                            break;
                        }
                        $record[] = "{$detail[0]}:" . $one[$detail[0]] . "(" . $detail[1] . $detail[2] . ")";
                    }
                    if ($flag) {
                        break;
                    }
                }
                if ($flag) {
                    if (in_array($one['sid'], $recordHit)) {
                        continue;
                    }
                    $recordHit[] = $one['sid'];
                    $paused[] = $one['sid'];
                    $cidList[$one['sid']] = $one['aid'];
                    $pausedMsg .= $one['name'] . "(" . $one['sid'] . ") : " .
                        implode(',', $record) . $phpEol;
                    try {
                        $this->updateRecord($one, ruleModel::SAFE_LINE_V5, $date, $nowSec, $record, [1]);
                    } catch (\Exception $e) {
                        \Log::info('更新规则记录失败', [$e->getMessage(), $one]);
                    }
                }
            }
            if (empty($paused)) {
                continue;
            }

            $update = ['status' => 'PAUSED'];
            $curlSuc = [];
            foreach ($paused as $cid) {
                if (!isset($cidList[$cid])) {
                    continue;
                }
                $curlSuc[] = $cid;
                CurlService::getIns()->curlRequest(
                    $cid,
                    $update,
                    $tokenList[$cidList[$cid]] ?? config('facebook.access_token'),
                    false,
                    CurlService::REQUEST_TYPE_POST
                );
            }

            if ($curlSuc) {
                FacebookSet::getIns()
                    ->whereIn("sid", $curlSuc)
                    ->update(['status' => 0]);
            }


            AlarmService::dingdingSend(
                '兜底逻辑v5.11-关停以下广告组' . $phpEol .
                '触发时段:' . ProtectService::getIns()->fmtSecond2Time($nowSec) . $phpEol .
                $pausedMsg,
                [],
                $coInfo['dingding_secret'],
                $coInfo['dingding_keyword']
            );
        }
    }

    public function doPixelSafelyMain($date, $aids, $coInfo, $nowSec, $timzones, $aidRsInfo, $tokenMap)
    {
        // 将同一时区的广告账户分为不同平台的数组
        $recordHit = [];
        $aidPlatformArr = [];
        $tokenList = [];
        foreach ($aids as $aid) {
            $detail = Tool::get($aidRsInfo, $aid);

            if (empty($detail) || !isset($tokenMap[$detail['bm_id']])) {
                continue;
            }
            $tokenList[$detail['aid']] = $tokenMap[$detail['bm_id']];
            $aidPlatformArr[$detail['platform']][] = $detail['aid'];
        }

        if (empty($aidPlatformArr)) {
            return;
        }

        $conf = [
            0 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            6 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            7 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            11 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            15 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            19 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            20 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 10],
                    ]
                ]

            ],
            5 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 5],
                    ]
                ]
            ],
            16 => [
                'spend_time'    => 14400,
                'next_day_time' => 14400,
                'limit'         => [
                    [
                        ['roi', '=', 0],
                        ['cpc', '>=', 5],
                    ]
                ]
            ]
        ];

        $curTime = time() - $timzones;
        $phpEol = "\n";
        foreach ($aidPlatformArr as $platform => $aidList) {
            $curConf = Tool::get($conf, $platform);
            if (empty($curConf)) {
                continue;
            }

            $setInfo = $this->getAdPixelSetsByBooksAndDate(
                $aidList,
                $date,
                1,
                [
                    DB::raw('ad_set.sid'),
                    DB::raw('ad_set.user'),
                    DB::raw('ad_set.name'),
                    DB::raw('ad_set.start_time'),
                    DB::raw('ad_campaign.cid'),
                    DB::raw('ad_campaign.name as campaign_name'),
                    DB::raw('ad_campaign.aid'),
                    DB::raw('set_data.spend'),
                    DB::raw('set_data.revenue'),
                    DB::raw('convert(set_data.revenue*100/set_data.spend,decimal(5,2)) as roi'),
                    DB::raw('IF(set_data.install,
                        convert(set_data.spend/set_data.install,decimal(10,2)),set_data.spend) as cpi'),
                    DB::raw('convert(set_data.clicks*100/set_data.impressions,decimal(5,2)) as ctr'),

                    DB::raw('IF(set_data.clicks,
                        convert(set_data.spend/set_data.clicks,decimal(10,2)),set_data.spend) as cpc'),

                ]
            );
            // 凌晨4点不执行
            if ($nowSec < $curConf['next_day_time']) {
                continue;
            }

            $paused = [];
            $pausedMsg = '';
            $cidList = [];
            foreach ($setInfo as $one) {
                // 时间判定
                $startTime = strtotime($one['start_time']);
                // 4小时以内跳过
                if ($curTime - $startTime < $curConf['spend_time']) {
                    continue;
                }

                if (!$one['spend'] || $one['spend'] == '0.00') {
                    continue;
                }

                $record = [];
                $flag = true;
                foreach ($curConf['limit'] as $row) {
                    $record = [];
                    foreach ($row as $detail) {
                        $flag = $this->compareRule($one, $detail);
                        if (!$flag) {
                            break;
                        }
                        $record[] = "{$detail[0]}:" . $one[$detail[0]] . "(" . $detail[1] . $detail[2] . ")";
                    }
                    if ($flag) {
                        break;
                    }
                }
                if ($flag) {
                    if (in_array($one['sid'], $recordHit)) {
                        continue;
                    }
                    $recordHit[] = $one['sid'];
                    $paused[] = $one['sid'];
                    $cidList[$one['sid']] = $one['aid'];
                    $pausedMsg .= $one['name'] . "(" . $one['sid'] . ") : " .
                        implode(',', $record) . $phpEol;
                    try {
                        $this->updateRecord($one, ruleModel::SAFE_LINE_V5, $date, $nowSec, $record, [1]);
                    } catch (\Exception $e) {
                        \Log::info('更新规则记录失败', [$e->getMessage(), $one]);
                    }
                }
            }
            if (empty($paused)) {
                continue;
            }

            $update = ['status' => 'PAUSED'];
            $curlSuc = [];
            foreach ($paused as $cid) {
                if (!isset($cidList[$cid])) {
                    continue;
                }
                $curlSuc[] = $cid;
                CurlService::getIns()->curlRequest(
                    $cid,
                    $update,
                    $tokenList[$cidList[$cid]] ?? config('facebook.access_token'),
                    false,
                    CurlService::REQUEST_TYPE_POST
                );
            }

            if ($curlSuc) {
                FacebookSet::getIns()
                    ->whereIn("sid", $curlSuc)
                    ->update(['status' => 0]);
            }


            AlarmService::dingdingSend(
                '兜底逻辑v5.12-关停以下像素广告组' . $phpEol .
                '触发时段:' . ProtectService::getIns()->fmtSecond2Time($nowSec) . $phpEol .
                $pausedMsg,
                [],
                $coInfo['dingding_secret'],
                $coInfo['dingding_keyword']
            );
        }
    }


    public function compareRule($calc, $row): bool
    {
        switch ($row[1]) {
            case '=':
                $flag = $calc[$row[0]] == $row[2];
                break;
            case '<':
                $flag = $calc[$row[0]] < $row[2];
                break;
            case '>':
                $flag = $calc[$row[0]] > $row[2];
                break;
            case '<=':
                $flag = $calc[$row[0]] <= $row[2];
                break;
            case '>=':
                $flag = $calc[$row[0]] >= $row[2];
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    public function shutdownCache($id, $time): string
    {
        return 'protect_v1_shutdown_' . $id . "_" . $time;
    }
}
