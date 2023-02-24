<?php

namespace App\Console\Commands\Outside\Amo;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Outside\Amo\AmoCodes;
use App\Models\Outside\Amo\AmoCodesLinkMap;
use App\Models\Outside\Amo\AmoOrders;
use App\Models\Outside\Amo\AmoUsers;
use App\Models\Stat\AbroadDayRecharge;
use App\Models\Stat\AbroadDayRechargePayNum;
use App\Models\Stat\AbroadLinkStatistics;
use App\Services\ApiService;
use App\Services\Common\DBService;
use App\Services\Facebook\CurlService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatCommand extends Command
{
    protected $signature = 'outside:AmoStat {date?} {end_date?}{runMiss?}';

    protected $description = '统计番茄数据';


    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        // 番茄0时区
        $startDate = Tool::get($args, 'date', date("Ymd", time() - 8 * 3600));
        $endDate = Tool::get($args, 'end_date', date("Ymd", time() - 8 * 3600));
        $runMiss = Tool::get($args, 'runMiss', 0);
        $GLOBALS['co'] = 1;
        if ($runMiss) {
            $this->matchMiss();
        } else {
            // 先统计遗漏的link
            Artisan::call('outside:AmoFillLinkIdByCode');
            for ($curTime = strtotime($startDate); $curTime <= strtotime($endDate); $curTime += 86400) {
                $date = date("Ymd", $curTime);
                dump($date);
                // 统计abroad_link_statistics
                $this->statLinkStat($date);
                $this->statAbroadDayRecharge($date);
                usleep(20000);
            }
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }


    // 查找缺失数据
    public function matchMiss()
    {
        $missSids = [
            23851896324790279
            , 23851410800100529, 23851906717490279
            , 23851896513200279
            , 23851411180720529
            , 23851419926300529
            , 23851906978980279
            , 23851906978650279
            , 23851906978520279
            , 23851420289780529
            , 23851420289480529
            , 23851977673730279
            , 23851977817380279
            , 23851473851570529
            , 23851473851470529
            , 23852002194090279
            , 23852002193840279
            , 23852016956970279
            , 23852016953540279
            , 23851936038730631
            , 23851936038520631
            , 23851936038330631
            , 23851947034060631
            , 23851947033860631
            , 23851947033640631
            , 23852103807840279
            , 23852103806650279
            , 23852010051580631
            , 23852010051420631
            , 23851485645040529
            , 23851485644890529
            , 23851485513210529
            , 23851497378250529
            , 23851497133530529
            , 23851497649800529
            , 23851497649520529
            , 23852016817070279
            , 23852016816940279
            , 23851512165930529
            , 23851512165580529
            , 23852029941370279
            , 23852029905700279
            , 23853204489850241
            , 23853204489450241
            , 23853204412550241
            , 23853205192660241
            , 23853205192410241
            , 23853205192180241
            , 23853233452760241
            , 23853233396790241
            , 23853247104780241
            , 23853247104000241
            , 23853259363080241
            , 23853259362690241
            , 23853271381530241
            , 23853271347520241
            , 23853282910830241
            , 23853282883440241
            , 23853282851110241
            , 23852414095210669
            , 23852414094970669
            , 23851886419650631
            , 23851712759340377
            , 23852134281750279
            , 23852225785260279
            , 23851702981830529
            , 23851702981630529
            , 23852514414890034
            , 23852514414780034
            , 23851729981100529
            , 23851729980980529
            , 23852266602010279
            , 23852266601220279
            , 23852571304990034
            , 23852571304390034
            , 23851941213370377
            , 23851941503600377
            , 23851492855490407
            , 23851492855270407
            , 23851492855100407
            , 23851492821360407
            , 23853807005060734
            , 23853806847140734
            , 23853807155950734
            , 23853807155680734
            , 23853816433970734
            , 23853841753390734
            , 23853841752880734
            , 23853841752430734
            , 23853851000380734
            , 23854116909530734
            , 23854173648210734
            , 23854173648280734
            , 23852869331930585
            , 23852869549790585
            , 23852869505470585
            , 23852881232740585
            , 23852881232490585
            , 23852903637770585
            , 23852903797860585
            , 23852911136170585
            , 23852911136000585
            , 23852923053910585
            , 23852922965980585
            , 23852932604840585
            , 23852932604580585
            , 23852932604290585
            , 23852941387370585
            , 23852941387190585
            , 23852950104840585
            , 23852950104700585
            , 23852955650520585
            , 23852955650340585
            , 23852955650200585
            , 23852965293340585
            , 23852965293230585
            , 23852709124990698
            , 23854051044860734
            , 23852434944630688
            , 23852527277290688
            , 23852533040680316
            , 23852925850620632
            , 23852925724440632
            , 23852968943540632
            , 23852968943440632
            , 23852709124990698
            , 23852533040680316

        ];
        $arrayChunks = array_chunk($missSids, 50);
        foreach ($arrayChunks as $miss) {
            [$status, $curlMsg, $res] = CurlService::getIns()->curlRequest(
                '',
                [
                    'fields' => 'name,account_id',
                    'ids'    => implode(',', $miss)
                ],
                'EAAHCZAJ8ZAhBMBAObI8kng0rwLF6U5K8tZA98psUOL7a7fsUqA54pxuK9AiwTTGZCW0YIiGfe8QdO18Qt2UAD0spnQUv7CDbRCgdd99Whw7fDdZC2iU92VZBA1HfU0dCbU4IGwefnLKtBcuHhcZC8qr1UuGO5ZCGqXYVkqbZCqzMUcpXp9ZBHyTNIc'
            );
            foreach ($res as $sid => $row) {
                preg_match('/(?<={).+(?=})/', $row['name'], $matchSet);
                $explodeSet = explode('/', $matchSet[0] ?? '');
                preg_match('/\d+/', $explodeSet[1], $setLinkId);
                echo $row['account_id'] . ',' . $sid . ',' . $row['name'] . ',' . $setLinkId[0] . PHP_EOL;

                // dump([
                //     'name'          => $row['name'],
                //     'link_id'       => $setLinkId[0],
                //     'union_link_id' => ApiService::buildPlatformLink(100, $setLinkId[0])
                // ]);
                FacebookSet::getIns()->updateByCond(
                    ['sid' => $sid],
                    [
                        'name'          => $row['name'],
                        'link_id'       => $setLinkId[0],
                        'union_link_id' => ApiService::buildPlatformLink(100, $setLinkId[0])
                    ]
                );
            }
        }
        //

        Artisan::call('outside:AmoFillLinkIdByCode');
        $date = '20221019';
        $endTime = '20230217';
        // $endTime = date("Ymd", strtotime($date) + 86400);
        // 当日订单信息
        $orders = AmoOrders::getIns()
            ->from(AmoOrders::getIns()->getTableName() . ' as o')
            ->leftJoin(AmoCodesLinkMap::getIns()->getTableName() . ' as link', 'link.code', 'o.code')
            ->selectRaw('
                sum(price) as price,
                count(distinct o.user_id) as count,
                count(o.order_id) as order_num,
                link.union_link_id as union_link_id,
                 o.code,
                date_format(o.user_dyeing_at, "%Y%m%d") as user_date
            ')
            ->whereBetween('o.pay_at', [$date, $endTime])
            ->where('o.order_status', AmoOrders::AMO_PAY_STATUS_SUCCESS)
            ->whereNull('union_link_id')
            // ->groupBy(['user_date', 'union_link_id'])
            ->groupBy(['code'])
            ->get()->toArray();
        // dump($orders);
        // dump(implode("','", array_column($orders, 'code')));
        $codeArr = array_column($orders, 'code');
        $arr = [];
        foreach ($codeArr as $code) {
            $rs = FacebookSet::getIns()
                ->from(FacebookSet::getIns()->getTableName() . ' as sets')
                ->leftJoin(AmoCodesLinkMap::getIns()->getTableName() . ' as map', 'map.union_link_id', 'sets.union_link_id')
                ->leftJoin(AmoCodes::getIns()->getTableName() . ' as code', 'code.code', 'map.code')
                ->select('aid', 'sid', 'name', 'map.code', 'promotion_id')
                ->where('name', 'like', '%' . $code . '%')
                ->get()
                ->toArray();
            $arr = array_merge($arr, $rs);
        }

        $str = 'aid,sid,name,code,id' . PHP_EOL;
        foreach ($arr as $row) {
            // $row['aid'] = "{$row['aid']} ";
            // $row['sid'] = "{$row['sid']} ";
            $row['aid'] = "'" . strval($row['aid']);
            $row['sid'] = "'" . strval($row['sid']);
            $row['promotion_id'] = "'" . strval($row['promotion_id']);

            $str .= $row['aid'] . ',' . $row['sid'] . ',' . $row['name'] . ',' . $row['code'] . ',' . $row['promotion_id'] . PHP_EOL;
        }
        echo $str;
        // $fName = "/var/www/html/phplog/缺失.csv";
        // $file = fopen($fName, 'w');
        // fwrite($file, $str);
        // fclose($file);
    }

    public function statLinkStat($date)
    {
        $endTime = date("Ymd", strtotime($date) + 86400);
        // 当日订单信息
        $orders = AmoOrders::getIns()
            ->from(AmoOrders::getIns()->getTableName() . ' as o')
            ->leftJoin(AmoCodesLinkMap::getIns()->getTableName() . ' as link', 'link.code', 'o.code')
            ->selectRaw('
                sum(price) as price,
                count(distinct o.user_id) as count,
                count(o.order_id) as order_num,
                link.union_link_id as union_link_id,
                 o.code,
                date_format(o.user_dyeing_at, "%Y%m%d") as user_date
            ')
            ->whereBetween('o.pay_at', [$date, $endTime])
            ->where('o.order_status', AmoOrders::AMO_PAY_STATUS_SUCCESS)
            ->groupBy(['user_date', 'union_link_id'])
            ->get()->toArray();
        // 当天新注册用户

        $users = AmoUsers::getIns()
            ->from(AmoUsers::getIns()->getTableName() . ' as u')
            ->leftJoin(AmoCodesLinkMap::getIns()->getTableName() . ' as link', 'link.code', 'u.code')
            ->selectRaw('count(user_id) as count, link.union_link_id')
            ->whereBetween('effective_at', [$date, $endTime])
            ->groupBy(['link.union_link_id'])
            ->pluck('count(user_id) as count', 'link.union_link_id')
            ->toArray();
        $fbSpend = FacebookSet::getIns()
            ->from(FacebookSet::getIns()->getTableName() . ' as a')
            ->join(FacebookDayAdSetData::getIns()->getTableName() . ' as b', 'a.sid', '=', 'b.sid')
            ->where('b.event_date', $date)
            ->where('a.platform', 100)
            ->selectRaw("
                sum(spend) as spend,
                union_link_id
            ")
            ->groupBy(['union_link_id'])
            ->pluck('spend', 'union_link_id')
            ->toArray();
        $linkIds = array_merge(array_column($orders, 'union_link_id'), array_keys($users), array_keys($fbSpend));
        $createData = [];
        $usd = DBService::getIns()->getUSD2HKD();
        foreach ($orders as &$v) {
            $linkId = $v['union_link_id'];
            $createData[$linkId]['date'] = $date;
            $createData[$linkId]['pnumber'] = $users[$linkId] ?? 0; //当日新增用户(遍历订单中的link)
            $createData[$linkId]['pay_num'] = isset($createData[$linkId]['pay_num'])
                ? $createData[$linkId]['pay_num'] + $v['count']
                : $v['count']; //当日付费人数
            $createData[$linkId]['recharge'] = isset($createData[$linkId]['recharge'])
                ? $createData[$linkId]['recharge'] + $v['price']
                : $v['price']; //当日充值金额

            if ($v['user_date'] == $date) {
                $createData[$linkId]['oneday_pay_num'] = isset($createData[$linkId]['oneday_pay_num'])
                    ? $createData[$linkId]['oneday_pay_num'] + $v['count']
                    : $v['count']; //当日新注册用户的付费人数
                $createData[$linkId]['oneday_money'] = isset($createData[$linkId]['oneday_money'])
                    ? $createData[$linkId]['oneday_money'] + $v['price']
                    : $v['price']; //当日新注册用户的充值金额
                $createData[$linkId]['oneday_order_num'] = isset($createData[$linkId]['oneday_order_num'])
                    ? $createData[$linkId]['oneday_order_num'] + $v['order_num']
                    : $v['order_num']; // 当日新增用户订单数
            }
        }
        foreach ($linkIds as $linkId) {
            if (empty($linkId)) {
                continue;
            }
            $val = $createData[$linkId] ?? [];
            $val['pnumber'] = $users[$linkId] ?? 0; // 当日新增用户(无订单但有新增用户，遍历当天新增用户中的link)
            $primecost = ($fbSpend[$linkId] ?? 0) * 100 * $usd; // 港分
            $val['oneday_money'] = ($val['oneday_money'] ?? 0) * $usd;
            $val['recharge'] = ($val['recharge'] ?? 0) * $usd;
            $val['platform'] = 100;
            $val['link_id'] = max($linkId - 100000000, 0);
            if ($primecost > 0) {
                $val['primecost'] = $primecost;
            }


            AbroadLinkStatistics::getIns()->updateOrCreate(
                ['union_link_id' => $linkId, 'date' => $date],
                $val
            );
        }
    }

    public function statAbroadDayRecharge($date)
    {
        // $start = date("Ymd", strtotime($date) - 7 * 86400);
        $start = $date;
        $end = date("Ymd", strtotime($date) + 86400);
        $usd = DBService::getIns()->getUSD2HKD();
        DB::beginTransaction();
        try {
            $rs = AmoOrders::getIns()
                ->from(AmoOrders::getIns()->getTableName() . ' as o')
                ->leftJoin(AmoCodesLinkMap::getIns()->getTableName() . ' as map', 'o.code', 'map.code')
                ->select(
                    'o.id',
                    'o.price',
                    'o.user_id',
                    'o.code',
                    DB::raw('date_format(o.user_dyeing_at, "%Y%m%d") as user_date'),
                    DB::raw('date_format(o.pay_at, "%Y%m%d") as order_date'),
                    'map.union_link_id'
                )
                ->whereBetween('pay_at', [$start, $end])
                ->where('ack', 1)
                ->whereNotNull('union_link_id')
                ->where('rsc', 0)
                ->orderBy('id')
                ->get()
                ->toArray();
            $ids = [];
            foreach ($rs as $row) {
                $this->doDayRecharge($row, $usd);
                $this->doStatPayNum($row);
                $ids[] = $row['id'];
            }
            AmoOrders::getIns()->updateByCond(['id' => ['in', $ids]], ['rsc' => 1]);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($this->description . '-->' . $exception->getMessage());
        }
    }

    public function doDayRecharge($row, $usd)
    {
        AbroadDayRecharge::getIns()->record(
            max($row['union_link_id'] - 100000000, 0),
            $row['user_date'],
            $row['order_date'],
            $row['price'] * $usd,
            100,
            $row['union_link_id']
        );
    }

    public function doStatPayNum($order)
    {
        $linkId = $order['union_link_id']; // 当前归因链接
        $userDate = $order['user_date']; // 当前归因日期

        $count = AmoOrders::getIns()
            ->where('order_status', 1)
            ->where('id', '<=', $order['id'])
            ->where('code', $order['code'])
            ->where('user_id', $order['user_id'])
            ->count('id');

        $payNum = 0;
        $twice = 0;
        $triple = 0;
        $quartic = 0;
        switch ($count) {
            case 0:
                Log::info("统计2/3/4付费数，累计充值人数未计数：付费次数$count, ", $order);
                break;
            case 1:
                $payNum = 1;
                break;
            case 2:
                $twice = 1;
                break;
            case 3:
                $triple = 1;
                break;
            case 4:
                $quartic = 1;
                break;

            default:
                break;
        }

        // 统计表
        AbroadDayRechargePayNum::getIns()
            ->updateOrCreate(
                [
                    'user_date'     => $userDate,
                    'union_link_id' => $linkId,

                ],
                [
                    'link_id'  => max($linkId - 100000000, 0),
                    'platform' => 100,
                    'twice'    => DB::raw('twice + ' . $twice),
                    'triple'   => DB::raw('triple + ' . $triple),
                    'quartic'  => DB::raw('quartic + ' . $quartic),
                    'pay_num'  => DB::raw('pay_num + ' . $payNum),
                ]
            );
    }
}
