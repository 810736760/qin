<?php

namespace App\Console\Commands\Outside\Amo;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\Outside\Amo\AmoOrders;
use App\Models\Outside\Amo\AmoUsers;
use App\Services\Outside\Amo\CurlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOrderCommand extends Command
{
    protected $signature = 'outside:AmoSyncOrder {date?} {id?}';

    protected $description = '同步番茄订单回传数据';


    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        // 番茄0时区
        $date = Tool::get($args, 'date', date("Ymd", time() - 8 * 3600));
        $id = Tool::get($args, 'id', 1);

        $this->main($date, $id);

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }


    public function main($date, $id)
    {
        $startTime = strtotime($date);
        $path = 'order';
        $params = [
            'begin_time' => $startTime,
            'end_time'   => $startTime + 86399,
            'page_num'   => 0,
            'page_size'  => 100

        ];
        // 通过Api获取数据
        $serv = new CurlService($id);
        do {
            $params['page_num']++;
            [$status, $msg, $rs, $code] = $serv->fetchCurl($path, $params);
            if ($status != ReturnCode::SUCCEED || empty(Tool::get($rs['result'] ?? [], 'order_infos'))) {
                return;
            }

            $lastPage = ceil($rs['result']['total_num'] / $params['page_size']);

            $allColumn = AmoOrders::getIns(0)->getTableColumn();

            $list = [];
            foreach ($rs['result']['order_infos'] as $row) {
                $one = [];
                foreach ($row as $key => $value) {
                    if (!in_array($key, $allColumn)) {
                        continue;
                    }
                    // if (Tool::isInclude($key, '_time') && is_numeric($value)) {
                    //     $value = date("Y-m-d H:i:s", $value);
                    // }
                    if (Tool::isInclude($key, '_time') && is_numeric($value)) {
                        $keyAt = str_replace('_time', '_at', $key);
                        $one[$keyAt] = date("Y-m-d H:i:s", $value - 28800);
                    }
                    if ($key == 'promotion_url') {
                        $one['code'] = str_replace('?code=', '', $value);
                    }
                    $one[$key] = $value;
                }

                if (empty($one)) {
                    continue;
                }
                $list[$one['order_id']] = $one;
            }

            if (empty($list)) {
                return;
            }
            $userList = Tool::getUniqueArr($list, 'user_id', true);
            $userRs = AmoUsers::getIns(0)->listByCond(
                ['user_id' => ['in', $userList]]
            );

            $userMap = array_column($userRs, null, 'user_id');
            $orderExist = AmoOrders::getIns(0)->listByCond(['order_id' => ['in', array_keys($list)]]);
            $orderExistMap = array_column($orderExist, null, 'order_id');
            foreach ($list as $row) {
                // 归因时间不可改变
                $hit = Tool::get($orderExistMap, $row['order_id']);
                if (empty($hit) || empty($hit['user_created_time'])) {
                    $userInfo = Tool::get($userMap, $row['user_id']);
                    if (!empty($userInfo)) {
                        $row['user_created_time'] = $userInfo['create_time'];
                        $row['user_created_at'] = $userInfo['create_at'];
                        $row['user_dyeing_time'] = $userInfo['effective_time'];
                        $row['user_dyeing_at'] = $userInfo['effective_at'];
                    }
                    $row['ack'] = $row['order_status'] == AmoOrders::AMO_PAY_STATUS_SUCCESS ?
                        AmoOrders::AMO_PAY_ACK_SUCCESS : AmoOrders::AMO_PAY_ACK_WAIT; // 0 代表不需要检验
                } elseif ($hit['order_status'] == 1) {
                    $row['ack'] = $row['order_status'] == AmoOrders::AMO_PAY_STATUS_SUCCESS ?
                        AmoOrders::AMO_PAY_ACK_SUCCESS : AmoOrders::AMO_PAY_ACK_WAIT; // 已支付时需要改变标签
                }
                AmoOrders::getIns(0)->updateOrInsert(
                    ['order_id' => $row['order_id']],
                    $row
                );
            }
        } while ($params['page_num'] < $lastPage);
    }
}
