<?php

namespace App\Console\Commands\Facebook\Task;

use App\Helper\Tool;
use App\Libs\RabbitMq\Producer;
use App\Models\Facebook\DraftCampaignModel;
use App\Models\Facebook\DraftAdSetModel;
use App\Models\Facebook\DraftAdModel;
use App\Models\Task\Task;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\TaskService;
use App\Services\RedisService;
use Illuminate\Console\Command;

/**
 * 监控任务执行情况 对于超时未执行的 重置状态 并重新加入MQ
 * Class Monitor
 * @package App\Console\Commands\Facebook\Task
 */
class Monitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:Monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '任务监控';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $args = $this->arguments();
        \Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->main();
            $this->checkFailed();
        }

        \Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main()
    {
        // 检测超时任务并重置 10分钟一次
        $checkTimeStart = date("Y-m-d H:i:s", time() - 7200);


        $rs = Task::getIns()->listByCond(
            [
                'status'     => ['in', [Task::STATUS_RUNNING, Task::STATUS_BUILD]],
                'created_at' => ['>', $checkTimeStart]
            ]
        );
        if (empty($rs)) {
            return;
        }
        $reRun = [];
        $curTime = time();
        $expireTime = 600;
        foreach ($rs as $row) {
            // 大于10分钟
            if ($curTime - strtotime($row['created_at']) < $expireTime) {
                continue;
            }
            $reRun[] = $row;
        }
        if (!$reRun) {
            return;
        }

        $overTimeTask = [];
        $redis = RedisService::getIns();
        // 每个任务有3次重试机会 如果3次都不行则强制失败并钉钉告知 AAA 发布视频过多时间长不受此控制
        foreach ($reRun as $row) {
            $failedCacheName = "reFailedRun_" . $row['id'];
            // 重发失败任务 不需要重启
            if ($redis->get($failedCacheName)) {
                continue;
            }
            $cacheName = "reRun_" . $row['id'];
            $times = $redis->incr($cacheName);
            // 设置过期时间
            if ($times == 1) {
                $redis->expire($cacheName, $expireTime * 4);
            }
            // 清理任务 重置发布状态
            if ($row['type'] == Task::TYPE_DRAFT) {
                $campaignRs = DraftCampaignModel::getIns()->findBySignId($row['cid']);
                if (Tool::get($campaignRs, 'params')) {
                    $params = json_decode($campaignRs['params'], true);
                    if (Tool::isAAA($params)) {
                        continue;
                    }
                }
                // 清理广告系列
                TaskService::getIns()->resetOneDraftTask($row);
            }
            // 清理redis
            $redis->del(TaskService::getIns()->runningCacheName($row['id']));
            if ($times > 3) {
                $overTimeTask[] = $row;
                $redis->del($cacheName);
                continue;
            }
            Task::getIns()->updateByCond(
                ['id' => $row['id']],
                [
                    'status'    => 1,
                    'is_notice' => Task::NOTICE_NO,
                ]
            );
            Producer::publish('task', ['id' => $row['id'], 'co' => PublicService::getBusinessId()]);
            \Log::info('重新发布了任务' . $row['id']);
            echo '重新发布了任务' . $row['id'] . PHP_EOL;
        }

        if ($overTimeTask) {
            $ids = array_column($overTimeTask, 'id');
            Task::getIns()->updateByCond(
                ['id' => ['in', $ids]],
                [
                    'status' => 4,
                    'msg'    => '发布失败若干次,请检查后重新发布'
                ]
            );
            // $msg = implode(",", $ids) . "发布超时，请检查";
            \Log::info("发布任务超时", $ids);
        }
    }

    public function checkFailed()
    {
        // 检测超时任务并重置 10分钟一次
        $checkTimeStart = date("Y-m-d H:i:s", time() - 9600);


        $rs = Task::getIns()->listByCond(
            [
                'status'     => Task::STATUS_PUB_FAILED,
                'created_at' => ['>', $checkTimeStart]
            ]
        );
        if (empty($rs)) {
            return;
        }
        $reRun = [];
        $curTime = time();
        $expireTime = 1200;
        foreach ($rs as $row) {
            // 大于20分钟
            if ($curTime - strtotime($row['created_at']) < $expireTime) {
                continue;
            }
            // 重发关键字
            if (!preg_match(Tool::fmtRuleName(['retry', '重发', 'unknown', 'timed out', '重试']), Tool::unicodeToString($row['msg']))) {
                continue;
            }
            $reRun[] = $row;
        }
        if (!$reRun) {
            return;
        }

        $redis = RedisService::getIns();
        $ids = [];
        // 每个任务有2次重试机会
        foreach ($reRun as $row) {
            $cacheName = "reFailedRun_" . $row['id'];
            $times = $redis->incr($cacheName);
            if ($times > 3) {
                continue;
            }
            // 设置过期时间
            if ($times == 1) {
                $redis->expire($cacheName, $expireTime * 4);
            }
            $ids[] = $row['id'];
        }
        if (empty($ids)) {
            return;
        }

        TaskService::getIns()->restart($ids);

        \Log::info("重新发布失败任务", $ids);
    }
}
