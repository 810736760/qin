<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use App\Models\Commission\CommissionInfo;
use App\Models\Commission\CommissionPerPeriodInfo;
use App\Models\Commission\CommissionReport;
use App\Models\Facebook\BM;
use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookDailyBookSummaryData;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Group;
use App\Models\PackageConf;
use App\Models\Stat\AbroadDayRecharge;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\AbroadLinkStatistics;
use App\Models\Stat\Book;
use App\Models\Stat\OldAdminManager;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\CurlService;
use App\Services\RedisService;
use App\Services\Tiktok\PublicService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Facades\Schema;

class CommissionCommand extends Command
{
    protected $signature = 'Commission {start_date?} {end_date?} {type?} {os?}';

    protected $description = '提成统计';

    // 3.28-4.3号执行的是方案 1  之后执行的是方案2
    public function handle()
    {
        ini_set('memory_limit', '512M');
        $args = $this->arguments();
        $start = Tool::get($args, 'start_date') ?: 20220101;
        $end = Tool::get($args, 'end_date', date("Ymd", strtotime('-1 day')));
        $type = Tool::get($args, 'type', 0);
        $os = Tool::get($args, 'os', 0);


        $systemInfos = CompanyService::systemInfo();
        $confList = [
            0 => [
                0 => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],
                1 => ['reach' => 133, 'successOne' => 110, 'failedOne' => '125'],
                2 => ['reach' => 133, 'successOne' => 110, 'failedOne' => '125'],
                3 => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],
                5 => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],
                7 => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],

                6  => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],
                13 => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],
                11 => ['reach' => 135, 'successOne' => 110, 'failedOne' => '125'],


            ],
            1 => [
                0 => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],
                1 => ['reach' => 131, 'successOne' => 110, 'failedOne' => '125'],
                2 => ['reach' => 133, 'successOne' => 110, 'failedOne' => '125'],
                3 => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],
                5 => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],
                7 => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],

                6  => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],
                13 => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],
                11 => ['reach' => 134, 'successOne' => 110, 'failedOne' => '125'],
            ]
        ];

        $conf = $confList[$type];

        $platform = array_keys($conf);
        if ($os) {
            $this->doIOSHandle($start, $end, $platform, $conf, $systemInfos);
        } else {
            $this->doAndroidHandle($start, $end, $platform, $conf, $systemInfos);
        }
    }

    public function doIOSHandle($start, $end, $platform, $conf, $systemInfos)
    {

        // 仅IOS非像素
        $iosLink = AbroadLink::getIns()
            ->where('system', 1)
            ->whereIn('platform', $platform)
            ->where('ad_market', 1)
            ->whereNotIn('link_type', [3])
            ->pluck('union_link_id')
            ->toArray();
        if (empty($iosLink)) {
            return;
        }
        foreach (['%Y%m'] as $dt) {
            $dateArr = Tool::fmtBetweenDate($start, $end, $dt);
            if (empty($dateArr)) {
                dump('未满一月');
                continue;
            }
            [$sum, $fmtSpend, $fmtPrice, $fmtRatio, $hitDate] = $this->calcOsByLink($iosLink, $dateArr, $dt, $conf);
            $bookRs = $this->calcBookRsByDate($sum, $hitDate, $dateArr, $dt);
            $this->export($systemInfos, $dt, [], $bookRs, $dateArr, $fmtPrice, $fmtSpend, $fmtRatio, $conf, 1);
        }
    }

    public function calcBookRsByDate($sum, $dateArr, $dateArr2, $dateType)
    {
        $usd = DBService::getIns()->getUSD2HKD();
        $allBookRs = FacebookDailyBookSummaryData::getIns()
            ->select(
                DB::raw("date_format(event_date, '$dateType' ) as date_f"),
                'platform',
                'book_info'
            )
            ->whereIn('event_date', $dateArr2)
            ->get()
            ->toArray();
        $rs = [];
        foreach ($allBookRs as $row) {
            if (!Tool::get($sum, $row['platform'])
                || !in_array($row['date_f'], $sum[$row['platform']])
                || empty($row['book_info'])
            ) {
                continue;
            }
            $bookRs = json_decode($row['book_info'], true);
            foreach ($bookRs as $bookId => $one) {
                if (empty($one['spendByUser'])) {
                    continue;
                }
                foreach ($one['spendByUser'] as $detail) {
                    $rs[$row['platform']][$row['date_f']][$detail['user']]['spend']
                        = ($rs[$row['platform']][$row['date_f']][$detail['user']]['spend'] ?? 0) + round(
                            $detail['spend'] / 100 / $usd,
                            2
                        );
                    $rs[$row['platform']][$row['date_f']][$detail['user']]['link_id'] = $detail['user'];
                    $rs[$row['platform']][$row['date_f']][$detail['user']]['code'] = $detail['user'];
                    $rs[$row['platform']][$row['date_f']][$detail['user']]['book_id'] = $bookId;
                    $rs[$row['platform']][$row['date_f']][$detail['user']]['revenue'] = 0;
                    $rs[$row['platform']][$row['date_f']][$detail['user']]['commission'] =
                        round($rs[$row['platform']][$row['date_f']][$detail['user']]['spend'] * 0.006, 2);
                }
            }
        }
        return $rs;
    }

    public function calcOsByLink($iosLink, $dateArr, $dateType, $conf)
    {
        $defaultReturn = [
            [],
            [],
            [],
            [],
            [],
        ];
        $rowName = DB::raw("date_format(user_date, '$dateType' )");
        $priceRs = AbroadDayRecharge::getIns()
            ->select(
                DB::raw("date_format(user_date, '$dateType' ) as date_f"),
                'platform',
                DB::raw('sum(money) as price'),
                'union_link_id'
            )
            ->whereIn('user_date', $dateArr)
            ->whereIn('union_link_id', $iosLink)
            ->groupBy([$rowName, 'platform'])
            ->get()
            ->toArray();
        if (empty($priceRs)) {
            return $defaultReturn;
        }


        $spendRs = AbroadLinkStatistics::getIns()
            ->select(
                DB::raw("date_format(date, '$dateType' ) as date_f"),
                DB::raw('round(sum(primecost),2) as spend'),
                'platform',
                'union_link_id'
            )
            ->whereIn('date', $dateArr)
            ->whereIn('union_link_id', $iosLink)
            ->groupBy([DB::raw("date_format(date, '$dateType' )"), 'platform'])
            ->get()
            ->toArray();
        if (empty($spendRs)) {
            return $defaultReturn;
        }
        $fmtSpend = [];
        foreach ($spendRs as $row) {
            if ($row['platform'] === null) {
                continue;
            }
            $fmtSpend[$row['platform']][$row['date_f']] = $row['spend'];
        }

        $back = [];
        $fmtPrice = [];
        $fmtRatio = [];
        $allDate = [];
        foreach ($priceRs as $row) {
            $spend = floatval($fmtSpend[$row['platform']][$row['date_f']] ?? 0);


            if (empty($spend) || !Tool::get($conf, $row['platform'])) {
                continue;
            }
            $linkBackRatio = $conf[$row['platform']]['reach'];
            $ratio = round($row['price'] * 100 / $spend, 2);
            $fmtRatio[$row['platform']][$row['date_f']] = $ratio;
            $fmtPrice[$row['platform']][$row['date_f']] = $row['price'];
            if ($ratio < $linkBackRatio) {
                continue;
            }
            // $personRatio = $conf[$row['platform']]['successOne'];
            $back[$row['platform']][] = $row['date_f'];
            $allDate[] = $row['date_f'];
        }
        return [$back, $fmtSpend, $fmtPrice, $fmtRatio, Tool::getUnique($allDate)];
    }

    public function doAndroidHandle($start, $end, $platform, $conf, $systemInfos)
    {
        // 获取安卓Link
        // 仅安卓和像素代码，不含再归因链接
        $androidLink = AbroadLink::getIns()
            ->where('system', 0)
            ->whereIn('platform', $platform)
            ->whereIn('link_type', [0, 3])
            ->where('is_default', 0)
            ->where('ad_market', 1)
            ->pluck('union_link_id')
            ->toArray();

        // ios 像素链接
        $iosPixelLink = AbroadLink::getIns()
            ->where('system', 1)
            ->whereIn('platform', $platform)
            ->where('link_type', 3)
            ->where('is_default', 0)
            ->where('ad_market', 1)
            ->pluck('union_link_id')
            ->toArray();
        $androidLink = array_merge($androidLink, $iosPixelLink);
        if (empty($androidLink)) {
            return;
        }
        // $bookConf = [
        //     '%Y%m%d' => [
        //         0 => ['short' => 70, 'male' => 20, 'female' => '35'],
        //         1 => ['short' => 70, 'male' => 20, 'female' => '35'],
        //         5 => ['short' => 70, 'male' => 20, 'female' => '35'],
        //         7 => ['short' => 70, 'male' => 20, 'female' => '35'],
        //     ],
        //     '%Y%U'   => [
        //         0 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //         1 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //         5 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //         7 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //     ],
        //     '%Y%m'   => [
        //         0 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //         1 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //         5 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //         7 => ['short' => 100, 'male' => 40, 'female' => '60'],
        //     ],
        //
        // ];
        foreach (['%Y%U'] as $dt) {
            $dateArr = Tool::fmtBetweenDate($start, $end, $dt);
            if (empty($dateArr)) {
                dump('未满一周');
                continue;
            }
            [$sum, $unReach, $unReachDateAll, $fmtPrice, $fmtSpend, $fmtRatio] = $this->calcByLink($androidLink, $dateArr, $dt, $conf);
            $unReachSum = $this->calcByLinkV1($androidLink, $unReachDateAll, $dateArr, $dt, $conf, $unReach, $sum);
            $this->export($systemInfos, $dt, [], $unReachSum, $dateArr, $fmtPrice, $fmtSpend, $fmtRatio, $conf, 0);

            // if (Tool::get($bookConf, $dt)) {
            //     $rs = $this->calcByLinkV2($bookConf[$dt], $dt, $dateArr, $androidLink);
            //     $this->exportV2($systemInfos, $dt, $rs, $dateArr, $fmtPrice, $fmtSpend, $fmtRatio);
            // }
        }
    }

    public function exportV2($systemInfos, $dateType, $rs, $dateArr, $fmtPrice, $fmtSpend, $fmtRatio)
    {
        $sysMap = array_column($systemInfos, 'name', 'platform');


        $content = '';
        $totalCommission = 0;
        foreach ($rs as $platform => $detail) {
            $content .= $sysMap[$platform] . PHP_EOL;
            $content .= 'Date / User,spend,revenue,roi,commission,remark' . PHP_EOL;

            ksort($detail);

            $packageCommission = 0;
            foreach ($detail as $userDate => $oneMore) {
                $spend = round(($fmtSpend[$platform][$userDate] ?? 0), 2);
                $revenue = round(($fmtPrice[$platform][$userDate] ?? 0) / 100, 2);
                $roi = ($fmtRatio[$platform][$userDate] ?? 0) . "%";
                if ($dateType == '%Y%U') {
                    $us = $this->fmtWeek(substr($userDate, 0, 4), substr($userDate, 4));
                    $userDate = max($us[0], $dateArr[0]) . "-" . min($us[1], end($dateArr));
                }
                $content .= "{$userDate},{$spend},{$revenue},{$roi},-,-" . PHP_EOL;

                $oneCommission = 0;
                foreach ($oneMore as $user => $show) {
                    $roi = intval($show['spend']) == 0 ? 0 : round($show['revenue'] * 100 / $show['spend'], 2);
                    $content .= "{$user},{$show['spend']},{$show['revenue']},{$roi}%,{$show['commission']}," . implode("&", $show['link']) . PHP_EOL;
                    $oneCommission += $show['commission'];
                }
                $oneCommission = round($oneCommission, 2);
                $content .= "合计,,,,{$oneCommission}" . PHP_EOL . PHP_EOL;
                $packageCommission += $oneCommission;
            }
            $content .= PHP_EOL;
            $packageCommission = round($packageCommission, 2);
            $content .= "包合计,,,,{$packageCommission}" . PHP_EOL . PHP_EOL;
            $totalCommission += $packageCommission;
            $content .= PHP_EOL;
        }

        $totalCommission = round($totalCommission, 2);
        $content .= "总合计,'','','',{$totalCommission}" . PHP_EOL;
        $fName = "/var/www/html/phplog/book_" . $dateArr[0] . "-" . end($dateArr) . "(" . $dateType . ").csv";
        $file = fopen($fName, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    public function export($systemInfos, $dateType, $sum, $unReachSum, $dateArr, $fmtPrice, $fmtSpend, $fmtRatio, $conf, $os)
    {
        $appOs = ['Android', 'IOS'];
        $sysMap = array_column($systemInfos, 'name', 'platform');
        $all = $unReachSum;
        // $all = [
        //     '0' => [
        //         202202 => [
        //             'lqq-732' => ["spend"      => 180.9,
        //                           "revenue"    => 446.88,
        //                           "roi"        => 247.03,
        //                           "commission" => 1.27,
        //                           "user"       => "lqq-732",
        //                           "link_id"    => 732]
        //         ]
        //     ]
        // ];
        // 跟过去比
        $period = date("Ymd");
        $this->compareHistory($all, $os, $period);
        $content = '';
        $totalCommission = 0;
        $userContent = '';
        $allUser = [];
        foreach ($all as $platform => $detail) {
            $content .= $sysMap[$platform] . PHP_EOL;
            $content .= 'Date / User,spend,revenue,roi,commissionHistory,commission' . PHP_EOL;

            $userContent .= $sysMap[$platform] . PHP_EOL;
            $userContent .= 'Date / User,spend,revenue,roi,commissionHistory,commission' . PHP_EOL;


            ksort($detail);

            $packageCommission = 0;

            foreach ($detail as $userDate => $oneMore) {
                $spend = round(($fmtSpend[$platform][$userDate] ?? 0) / 100, 2);
                $revenue = round(($fmtPrice[$platform][$userDate] ?? 0) / 100, 2);
                $roi = ($fmtRatio[$platform][$userDate] ?? 0) . "%";
                if ($dateType == '%Y%U') {
                    $us = $this->fmtWeek(substr($userDate, 0, 4), substr($userDate, 4));
                    $userDate = max($us[0], $dateArr[0]) . "-" . min($us[1], end($dateArr));
                }
                $content .= "{$userDate},{$spend},{$revenue},{$roi}" . PHP_EOL;
                $userContent .= "{$userDate},{$spend},{$revenue},{$roi}" . PHP_EOL;

                $oneCommission = 0;
                $userList = [];
                foreach ($oneMore as $user => $show) {
                    $roi = intval($show['spend']) == 0 ? 0 : round($show['revenue'] * 100 / $show['spend'], 2);
                    $hist = $show['commissionHistory'] ?? 0;
                    $content .= "{$user},{$show['spend']},{$show['revenue']},{$roi}%,{$hist},{$show['commission']}" . PHP_EOL;
                    $oneCommission += $show['commission'];
                    $userList[$show['code']]['revenue'] = ($userList[$show['code']]['revenue'] ?? 0) + $show['revenue'];
                    $userList[$show['code']]['spend'] = ($userList[$show['code']]['spend'] ?? 0) + $show['spend'];
                    $userList[$show['code']]['commissionHistory'] = ($userList[$show['code']]['commissionHistory'] ?? 0) + $hist;
                    $userList[$show['code']]['commission'] = ($userList[$show['code']]['commission'] ?? 0) + $show['commission'];
                }

                foreach ($userList as $code => $oneUser) {
                    $allUser[$code]['revenue'] = ($allUser[$code]['revenue'] ?? 0) + $oneUser['revenue'];
                    $allUser[$code]['spend'] = ($allUser[$code]['spend'] ?? 0) + $oneUser['spend'];
                    $allUser[$code]['commissionHistory'] = ($allUser[$code]['commissionHistory'] ?? 0) + $oneUser['commissionHistory'];
                    $allUser[$code]['commission'] = ($allUser[$code]['commission'] ?? 0) + $oneUser['commission'];
                    $roi = intval($oneUser['spend']) == 0 ? 0 : round($oneUser['revenue'] * 100 / $oneUser['spend'], 2);
                    $userContent .= "{$code},{$oneUser['spend']},{$oneUser['revenue']},{$roi}%,{$oneUser['commissionHistory']},{$oneUser['commission']}" . PHP_EOL;
                }


                $oneCommission = round($oneCommission, 2);
                $content .= "合计,,,,,{$oneCommission}" . PHP_EOL . PHP_EOL;
                $userContent .= "合计,,,,,{$oneCommission}" . PHP_EOL . PHP_EOL;
                $packageCommission += $oneCommission;
            }
            $content .= PHP_EOL;
            $userContent .= PHP_EOL;
            $packageCommission = round($packageCommission, 2);
            $content .= "包合计,,,,,{$packageCommission}" . PHP_EOL . PHP_EOL;
            $userContent .= "包合计,,,,,{$packageCommission}" . PHP_EOL . PHP_EOL;
            $totalCommission += $packageCommission;
            $content .= PHP_EOL;
            $userContent .= PHP_EOL;
        }


        $totalCommission = round($totalCommission, 2);
        $content .= "总合计,,,,,{$totalCommission}" . PHP_EOL;
        $userContent .= "用户合计" . PHP_EOL;
        foreach ($allUser as $code => $oneUser) {
            $roi = intval($oneUser['spend']) == 0 ? 0 : round($oneUser['revenue'] * 100 / $oneUser['spend'], 2);
            $userContent .= "{$code},{$oneUser['spend']},{$oneUser['revenue']},{$roi}%,{$oneUser['commissionHistory']},{$oneUser['commission']}" . PHP_EOL;
        }
        $userContent .= "总合计,,,,,{$totalCommission}" . PHP_EOL;
        CommissionReport::getIns()->Insert(
            [
                'start_date'       => $dateArr[0],
                'end_date'         => end($dateArr),
                'name'             => $appOs[$os] . "-" . $dateArr[0] . "-" . end($dateArr) . "(" . $dateType . ").csv",
                'total_commission' => $totalCommission,
                'os'               => $os,
                'event_date'       => $period,
                'conf'             => json_encode($conf),
                'summary'          => json_encode($allUser),
                'link_detail'      => $content,
                'user_detail'      => $userContent,
            ]
        );

        // $fName = "/var/www/html/phplog/" . $dateArr[0] . "-" . end($dateArr) . "(" . $dateType . ").csv";
        // $file = fopen($fName, 'w');
        // fwrite($file, $content);
        // fclose($file);
    }

    public function compareHistory(&$all, $os, $period)
    {
        // dump($all);
        foreach ($all as $platform => &$row) {
            $rs = CommissionInfo::getIns()
                ->whereIn('event_date', array_keys($row))
                ->where('os', $os)
                ->where('platform', $platform)
                ->get()
                ->toArray();

            $hitDate = array_column($rs, 'event_date');

            $insert = [];
            $insertPerPeriod = [];

            foreach ($row as $dateF => $o) {
                if (in_array($dateF, $hitDate)) {
                    continue;
                }
                $ins = [

                    'event_date' => $dateF,
                    'os'         => $os,
                    'platform'   => $platform,
                    'info'       => json_encode(array_column($o, 'commission', 'link_id'))

                ];
                $insert[] = $ins;
                $insertPerPeriod[] = $ins + ['period' => $period, 'detail' => $o];
            }
            if (!empty($insert)) {
                CommissionInfo::getIns()->insert($insert);
                $this->fmtPeriodData($insertPerPeriod, $os);
            }


            $insertPerPeriod = [];
            foreach ($rs as $one) {
                $oneRow = $row[$one['event_date']];
                $info = json_decode($one['info'], true);

                $change = false;
                foreach ($oneRow as $user => $detail) {
                    $oldCommission = $info[$detail['link_id']] ?? 0;
                    $row[$one['event_date']][$user]['commissionHistory'] = $oldCommission;
                    $row[$one['event_date']][$user]['commission'] -= $oldCommission;
                    if ($row[$one['event_date']][$user]['commission'] < 0.2) {
                        unset($row[$one['event_date']][$user]);
                    }
                    if ($oldCommission != $detail['commission']) {
                        $change = true;
                    }
                    $info[$detail['link_id']] = $detail['commission'];
                }
                if ($change) {
                    $attr = [
                        'event_date' => $one['event_date'],
                        'os'         => $one['os'],
                        'platform'   => $one['platform'],
                    ];
                    $value = ['info' => json_encode($info)];
                    CommissionInfo::getIns()->updateOrInsert(
                        $attr,
                        $value
                    );
                    $insertPerPeriod[] = $attr + ['period' => $period, 'detail' => $row[$one['event_date']]];
                }
            }
            $this->fmtPeriodData($insertPerPeriod, $os);
        }
    }

    public function fmtPeriodData($arr, $os)
    {
        $attrItem = [
            'period',
            'event_date',
            'platform',
            'os',
        ];

        foreach ($arr as $one) {
            $attr = [];

            foreach ($attrItem as $item) {
                $attr[$item] = $one[$item];
            }

            foreach ($one['detail'] as $detail) {
                $change = [];
                $change['spend'] = $detail['spend'];
                $change['revenue'] = $detail['revenue'];
                $change['commission_history'] = $detail['commissionHistory'] ?? 0;
                $change['commission'] = $detail['commission'];
                $attr['union_link_id'] = 0;
                $attr['book_id'] = $detail['book_id'];
                $attr['code'] = $detail['code'];
                // 安卓处理
                if ($os == 0) {
                    $attr['union_link_id'] = $detail['link_id'];
                }
                CommissionPerPeriodInfo::getIns()->updateOrInsert($attr, $change);
            }
        }
    }

    public function exportIOS($systemInfos, $dateType, $sum, $dateArr, $fmtPrice, $fmtSpend, $fmtRatio)
    {
        $sysMap = array_column($systemInfos, 'name', 'platform');
        $all = $sum;

        $content = '';
        $totalCommission = 0;
        // $this->compareHistory($all, 1);
        // 找对应书籍的分布
        foreach ($all as $platform => $detail) {
            $content .= $sysMap[$platform] . PHP_EOL;
            $content .= 'Date / User,spend,revenue,roi,commission' . PHP_EOL;

            ksort($detail);

            $packageCommission = 0;
            foreach ($detail as $userDate => $oneMore) {
                $spend = round(($fmtSpend[$platform][$userDate] ?? 0) / 100, 2);
                $revenue = round(($fmtPrice[$platform][$userDate] ?? 0) / 100, 2);
                $roi = ($fmtRatio[$platform][$userDate] ?? 0) . "%";
                if ($dateType == '%Y%U') {
                    $us = $this->fmtWeek(substr($userDate, 0, 4), substr($userDate, 4));
                    $userDate = max($us[0], $dateArr[0]) . "-" . min($us[1], end($dateArr));
                }
                $content .= "{$userDate},{$spend},{$revenue},{$roi},-" . PHP_EOL;

                $oneCommission = 0;
                foreach ($oneMore as $user => $show) {
                    // $roi = intval($show['spend']) == 0 ? 0 : round($show['revenue'] * 100 / $show['spend'], 2);
                    $c = round($show['spend'] * 0.006, 2);
                    $content .= "{$user},{$show['spend']},-,-,{$c}" . PHP_EOL;
                    $oneCommission += $c;
                }
                $oneCommission = round($oneCommission, 2);
                $content .= "合计,,,,{$oneCommission}" . PHP_EOL . PHP_EOL;
                $packageCommission += $oneCommission;
            }
            $content .= PHP_EOL;
            $packageCommission = round($packageCommission, 2);
            $content .= "包合计,,,,{$packageCommission}" . PHP_EOL . PHP_EOL;
            $totalCommission += $packageCommission;
            $content .= PHP_EOL;
        }

        $totalCommission = round($totalCommission, 2);
        $content .= "总合计,'','','',{$totalCommission}" . PHP_EOL;
        $fName = "/var/www/html/phplog/ios" . $dateArr[0] . "-" . end($dateArr) . "(" . $dateType . ").csv";
        $file = fopen($fName, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    public function fmtWeek($year, $weeknum)
    {
        $firstdayofyear = mktime(0, 0, 0, 1, 1, $year);
        $firstweekday = date('N', $firstdayofyear);
        $firstweenum = date('W', $firstdayofyear);
        if ($firstweenum == 1) {
            $day = (1 - ($firstweekday - 1)) + 7 * ($weeknum - 1);
            $startdate = date('Ymd', mktime(0, 0, 0, 1, $day, $year));
            $enddate = date('Ymd', mktime(0, 0, 0, 1, $day + 6, $year));
        } else {
            $day = (8 - $firstweekday) + 7 * ($weeknum - 1);
            $startdate = date('Ymd', mktime(0, 0, 0, 1, $day, $year));
            $enddate = date('Ymd', mktime(0, 0, 0, 1, $day + 6, $year));
        }
        return [$startdate, $enddate];
    }

    public function calcByLink($androidLink, $dateArr, $dateType, $conf)
    {
        $defaultReturn = [
            [],
            [],
            [],
            [],
            [],
            [],
        ];
        $priceRs = AbroadDayRecharge::getIns()
            ->select(
                DB::raw("date_format(user_date, '$dateType' ) as date_f"),
                'platform',
                DB::raw('sum(money) as price'),
                'union_link_id'
            )
            ->whereIn('user_date', $dateArr)
            ->whereIn('union_link_id', $androidLink)
            ->groupBy([DB::raw("date_format(user_date, '$dateType' )"), 'platform'])
            ->get()
            ->toArray();

        if (empty($priceRs)) {
            return $defaultReturn;
        }


        // 获取每日成本数
        // $spendRs = FacebookDayAdSetData::getIns()
        //     ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setData')
        //     ->leftJoin(FacebookSet::getIns()->getTableName() . ' as set', 'set.sid', 'setData.sid')
        //     ->select(
        //         DB::raw("date_format(setData.event_date, '$dateType' ) as date_f"),
        //         'set.platform',
        //         DB::raw('sum(setData.spend) as spend'),
        //         'union_link_id'
        //     )
        //     ->whereIn('setData.event_date', $dateArr)
        //     ->whereIn('set.union_link_id', $androidLink)
        //     ->groupBy([DB::raw("date_format(setData.event_date, '$dateType' )"), 'set.platform'])
        //     ->get()
        //     ->toArray();

        $spendRs = AbroadLinkStatistics::getIns()
            ->select(
                DB::raw("date_format(date, '$dateType' ) as date_f"),
                DB::raw('round(sum(primecost),2) as spend'),
                'platform',
                'union_link_id'
            )
            ->whereIn('date', $dateArr)
            ->whereIn('union_link_id', $androidLink)
            ->groupBy([DB::raw("date_format(date, '$dateType' )"), 'platform'])
            ->get()
            ->toArray();
        if (empty($spendRs)) {
            return $defaultReturn;
        }
        $fmtSpend = [];
        foreach ($spendRs as $row) {
            if ($row['platform'] === null) {
                continue;
            }
            $fmtSpend[$row['platform']][$row['date_f']] = $row['spend'];
        }

        // // 回本率 >110% 即为合格 ->得到对应platform的回本日期 -- 换算成港分
        $back = [];
        $fmtPrice = [];
        $fmtRatio = [];
        $unReachDateF = [];
        $unReachDateAll = [];
        foreach ($priceRs as $row) {
            $spend = floatval($fmtSpend[$row['platform']][$row['date_f']] ?? 0);

            if (empty($spend) || !Tool::get($conf, $row['platform'])) {
                continue;
            }
            $linkBackRatio = $conf[$row['platform']]['reach'];
            $ratio = round($row['price'] * 100 / $spend, 2);
            $fmtRatio[$row['platform']][$row['date_f']] = $ratio;
            $fmtPrice[$row['platform']][$row['date_f']] = $row['price'];
            if ($ratio < $linkBackRatio) {
                if (!in_array($row['date_f'], $unReachDateF[$row['platform']] ?? [])) {
                    $unReachDateF[$row['platform']][] = $row['date_f'];
                }
                if (!in_array($row['date_f'], $unReachDateAll)) {
                    $unReachDateAll[] = $row['date_f'];
                }

                continue;
            }
            // $personRatio = $conf[$row['platform']]['successOne'];
            $back[$row['platform']][] = $row['date_f'];
        }

        if (empty($back)) {
            return [[], $unReachDateF, $unReachDateAll, $fmtPrice, $fmtSpend, $fmtRatio];
        }
        // $personRatio = $conf[$row['platform']]['successOne'];
        $commissionRate = 0.006; // 提成
        $sum = [];
        $manger = OldAdminManager::getIns()->get()->toArray();
        $codeArr = array_column($manger, null, 'uid');
        $usd = DBService::getIns()->getUSD2HKD();
        foreach ($back as $platform => $hitDate) {
            // $rs = FacebookDayAdSetData::getIns()
            //     ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setData')
            //     ->leftJoin(FacebookSet::getIns()->getTableName() . ' as set', 'set.sid', 'setData.sid')
            //     ->select(
            //         'set.user',
            //         DB::raw("date_format(setData.event_date, '$dateType' ) as date_f"),
            //         DB::raw('sum(setData.spend) as spend'),
            //         DB::raw('sum(setData.revenue) as revenue')
            //     // DB::raw('if (setData . spend,convert(sum(setData . revenue) * 100 / sum(setData . spend), decimal(5, 2)),spend) as roi')
            //     )
            //     ->whereIn($rowName, $hitDate)
            //     ->where('set.platform', $platform)
            //     ->where('set.os', 0)
            //     ->groupBy([DB::raw("date_format(setData.event_date, '$dateType' )"), 'set.user'])
            //     ->get()
            //     ->toArray();

            // 获取成本
            $rowName = DB::raw("date_format(date, '$dateType' )");
            $sRs = AbroadLinkStatistics::getIns()
                ->from(AbroadLinkStatistics::getIns()->getTableName() . " as statistics")
                ->leftJoin(AbroadLink::getIns()->getTableName() . ' as link', 'link.union_link_id', 'statistics.union_link_id')
                ->select(
                    'link.promoter_user_id',
                    DB::raw("date_format(date, '$dateType' ) as date_f"),
                    DB::raw('round(sum(primecost),2) as spend'),
                    'link.platform',
                    'link.union_link_id'
                )
                ->whereIn($rowName, $hitDate)
                ->whereIn('link.union_link_id', $androidLink)
                ->where('statistics.platform', $platform)
                ->groupBy([$rowName, 'link.promoter_user_id'])
                ->get()
                ->toArray();

            // 获取充值
            $rowName = DB::raw("date_format(recharge.user_date, '$dateType' )");
            $pRs = AbroadDayRecharge::getIns()
                ->from(AbroadDayRecharge::getIns()->getTableName() . " as recharge")
                ->leftJoin(AbroadLink::getIns()->getTableName() . ' as link', 'link.union_link_id', 'recharge.union_link_id')
                ->select(
                    'link.promoter_user_id',
                    DB::raw("date_format(recharge.user_date, '$dateType' ) as date_f"),
                    'link.platform',
                    DB::raw('sum(recharge.money) as revenue'),
                    'link.union_link_id'
                )
                ->whereIn($rowName, $hitDate)
                ->whereIn('link.union_link_id', $androidLink)
                ->where('link.platform', $platform)
                ->groupBy([$rowName, 'link.promoter_user_id'])
                ->get()
                ->toArray();

            if (empty($sRs) || empty($pRs)) {
                continue;
            }
            $fmtSSpend = [];
            foreach ($sRs as $row) {
                $fmtSSpend[$row['date_f']][$row['promoter_user_id']] = $row['spend'];
            }
            foreach ($pRs as $one) {
                $one['revenue'] = round($one['revenue'] / 100 / $usd, 2);
                $one['spend'] = round($fmtSSpend[$one['date_f']][$one['promoter_user_id']] / 100 / $usd, 2);
                $userInfo = $codeArr[$one['promoter_user_id']] ?? [];
                if (empty($userInfo)) {
                    $one['user'] = $one['promoter_user_id'];
                } else {
                    $one['user'] = strtolower($userInfo['code']) ?: $userInfo['nickname'];
                }
                $personRatio = $conf[$one['platform']]['successOne'];
                $roi = $one['spend'] ? round($one['revenue'] * 100 / $one['spend'], 2) : 0;
                if ($roi < $personRatio) {
                    continue;
                }

                $one['commission'] = round($one['spend'] * $commissionRate, 2);
                $sum[$platform][$one['date_f']][$one['user']] = $one;
            }
        }

        return [$sum, $unReachDateF, $unReachDateAll, $fmtPrice, $fmtSpend, $fmtRatio];
        // $sysMap = array_column($systemInfos, 'name', 'platform');
        // $content = '';
        // foreach ($sum as $platform => $detail) {
        //     $content .= $sysMap[$platform] . PHP_EOL;
        //     $content .= 'Date / User,spend,revenue,roi,commission' . PHP_EOL;
        //
        //     ksort($detail);
        //     foreach ($detail as $userDate => $oneMore) {
        //         $spend = round($fmtSpend[$platform][$userDate], 2);
        //         $revenue = round($fmtPrice[$platform][$userDate] / 100, 2);
        //         $roi = $fmtRatio[$platform][$userDate] . "%";
        //         if ($dateType == '%Y%U') {
        //             $us = $this->fmtWeek(substr($userDate, 0, 4), substr($userDate, 4));
        //             $userDate = max($us[0], $dateArr[0]) . "-" . min($us[1], end($dateArr));
        //         }
        //         $content .= "{$userDate},{$spend},{$revenue},{$roi},-" . PHP_EOL;
        //         foreach ($oneMore as $user => $show) {
        //             $roi = intval($show['spend']) == 0 ? 0 : round($show['revenue'] * 100 / $show['spend'], 2);
        //             $content .= "{$user},{$show['spend']},{$show['revenue']},{$roi}%,{$show['commission']}" . PHP_EOL;
        //         }
        //     }
        //     $content .= PHP_EOL;
        // }
        //
        // $fName = "/var/www/html/phplog/" . $dateArr[0] . "-" . end($dateArr) . "(" . $dateType . ").csv";
        // $file = fopen($fName, 'w');
        // fwrite($file, $content);
        // fclose($file);
    }


    public function calcByLinkV2($conf, $dateType, $dateArr, $androidLink)
    {
        if (empty($dateArr)) {
            return [];
        }

        // 获取ROI
        $spendRs = FacebookDayAdSetData::getIns()
            ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setData')
            ->leftJoin(FacebookSet::getIns()->getTableName() . ' as set', 'set.sid', 'setData.sid')
            ->select(
                DB::raw("date_format(setData.event_date, '$dateType' ) as date_f"),
                'set.platform',
                'set.book_id',
                'set.user',
                DB::raw('sum(setData.spend) as spend'),
                DB::raw('sum(setData.revenue) as revenue'),
                DB::raw('IF(sum(setData.spend),convert(sum(setData.revenue)*100/sum(setData.spend),decimal(10,2)),0) as roi'),
                'union_link_id'
            )
            ->whereIn('setData.event_date', $dateArr)
            ->whereIn('union_link_id', $androidLink)
            ->groupBy([DB::raw("date_format(setData.event_date, '$dateType' )"), 'union_link_id'])
            ->get()
            ->toArray();

        if (empty($spendRs)) {
            return [];
        }
        $bookIds = Tool::getUniqueArr($spendRs, 'book_id', true);
        $bookRs = Book::getIns()->listByCond(['book_id' => ['in', $bookIds]], ['sex_type', 'is_complete', 'book_id']);
        $bookRsMap = array_column($bookRs, null, 'book_id');
        $commissionRate = 0.006; // 提成
        $hit = [];
        foreach ($spendRs as $row) {
            if (!Tool::get($bookRsMap, $row['book_id'])) {
                echo implode(",", $row) . PHP_EOL;
                echo '书库没有ID为' . $row['book_id'] . '的书' . PHP_EOL;
                continue;
            }
            $bookInfo = $bookRsMap[$row['book_id']];
            $isShort = Tool::get($bookInfo, 'is_complete'); // 0 长篇 1 短篇
            $isFeMale = Tool::get($bookInfo, 'sex_type'); //0 男生频道  1 女生频道'
            if ($isShort) {
                $linkBackRatio = $conf[$row['platform']]['short'];
            } else {
                $linkBackRatio = $isFeMale ? $conf[$row['platform']]['female'] : $conf[$row['platform']]['male'];
            }

            if ($row['roi'] < $linkBackRatio) {
                continue;
            }

            $hit[$row['platform']][$row['date_f']][$row['user']]['spend'] = ($hit[$row['platform']][$row['date_f']][$row['user']]['spend'] ?? 0) + $row['spend'];
            $hit[$row['platform']][$row['date_f']][$row['user']]['revenue'] = ($hit[$row['platform']][$row['date_f']][$row['user']]['revenue'] ?? 0) + $row['revenue'];
            $hit[$row['platform']][$row['date_f']][$row['user']]['commission'] = round($hit[$row['platform']][$row['date_f']][$row['user']]['spend'] * $commissionRate, 2);
            $hit[$row['platform']][$row['date_f']][$row['user']]['link'][] = ApiService::rebuildPlatformLink($row['platform'], $row['union_link_id']) . "({$row['revenue']}/{$row['spend']}/{$row['roi']})-{$isShort}{$isFeMale}";
        }

        return $hit;
    }

    public function calcByLinkV1($androidLink, $unReachDateAll, $dateArr, $dateType, $conf, $unReach, $sum)
    {
        if (empty($dateArr)) {
            return [];
        }

        $rowName = DB::raw("date_format(user_date, '$dateType' )");
        $priceRs = AbroadDayRecharge::getIns()
            ->select(
                DB::raw("date_format(user_date, '$dateType' ) as date_f"),
                'platform',
                DB::raw('sum(money) as price'),
                'union_link_id'
            )
            ->whereIn('user_date', $dateArr)
            ->whereIn('union_link_id', $androidLink)
            ->groupBy([$rowName, 'union_link_id'])
            ->get()
            ->toArray();

        if (empty($priceRs)) {
            return [];
        }


        // // 获取每日成本数
        // $rowSpendName = DB::raw("date_format(setData.event_date, '$dateType' )");
        // $spendRs = FacebookDayAdSetData::getIns()
        //     ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setData')
        //     ->leftJoin(FacebookSet::getIns()->getTableName() . ' as set', 'set.sid', 'setData.sid')
        //     ->select(
        //         DB::raw("date_format(setData.event_date, '$dateType' ) as date_f"),
        //         'set.user',
        //         'set.platform',
        //         DB::raw('sum(setData.spend) as spend'),
        //         'union_link_id'
        //     )
        //     ->whereIn($rowSpendName, $dateArr)
        //     ->whereIn('set.union_link_id', $androidLink)
        //     ->groupBy([$rowSpendName, 'union_link_id'])
        //     ->get()
        //     ->toArray();

        $rowName = DB::raw("date_format(date, '$dateType' )");
        $spendRs = AbroadLinkStatistics::getIns()
            ->from(AbroadLinkStatistics::getIns()->getTableName() . " as statistics")
            ->leftJoin(AbroadLink::getIns()->getTableName() . ' as link', 'link.union_link_id', 'statistics.union_link_id')
            ->select(
                'link.promoter_user_id',
                'link.book_id',
                DB::raw("date_format(date, '$dateType' ) as date_f"),
                DB::raw('round(sum(primecost),2) as spend'),
                'link.platform',
                'link.union_link_id'
            )
            ->whereIn('date', $dateArr)
            ->whereIn('link.union_link_id', $androidLink)
            ->groupBy([$rowName, 'link.union_link_id'])
            ->get()
            ->toArray();
        if (empty($spendRs)) {
            return [];
        }

        $usd = DBService::getIns()->getUSD2HKD();
        $fmtSpend = [];
        foreach ($spendRs as $row) {
            if ($row['platform'] === null) {
                continue;
            }
            $fmtSpend[$row['platform']][$row['date_f']][$row['union_link_id']] = $row['spend'];
        }


        $fFmtPrice = [];
        foreach ($priceRs as $row) {
            if ($row['platform'] === null) {
                continue;
            }
            $fFmtPrice[$row['platform']][$row['date_f']][$row['union_link_id']] = $row['price'];
        }

        $reachCode = [];
        foreach ($sum as $platform => $one) {
            foreach ($one as $dateF => $detail) {
                $reachCode[$platform][$dateF] = array_keys($detail);
            }
        }
        $rs = OldAdminManager::getIns()->get()->toArray();
        $userLinkMap = array_column($rs, 'code', 'uid');

        // // 回本率 >110% 即为合格 ->得到对应platform的回本日期 -- 换算成港分
        $back = [];
        $fmtRatio = [];
        $linkInfo = [];
        // 以消耗为主
        foreach ($spendRs as $row) {
            if (empty(floatval($row['spend']))) {
                continue;
            }
            $price = $fFmtPrice[$row['platform']][$row['date_f']][$row['union_link_id']] ?? 0;


            $spend = $row['spend'];
            $ratio = round($price * 100 / $spend, 2);
            $linkBackRatio = $conf[$row['platform']]['failedOne'];
            // $linkSuccBackRatio = $conf[$row['platform']]['successOne'];

            if (in_array($row['date_f'], $unReach[$row['platform']] ?? []) && $ratio < $linkBackRatio) {
                continue;
            }
            // if (!in_array($row['date_f'], $unReach[$row['platform']] ?? []) && $ratio < $linkSuccBackRatio) {
            //     continue;
            // }
            $fmtRatio[$row['platform']][$row['date_f']][$row['union_link_id']] = $ratio;
            // $fmtPrice[$row['platform']][$row['date_f']][$row['union_link_id']] = $row['price'];
            $back[$row['platform']][$row['date_f']][] = $row['union_link_id'];
            $linkInfo[] = $row['union_link_id'];
        }


        if (empty($back)) {
            return [];
        }


        $linkRs = AbroadLink::getIns()->whereIn('union_link_id', $linkInfo)->get()->toArray();
        $linkMap = array_column($linkRs, 'promoter_user_id', 'union_link_id');
        $bookRs = array_column($linkRs, 'book_id', 'union_link_id');

        $commissionRate = 0.006; // 提成
        $sum = [];
        foreach ($back as $platform => $hitDate) {
            foreach ($hitDate as $days => $links) {
                $allSpend = $fmtSpend[$platform][$days] ?? [];
                $allPrice = $fFmtPrice[$platform][$days] ?? [];
                $allRatio = $fmtRatio[$platform][$days] ?? [];
                $enterCode = $reachCode[$platform][$days] ?? [];
                foreach ($links as $linkId) {
                    $code = strtolower($userLinkMap[$linkMap[$linkId]] ?? '');
                    if (!in_array($days, $unReach[$platform] ?? []) && !in_array($code, $enterCode)) {
                        continue;
                    }
                    $temp = [];


                    $temp['spend'] = round(($allSpend[$linkId] ?? 0) / 100 / $usd, 2);
                    $temp['revenue'] = round(($allPrice[$linkId] ?? 0) / 100 / $usd, 2);
                    $temp['roi'] = $allRatio[$linkId] ?? 0;
                    $temp['commission'] = round($temp['spend'] * $commissionRate, 2);
                    $lId = ApiService::rebuildPlatformLink($platform, $linkId);
                    $temp['user'] = $code . "-" . $lId;
                    $temp['link_id'] = $linkId;
                    $temp['code'] = $code;
                    $temp['book_id'] = $bookRs[$linkId] ?? 0;
                    $sum[$platform][$days][$temp['user']] = $temp;
                }
            }
        }

        return $sum;
        // $sysMap = array_column($systemInfos, 'name', 'platform');
        // $content = '';
        // foreach ($sum as $platform => $detail) {
        //     $content .= $sysMap[$platform] . PHP_EOL;
        //     $content .= 'Date / User,spend,revenue,roi,commission' . PHP_EOL;
        //
        //     ksort($detail);
        //     foreach ($detail as $userDate => $oneMore) {
        //         if ($dateType == '%Y%U') {
        //             $us = $this->fmtWeek(substr($userDate, 0, 4), substr($userDate, 4));
        //             $userDate = max($us[0], $dateArr[0]) . "-" . min($us[1], end($dateArr));
        //         }
        //         $content .= "{$userDate},-,-,-,-" . PHP_EOL;
        //         foreach ($oneMore as $user => $show) {
        //             $content .= "{$user},{$show['spend']},{$show['revenue']},{$show['roi']}%,{$show['commission']}" . PHP_EOL;
        //         }
        //     }
        //     $content .= PHP_EOL;
        // }
        //
        // $fName = "/var/www/html/phplog/link_" . $dateArr[0] . "-" . end($dateArr) . "(" . $dateType . ").csv";
        // $file = fopen($fName, 'w');
        // fwrite($file, $content);
        // fclose($file);
    }

    // public function handle()
    // {
    //     // $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid', 'platform', 'id', 'bm_id', 'timezone_offset_hours_utc']);
    //     $aidRs = [2840940832843037,339122871318484];
    //     $active = [];
    //     $miss = [];
    //     foreach ($aidRs as $aid) {
    //         $rs = CurlService::getIns()->curlRequest(
    //             Tool::fmtAid($aid) . ' / insights',
    //             ['time_range' => ['since' => '2022 - 01 - 01', 'until' => '2022 - 03 - 31']]
    //         );
    //         if (Tool::get($rs[2], 'data')) {
    //             $active[] = $aid;
    //         }
    //         if (!$rs[0]) {
    //             $miss[] = $aid;
    //         }
    //     }
    //     dump(json_encode($active));
    //     dump(json_encode($miss));
    //     FacebookAdAccount::getIns()->updateByCond(['aid' => ['in', $active]], ['is_active' => 1]);
    // }
}
