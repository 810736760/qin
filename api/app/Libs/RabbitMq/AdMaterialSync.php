<?php

namespace App\Libs\RabbitMq;

use App\Helper\Tool;
use App\Libs\RedisKey;
use App\Models\Admin_Manager;
use App\Libs\RabbitMq\BaseConsumer;
use App\Models\Facebook\UserAccountsMap;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Services\Facebook\CommonService;
use App\Models\Facebook\FacebookAdAccount;

class AdMaterialSync extends BaseConsumer
{
    protected $exchangeName = 'AdMaterialSync'; // 交换机名

    protected $exchangeType = AMQP_EX_TYPE_FANOUT; // 交换机类型

    protected $queueName = 'AdMaterialSync'; // 队列名

    protected $routeKey = ''; // 路由key

    protected function handleMessage($message)
    {
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->main();
        }
    }

    protected function main()
    {
        $syncStatus = Tool::fmtCoIdKey(RedisKey::MATERIAL_SYNC_STATUS);
        $syncList = Tool::fmtCoIdKey(RedisKey::MATERIAL_SYNC_LIST);
        try {

            // 带宽等因素，不能同时多素材同步
            $status = Redis::get($syncStatus);
            if (!$status) {
                Log::info('=========================素材同步开始=============================');
                Redis::set($syncStatus, 1);

                $materials = Redis::hgetAll($syncList);
                Log::info('同步信息' . json_encode($materials));
                foreach ($materials as $userId => $materialIds) {
                    // 获取活跃账户
                    $userInfo = Admin_Manager::query()->where('id', $userId)->first();
                    if (empty($userInfo)) {
                        continue;
                    }
                    $userInfo = $userInfo->toArray();
                    if (!in_array($userInfo['power'], UserService::CREATOR_GROUP)) {
                        continue;
                    }
                    $aids = AdAccountService::getAccountsByUid($userInfo['id'], $userInfo['power']);
                    Log::info('同步' . $userId . "的账户" . json_encode($aids));
                    if (empty($aids)) {
                        continue;
                    }
                    Log::info("用户id:$userId, 素材id：" . json_encode($materialIds));
                    // 同步素材
                    $materialIds = json_decode($materialIds, true);
                    foreach ($materialIds as $materialId) {
                        foreach ($aids as $accountId) {
                            $GLOBALS['bmId'] = $accountId['bm_id'];
                            Log::info($accountId['aid'] . '_' . $materialId);
                            $commonService = new CommonService();
                            $commonService->getHashIdByMid($accountId['aid'], $materialId);
                        }
                    }
                    Redis::hdel($syncList, $userId);
                }

                Redis::set($syncStatus, 0);
                Log::info('=========================素材同步结束=============================');
            }
        } catch (\Exception $e) {
            Redis::set($syncStatus, 0);
            Log::info('队列MaterialSync错误：' . $e->getMessage());
        }
    }
}
