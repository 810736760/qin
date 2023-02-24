<?php

namespace App\Libs\RabbitMq;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Libs\SimpleRequest;
use App\Models\Facebook\FacebookAdAccount;
use App\Models\Material\MaterialAd;
use App\Models\Tiktok\TiktokAdAccounts;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\DraftService;
use App\Services\Facebook\FbSdkService;
use App\Services\Facebook\TaskService;
use App\Services\RedisService;
use App\Models\Task\Task;
use App\Models\Facebook\DraftAdModel;
use App\Models\Facebook\DraftCampaignModel;
use App\Models\Facebook\DraftAdSetModel;
use App\Services\Tiktok\CurlService;
use App\Services\Tiktok\InsightsService;
use App\Services\Tiktok\MaterialService;
use App\Services\Tiktok\TTSdkService;
use Exception;

/**
 *   发布创建/编辑/复制广告的任务
 */
class PublishTask extends BaseConsumer
{
    protected $exchangeName = 'task'; // 交换机名

    protected $exchangeType = AMQP_EX_TYPE_FANOUT; // 交换机类型

    protected $queueName = 'publish_ad'; // 队列名

    protected $routeKey = ''; // 路由key

    protected $beParams = [];


    /**
     * 发布任务主逻辑
     * @param $message
     * @return void
     */
    protected function handleMessage($message)
    {
        \Log::info("任务参数", $message);
        $startTime = microtime(true);
        $GLOBALS['co'] = $message['co'];
        $this->doTask($message['id']);
        \Log::info("任务[" . $message['id'] . "]已完成,use time=>" . (microtime(true) - $startTime));
    }

    public function doTask($id): bool
    {
        if (empty($id)) {
            return true;
        }

        $redis = RedisService::getIns();

        $task = TaskService::getIns();
        // 给一个请求赋予10分钟有效期
        $isRun = $redis->set($task->runningCacheName($id), 1, RedisService::REDIS_EXPIRE_TIME_HOUR / 6, true);
        if (!$isRun) {
            \Log::info("($id)[正在执行]");
            return true;
        }
        try {
            $taskModel = Task::getIns();


            // 检查该任务是否可以执行
            $rs = $taskModel->findOne($id);
            if (empty($rs) || $rs['status'] != Task::STATUS_BUILD) {
                \Log::info("($id)[任务状态异常]", $rs ?: []);
                return true;
            }
            // 更新任务状态
            $update = [];
            $update['status'] = Task::STATUS_RUNNING;
            $taskModel->updateById($id, $update);

            // 读取任务类型
            $params = json_decode($rs['params'], true);
            $doRs = [];
            $paramsId = 0;
            if ($rs['from'] == TASK::FROM_FB) {
                [$paramsId, $doRs] = $this->doFacebookTask($rs, $params);
            } elseif ($rs['from'] == TASK::FROM_TT) {
                [$paramsId, $doRs] = $this->doTTTask($rs, $params, $redis);
            }

            if ($paramsId) {
                $redis->del($task->buildCacheName($paramsId));
            }

            Task::getIns()->updateById($id, $doRs);
            $flag = true;
            if ($doRs['status'] != Task::STATUS_PUB_SUCCESS) {
                throw  new Exception($doRs['msg']);
            }
        } catch (Exception $e) {
            \Log::info("($id)[发布任务失败]" . $e->getMessage());
            $flag = false;
        }
        $redis->del($task->runningCacheName($id));
        return $flag;
    }

    public function doFacebookTask($rs, $params): array
    {
        $doRs = [];
        $adInfo = FacebookAdAccount::getIns()->getByCond(['aid' => $params['aid']]);
        $GLOBALS['bmId'] = $adInfo['bm_id'];

        switch ($rs['type']) {
            case Task::TYPE_CREATE:
                $doRs = $this->doParamsTask($rs['uid'], $params, FbSdkService::TYPE_TASK_MODEL_CREATE);
                $params['id'] = 0;
                break;
            case Task::TYPE_EDIT:
                $doRs = $this->doParamsTask($rs['uid'], $params, FbSdkService::TYPE_TASK_MODEL_EDIT);
                break;
            case Task::TYPE_DRAFT:
                $doRs = $this->doParamsTask($rs['uid'], $params, FbSdkService::TYPE_TASK_MODEL_DRAFT);
                break;
            default:
                break;
        }
        return [$params['id'], $doRs];
    }

    public function doTTTask($rs, $params, $redis): array
    {
        $doRs = [];
        $data = $params['data'];
        switch ($rs['type']) {
            // case Task::TYPE_CREATE:
            //     $doRs = $this->doCreateTask($rs['uid'], $params);
            //     break;
            case Task::TYPE_EDIT:
                $doRs = $this->doTTParamsTask($rs['uid'], $params, $redis, FbSdkService::TYPE_TASK_MODEL_EDIT);
                break;
            case Task::TYPE_DRAFT:
                $doRs = $this->doTTParamsTask($rs['uid'], $params, $redis, FbSdkService::TYPE_TASK_MODEL_DRAFT);
                break;
            default:
                break;
        }
        return [$data['id'], $doRs];
    }

    public function doTTParamsTask($uid, $params, $redis, $type): array
    {
        $update = [];
        $update['msg'] = '发布成功';
        $update['status'] = Task::STATUS_PUB_SUCCESS;
        try {
            if ($type == TTSdkService::TYPE_TASK_MODEL_EDIT) {
                $rs = TTSdkService::getIns()->multiEdit(
                    $params['aid'],
                    $params['level'],
                    [$params['data']],
                    $params['userInfo']
                );

                if (!Tool::get($rs, 'success')) {
                    $update['msg'] = $rs['failed'][0];
                    $update['status'] = Task::STATUS_PUB_FAILED;
                }
            } else {
                $rs = $this->publishTTDraft($params['aid'], $params['id'], $params['level'], $params['userInfo']);
                if ($rs['ret'] != ReturnCode::SUCCEED) {
                    $update['msg'] = $rs['msg'];
                    $update['status'] = Task::STATUS_PUB_FAILED;
                }
            }
        } catch (Exception $e) {
            $update['msg'] = $e;
            $update['status'] = Task::STATUS_PUB_FAILED;
        }

        return $update;
    }

    // 发布草稿
    public function publishTTDraft($aid, $ids, $level, $userInfo = []): array
    {
        $idArr = Tool::getArrayByComma($ids);
        if (empty($idArr)) {
            return [
                'ret' => ReturnCode::ERROR_PARAMS,
                'msg' => '参数错误'
            ];
        }
        $info = [
            'ret' => ReturnCode::SUCCEED,
            'msg' => '发布成功'
        ];

        $curLevel = $level;

        $rs = TTSdkService::getIns()->getSeriesIdLogic($aid, $curLevel, $idArr, true, true);
        $change = [];
        try {
            $this->publishTTLogic($userInfo, $aid, $rs['idMap'], $idArr, $curLevel, $change);
        } catch (Exception $e) {
            $this->rebuildErrMsg($change, $info, $e->getMessage() . "," . $e->getFile() . ":" . $e->getLine());
        }
        if (Tool::get($change, 'errMsg')) {
            $info = [
                'ret' => ReturnCode::ERROR_DATA_FAILED,
                'msg' => $change['errMsg']
            ];
            $this->redo($change, Task::FROM_TT);
        }
        // todo 记录一些日志 比如广告日志
        return $info;
    }

    public function doParamsTask($uid, $params, $type): array
    {
        $update = [];

        try {
            if ($type == FbSdkService::TYPE_TASK_MODEL_EDIT) {
                $commonService = new CommonService();
                [$nil, $msg, $ret] = $commonService->multiEdit($params['aid'], $params['param'], $uid, $params['level']);
            } elseif ($type == FbSdkService::TYPE_TASK_MODEL_CREATE) {
                $commonService = new CommonService();
                $result = FbSdkService::getIns()->createFbAd($params['data'], $params['aid']);
                $ret = $result['ret'];
                $msg = $result['msg'];
                $update['cid'] = $result['campaign_id'] ?? 0;
                if ($result['ret'] != ReturnCode::SUCCEED && Tool::get($result, 'campaign_id')) {
                    $commonService->deleteById([$result['campaign_id']], $params['aid']);
                }
            } else {
                $rs = $this->publishDraft($params['aid'], $params['id'], $params['level'], $uid);
                $ret = $rs['ret'];
                $msg = $rs['msg'];
            }

            if ($ret == ReturnCode::SUCCEED) {
                $update['msg'] = $msg ?: '发布成功';
                $update['status'] = Task::STATUS_PUB_SUCCESS;
            } else {
                $update['msg'] = $msg;
                $update['status'] = Task::STATUS_PUB_FAILED;
            }
        } catch (Exception $e) {
            $update['msg'] = $e->getMessage();
            $update['status'] = Task::STATUS_PUB_FAILED;
        }


        return $update;
    }

    // 发布草稿
    public function publishDraft($aid, $ids, $level, $uid = ''): array
    {
        $idArr = Tool::getArrayByComma($ids);
        if (empty($idArr)) {
            return [
                'ret' => ReturnCode::ERROR_PARAMS,
                'msg' => '参数错误'
            ];
        }
        $info = [
            'ret' => ReturnCode::SUCCEED,
            'msg' => '发布成功'
        ];

        $comSer = new CommonService();
        $map = $comSer->getLevelMap();
        $curLevel = Tool::get($map, $level);

        $rs = $comSer->getSeriesIdLogic($aid, $curLevel, $idArr, true, true);
        if (!Tool::get($rs, 'idMap')) {
            $funcList = DraftService::DRAFT_FUNC_LIST;
            $rs = $funcList[$curLevel]::getIns()->findBySignId($idArr[0]);
            $flag = true;
            if ($rs['status'] === DraftService::DRAFT_STATUS_DEL) {
                $flag = false;
                $info = [
                    'ret' => ReturnCode::SUCCEED,
                    'msg' => $idArr[0] . '已删除'
                ];
            } elseif ($rs['status'] === DraftService::DRAFT_STATUS_PUB) {
                $flag = false;
                $info = [
                    'ret' => ReturnCode::SUCCEED,
                    'msg' => $idArr[0] . '已发布'
                ];
            }
            if (!$flag) {
                return $info;
            }

            $info = [
                'ret' => ReturnCode::ERROR_BUSINESS,
                'msg' => '未找到' . $idArr[0] . '的关系树，请稍后重发'
            ];
            if ($curLevel !== DraftService::LEVEL_AD) {
                return $info;
            }
            $rs['idMap'] = [$idArr[0] => $idArr[0]];
        }
        $change = [];
        try {
            $this->publishLogic($uid, $aid, $rs['idMap'], $idArr, $curLevel, $change);
        } catch (Exception $e) {
            $this->rebuildErrMsg($change, $info, $e->getMessage() . "," . $e->getFile() . ":" . $e->getLine());
        }
        if (Tool::get($change, 'errMsg')) {
            // todo 针对AAA广告一次建了很多条的 可以考虑设定为成功
            $info = [
                'ret' => ReturnCode::ERROR_DATA_FAILED,
                'msg' => $change['errMsg']
            ];
            $this->redo($change, Task::FROM_FB, $aid);
        }
        // todo 记录一些日志 比如广告日志
        return $info;
    }

    public function rebuildErrMsg($change, &$info, $message)
    {
        $info = [
            'ret' => ReturnCode::ERROR_BUSINESS,
            'msg' => $change['errMsg'] ?? ($message ?: '发布异常')
        ];
    }

    public function redo($change, $from, $aid = 0)
    {
        if (empty($change)) {
            return;
        }
        \Log::info('回滚已创建的广告', $change);
        foreach ($change as $item => $idArr) {
            if ($item == 'errMsg') {
                continue;
            }
            $fbIds = array_column($idArr, 'id');
            $draftIds = array_column($idArr, 'draft');
            if ($from == Task::FROM_FB) {
                CommonService::getIns()->deleteById($fbIds, $aid);
            }

            switch ($item) {
                case CommonService::LEVEL_CAMPAIGN:
                    DraftCampaignModel::getIns()->updateStatusBySignIds($draftIds, DraftService::DRAFT_STATUS_RUN);
                    break;
                case CommonService::LEVEL_ADSET:
                    DraftAdSetModel::getIns()->updateStatusBySignIds($draftIds, DraftService::DRAFT_STATUS_RUN);
                    break;
                case CommonService::LEVEL_AD:
                    DraftAdModel::getIns()->updateStatusBySignIds($draftIds, DraftService::DRAFT_STATUS_RUN);
                    break;
                default:
                    break;
            }
        }
    }

    public function publishTTLogic(
        $userInfo,
        $aid,
        $info,
        $idArr,
        $level,
        &$change,
        $campaignId = '',
        $adSetId = ''
    ) {
        if (Tool::get($change, 'errMsg')) {
            return;
        }
        // 只发布草稿有关内容
        [$fbIdArr, $draftIdArr] = Tool::distinguishId($idArr);
        if (empty($draftIdArr)) {
            return;
        }
        $from = 1;
        switch ($level) {
            case TTSdkService::LEVEL_CAMPAIGN:
                // 发布广告系列内容
                foreach ($draftIdArr as $one) {
                    if (!Tool::get($info, $one)) {
                        continue;
                    }
                    $rs = $this->publishTTCampaign($one, $aid);
                    if (!$rs['id']) {
                        $change['errMsg'] = "广告系列:" . $rs['msg'];
                        \Log::info($change['errMsg'] . PHP_EOL . "[params]=>" . json_encode($rs['params']));
                        return;
                    }
                    if ($rs['new']) {
                        $change[$level][] = ['id' => $rs['id'], 'draft' => $one];
                    }
                    if (is_array($info[$one])) {
                        $this->publishTTLogic(
                            $userInfo,
                            $aid,
                            $info,
                            array_keys($info[$one]),
                            TTSdkService::LEVEL_ADSET,
                            $change,
                            $rs['id']
                        );
                    }
                }
                break;
            case TTSdkService::LEVEL_ADSET:
                foreach ($draftIdArr as $one) {
                    if (!Tool::get($info, $one)) {
                        continue;
                    }
                    $campaignIdRs = $this->getDraftSeriesIdWithLevel(
                        $userInfo['id'],
                        $one,
                        $level,
                        $campaignId,
                        $adSetId,
                        $aid,
                        $change,
                        $from
                    );
                    $rs = $this->publishTTAdSet($campaignIdRs, $one, $aid);
                    if (!$rs['id']) {
                        $change['errMsg'] = "广告集:" . $rs['msg'];
                        \Log::info($change['errMsg'] . PHP_EOL . "[params]=>" . json_encode($rs['params']));
                        return;
                    }
                    if ($rs['new']) {
                        $change[$level][] = ['id' => $rs['id'], 'draft' => $one];
                    }

                    if (is_array($info[$one])) {
                        $this->publishTTLogic(
                            $userInfo,
                            $aid,
                            $info,
                            array_values($info[$one]),
                            TTSdkService::LEVEL_AD,
                            $change,
                            $campaignIdRs,
                            $rs['id']
                        );
                    }
                }
                break;
            case TTSdkService::LEVEL_AD:
                // 发布广告集内容
                foreach ($draftIdArr as $one) {
                    if (!Tool::get($info, $one)) {
                        continue;
                    }
                    // 获取adSet_id
                    $adSetIdRs = $this->getDraftSeriesIdWithLevel(
                        $userInfo['id'],
                        $one,
                        $level,
                        $campaignId,
                        $adSetId,
                        $aid,
                        $change,
                        $from
                    );
                    // 先筛选
                    $rs = DraftAdModel::getIns()->findBySignId($one);
                    $tmp = [
                        'msg' => ''
                    ];
                    $this->checkStatus($rs, $one, $tmp);
                    if ($tmp['msg']) {
                        $change['errMsg'] = '广告:' . $tmp['msg'];
                        return;
                    }

                    $params = json_decode($rs['params'], true);
                    // $copyFromId = $rs['copy_from'];
                    // 先获取当前是否AAA

                    $isDynamic = TTSdkService::isDynamic($one);
                    $copyAid = Tool::get($params, 'copy_from_aid', $aid);
                    $post = [];
                    $adSetParams = $this->getBeParams(TTSdkService::LEVEL_ADSET, $adSetId, $aid);
                    $appList = TTSdkService::getIns()->getAppListByOs($aid);
                    $appListMap = array_column($appList['list'], 'tracking_url', 'app_id');
                    if ($isDynamic) {
                        $levelItem = TTSdkService::LEVEL_DYNAMIC_AD;
                        Tool::isExistKey(
                            $params,
                            TTSdkService::ENABLE_CREATE_ITEM[$levelItem],
                            $post
                        );
                        $post['media_info'] = MaterialService::getIns()->fmtMediaInfo($userInfo, $post['media_info'], $aid, $copyAid);
                        $post['adgroup_id'] = $adSetId;
                        // 自动命名广告名称
                        if (!isset($post['name'])) {
                            $post['name'] = '';
                        }

                        // SDK不支持动态优选 默认选择下载
                        if (!isset($post['call_to_action_list']) || empty($post['call_to_action_list'])) {
                            $post['call_to_action_list'] = ["DOWNLOAD_NOW"];
                        }

                        // 若是跨账户 需要调整url
                        if ($copyAid != $aid) {
                            if (Tool::get($appListMap, $adSetParams['app_id'])) {
                                $change['errMsg'] = "广告:" . '跨账户复制选择应用失败';
                                \Log::info($change['errMsg'] . PHP_EOL . "[params]=>" . json_encode([$appListMap, $appListMap]));
                                return;
                            }

                            $post['impression_tracking_url'] = $appListMap[$adSetParams['app_id']]['impression_url'] ?? '';
                            $post['click_tracking_url'] = $appListMap[$adSetParams['app_id']]['click_url'] ?? '';
                        }
                        $post['advertiser_id'] = $aid;
                    } else {
                        $levelItem = TTSdkService::LEVEL_AD;
                        Tool::isExistKey(
                            $params,
                            TTSdkService::ENABLE_CREATE_ITEM[$levelItem],
                            $post
                        );
                        MaterialService::getIns()->fmtNoDyMediaInfo($post, $userInfo, $aid, $params);
                        $temp = $post;
                        if (!isset($temp['ad_name'])) {
                            $temp['ad_name'] = '';
                        }
                        // SDK不支持动态优选 默认选择下载
                        if (!isset($temp['call_to_action_list']) || empty($temp['call_to_action_list'])) {
                            $temp['call_to_action_list'] = ["DOWNLOAD_NOW"];
                        }
                        // 若是跨账户 需要调整url
                        if ($copyAid != $aid) {
                            if (Tool::get($appListMap, $adSetParams['app_id'])) {
                                $change['errMsg'] = "广告:" . '跨账户复制选择应用失败';
                                \Log::info($change['errMsg'] . PHP_EOL . "[params]=>" . json_encode([$appListMap, $appListMap]));
                                return;
                            }
                            $temp['impression_tracking_url'] = $appListMap[$adSetParams['app_id']]['impression_url'] ?? '';
                            $temp['click_tracking_url'] = $appListMap[$adSetParams['app_id']]['click_url'] ?? '';
                        }
                        $update['adgroup_id'] = $adSetIdRs;
                        unset($temp['advertiser_id']);
                        $post = [
                            'advertiser_id' => $aid,
                            'adgroup_id'    => $adSetIdRs,
                            'creatives'     => [$temp]
                        ];
                    }


                    $res = $this->publishTTAd(
                        $post,
                        $one,
                        $adSetIdRs,
                        $isDynamic
                    );
                    if (!$res['id']) {
                        $change['errMsg'] = "广告:" . $res['msg'];
                        \Log::info($change['errMsg'] . PHP_EOL . "[params]=>" . json_encode($post));
                        return;
                    }
                    if ($res['new']) {
                        $change[$level][] = ['id' => $res['id'], 'draft' => $one];
                    }
                    // $commonService->usingInfo[$res['id']] = $commonService->usingMid;
                    // MaterialAd::getIns()->distribute($commonService->usingInfo);
                }
                break;
            default:
                break;
        }
    }

    public function publishLogic(
        $uid,
        $aid,
        $info,
        $idArr,
        $level,
        &$change,
        $campaignId = '',
        $adSetId = ''
    ) {
        if (Tool::get($change, 'errMsg')) {
            return;
        }
        // 只发布草稿有关内容
        [$fbIdArr, $draftIdArr] = Tool::distinguishId($idArr);
        if (empty($draftIdArr)) {
            return;
        }
        switch ($level) {
            case CommonService::LEVEL_CAMPAIGN:
                // 发布广告系列内容
                foreach ($draftIdArr as $one) {
                    if (!Tool::get($info, $one)) {
                        continue;
                    }
                    $rs = $this->publishCampaign($one, $aid);
                    if (!$rs['id']) {
                        $change['errMsg'] = "广告系列:" . $rs['msg'];
                        \Log::info($change['errMsg'], $rs['params'] ?? []);
                        return;
                    }
                    if ($rs['new']) {
                        $change[$level][] = ['id' => $rs['id'], 'draft' => $one];
                    }
                    if (is_array($info[$one])) {
                        $this->publishLogic(
                            $uid,
                            $aid,
                            $info,
                            array_keys($info[$one]),
                            CommonService::LEVEL_ADSET,
                            $change,
                            $rs['id']
                        );
                    }
                }
                break;
            case CommonService::LEVEL_ADSET:
                foreach ($draftIdArr as $one) {
                    if (!Tool::get($info, $one)) {
                        continue;
                    }
                    $campaignIdRs = $this->getDraftSeriesIdWithLevel(
                        $uid,
                        $one,
                        $level,
                        $campaignId,
                        $adSetId,
                        $aid,
                        $change
                    );
                    $rs = $this->publishAdSet($campaignIdRs, $one, $aid);
                    if (!$rs['id']) {
                        $change['errMsg'] = "广告集:" . $rs['msg'];
                        \Log::info($change['errMsg'], $rs['params'] ?? []);
                        return;
                    }
                    if ($rs['new']) {
                        $change[$level][] = ['id' => $rs['id'], 'draft' => $one];
                    }

                    if (is_array($info[$one])) {
                        $this->publishLogic(
                            $uid,
                            $aid,
                            $info,
                            array_values($info[$one]),
                            CommonService::LEVEL_AD,
                            $change,
                            $campaignIdRs,
                            $rs['id']
                        );
                    }
                }
                break;
            case CommonService::LEVEL_AD:
                // 发布广告集内容
                foreach ($draftIdArr as $one) {
                    if (!Tool::get($info, $one)) {
                        continue;
                    }
                    // 获取adSet_id
                    $adSetIdRs = $this->getDraftSeriesIdWithLevel(
                        $uid,
                        $one,
                        $level,
                        $campaignId,
                        $adSetId,
                        $aid,
                        $change
                    );
                    // 先筛选
                    $rs = DraftAdModel::getIns()->findBySignId($one);
                    $tmp = [
                        'msg' => ''
                    ];
                    $this->checkStatus($rs, $one, $tmp);
                    if ($tmp['msg']) {
                        $change['errMsg'] = '广告:' . $tmp['msg'];
                        return;
                    }

                    $params = json_decode($rs['params'], true);
                    // $copyFromId = $rs['copy_from'];

                    // 生成创意参数
                    $commonService = CommonService::getIns();
                    $commonService->usingInfo = [];
                    $commonService->usingMid = [];
                    $creative = $this->fmtAdCreative($uid, $aid, $params);
                    if ($creative['msg']) {
                        $change['errMsg'] = "广告创意:" . json_encode($creative['msg']);
                        \Log::info($change['errMsg'], $creative['params'] ?? []);
                        return;
                    }
                    $res = $this->publishAd(
                        $one,
                        $adSetIdRs,
                        $aid,
                        $creative['params'],
                        $params
                    );
                    if (!$res['id']) {
                        $change['errMsg'] = "广告:" . $res['msg'];
                        \Log::info($change['errMsg'], $res['params'] ?? []);
                        return;
                    }
                    if ($res['new']) {
                        $change[$level][] = ['id' => $res['id'], 'draft' => $one];
                    }
                    $commonService->usingInfo[$res['id']] = $commonService->usingMid;
                    MaterialAd::getIns()->distribute($commonService->usingInfo);
                }
                break;
            default:
                break;
        }
    }

    public function getDraftSeriesIdWithLevel($uid, $draft, $level, $campaignId, $adSetId, $aid, &$change, $from = 0)
    {
        $funcList = DraftService::DRAFT_FUNC_LIST;
        $rs = $funcList[$level]::getIns()->findBySignId($draft);
        $rsId = '';
        switch ($level) {
            case TTSdkService::LEVEL_ADSET:
            case DraftService::LEVEL_ADSET:
                if ($campaignId) {
                    $rsId = $campaignId;
                } else {
                    if (Tool::isPublishId($rs['campaign_id'])) {
                        if ($from) {
                            $info = $this->publishTTCampaign($rs['campaign_id'], $aid);
                        } else {
                            $info = $this->publishCampaign($rs['campaign_id'], $aid);
                        }

                        $rsId = $info['id'];
                        if ($info['new']) {
                            $change[DraftService::LEVEL_CAMPAIGN][] = ['id' => $info['id'], 'draft' => $rs['campaign_id']];
                        }
                    } else {
                        $rsId = $rs['campaign_id'];
                    }
                }

                break;
            case DraftService::LEVEL_AD:
                if ($adSetId) {
                    $rsId = $adSetId;
                } else {
                    if (Tool::isPublishId($rs['adset_id'])) {
                        if ($from == 0) {
                            $campaignId = $this->getDraftSeriesIdWithLevel(
                                $uid,
                                $rs['adset_id'],
                                DraftService::LEVEL_ADSET,
                                $campaignId,
                                $adSetId,
                                $aid,
                                $change
                            );

                            $info = $this->publishAdSet($campaignId, $rs['adset_id'], $aid);

                            $rsId = $info['id'];
                            if ($info['new']) {
                                $change[DraftService::LEVEL_ADSET][] = ['id' => $info['id'], 'draft' => $rs['adset_id']];
                            }
                        } elseif ($from == 1) {
                            $campaignId = $this->getDraftSeriesIdWithLevel(
                                $uid,
                                $rs['adset_id'],
                                TTSdkService::LEVEL_ADSET,
                                $campaignId,
                                $adSetId,
                                $aid,
                                $change
                            );

                            $info = $this->publishTTAdSet($campaignId, $rs['adset_id'], $aid);

                            $rsId = $info['id'];
                            if ($info['new']) {
                                $change[TTSdkService::LEVEL_ADSET][] = ['id' => $info['id'], 'draft' => $rs['adset_id']];
                            }
                        } else {
                            $rsId = $rs['adset_id'] ?? $rs['adgroup_id'] ?? 0;
                        }
                    } else {
                        $rsId = $rs['adset_id'];
                    }
                }

                break;
            default:
                break;
        }
        return $rsId;
    }

    public function publishCampaign($draftId, $aid): array
    {
        $info = [
            'id'     => 0,
            'msg'    => '',
            'new'    => false,
            'params' => []
        ];


        // 首先获取当前模板是否发布
        $rs = DraftCampaignModel::getIns()->findBySignId($draftId);
        $this->checkStatus($rs, $draftId, $info);
        if (!empty($info['msg'])) {
            return $info;
        }


        // 构造创建参数
        $params = json_decode($rs['params'], true);

        $post = [];
        Tool::isExistKey(
            $params,
            [
                'name', 'objective', 'status', 'start_time', 'smart_promotion_type',
                'end_time', 'daily_budget', 'lifetime_budget', 'bid_strategy',
                'is_skadnetwork_attribution', 'promoted_object'
            ],
            $post
        );
        // IOS 14+ 或者目录商品
        if (!Tool::get($post, 'is_skadnetwork_attribution') &&
            !Tool::get($post['promoted_object'] ?? [], 'product_catalog_id')
        ) {
            unset($post['promoted_object']);
        }

        $post['special_ad_categories'] = []; // 默认传空
        $post['buying_type'] = "AUCTION";
        // $post['name'] = Tool::fmtPublishName($post['name']);

        // AAA 再过滤
        if (Tool::isAAA($params)) {
            $post = CommonService::getIns()->rebuildCampaignParamsInAAA($post);
        }
        $sdkService = new FbSdkService();
        [$result, $msg] = $sdkService->publishWithSdk($aid, $post, CommonService::LEVEL_CAMPAIGN);
        if (empty($msg)) {
            $info['id'] = $result['id'];
            $build = [
                'status'   => DraftService::DRAFT_STATUS_PUB,
                'build_id' => $result['id']
            ];
            DraftCampaignModel::getIns()->updateBySignId($draftId, $build);
            $info['new'] = true;
        }
        $info['msg'] = $msg;
        $info['params'] = $post;
        return $info;
    }

    public function publishTTCampaign($draftId, $aid): array
    {
        $info = [
            'id'     => 0,
            'msg'    => '',
            'new'    => false,
            'params' => []
        ];
        // 首先获取当前模板是否发布
        $rs = DraftCampaignModel::getIns()->findBySignId($draftId);
        $this->checkStatus($rs, $draftId, $info);
        if (!empty($info['msg'])) {
            return $info;
        }

        // 构造创建参数
        $params = json_decode($rs['params'], true);
        $post = [];
        Tool::isExistKey(
            $params,
            TTSdkService::ENABLE_CREATE_ITEM[TTSdkService::LEVEL_CAMPAIGN],
            $post
        );

        $post['advertiser_id'] = $aid;
        [$status, $msg, $rs, $code] = $this->createTTCurl(TTSdkService::LEVEL_CAMPAIGN, $post);
        if (empty($code)) {
            $info['id'] = $rs['data']['campaign_id'];
            $build = [
                'status'   => DraftService::DRAFT_STATUS_PUB,
                'build_id' => $info['id']
            ];
            DraftCampaignModel::getIns()->updateBySignId($draftId, $build);
            $info['new'] = true;
        }

        $info['msg'] = $msg;
        $info['params'] = $post;
        $this->beParams = [
            TTSdkService::LEVEL_CAMPAIGN => $post
        ];
        return $info;
    }


    public function createTTCurl($level, $params, $isDynamic = false): array
    {
        return CurlService::getIns()->tkCurl(
            InsightsService::getIns()->fmtLightPathByLevel($level, '/create/', $isDynamic),
            $params,
            SimpleRequest::REQUEST_TYPE_TK_POST
        );
    }


    public function checkStatus($rs, $draftId, &$info)
    {
        if (empty($rs)) {
            $info['msg'] = '未找到草稿信息' . $draftId;
        } elseif ($rs['status'] == DraftService::DRAFT_STATUS_PUB) {
            $info['id'] = $rs['build_id'];
            $info['msg'] = '已发布';
        } elseif ($rs['status'] == DraftService::DRAFT_STATUS_DEL) {
            $info['msg'] = $draftId . '已删除';
        }
    }

    public function publishTTAdSet($campaignId, $draftId, $aid): array
    {
        $info = [
            'id'     => 0,
            'msg'    => '',
            'new'    => false,
            'params' => []
        ];

        // 首先获取当前模板是否发布
        $rs = DraftAdSetModel::getIns()->findBySignId($draftId);

        $this->checkStatus($rs, $draftId, $info);
        if (!empty($info['msg'])) {
            return $info;
        }

        // 构造创建参数
        $params = json_decode($rs['params'], true);


        $post = [];
        Tool::isExistKey(
            $params,
            TTSdkService::ENABLE_CREATE_ITEM[TTSdkService::LEVEL_ADSET],
            $post
        );

        // $campaignParams = $this->getBeParams(TTSdkService::LEVEL_CAMPAIGN, $campaignId, $aid);

        // brand_safety: one or more value of the params is not acceptable, correct is THIRD_PARTY, LIMITED_INVENTORY, current is NO_BRAND_SAFETY
        if (!in_array(Tool::get($post, 'brand_safety_type'), ['THIRD_PARTY', 'LIMITED_INVENTORY'])) {
            unset($post['brand_safety_type']);
        }

        if (Tool::get($post, 'bid_type') != 'BID_TYPE_CUSTOM' || Tool::get($post, 'billing_event') != 'OCPM') {
            unset($post['conversion_bid_price']);
        }

        // $emptyItem = ['device_price', 'interest_category_v2', 'device_models', 'targeting_expansion' => ['expansion_types']];
        // foreach ($emptyItem as $key => $eItem) {
        //     if (is_array($eItem)) {
        //         foreach ($eItem as $one) {
        //             if (empty($post[$key][$one])) {
        //                 unset($post[$key][$one]);
        //             }
        //         }
        //     } else {
        //         if (empty($post[$eItem])) {
        //             unset($post[$eItem]);
        //         }
        //     }
        // }
        // 编辑时间
        $curTime = time();
        $startTime = strtotime($post['schedule_start_time']);
        if ($startTime < $curTime) {
            $post['schedule_start_time'] = date("Y-m-d H:i:s", $curTime);
            $post['schedule_type'] = 'SCHEDULE_FROM_NOW';
            unset($post['schedule_end_time']);
        }

        $emptyCheck = ['interest_category_ids', 'device_price_ranges', 'device_model_ids', 'excluded_audience_ids', 'audience_ids',
            'interest_keyword_ids', 'contextual_tag_ids', 'purchase_intention_keyword_ids'];
        foreach ($emptyCheck as $oneItem) {
            $this->checkEmptyAndRemove($post, $oneItem);
        }


        $post['advertiser_id'] = $aid;
        $post['campaign_id'] = $campaignId;
        [$status, $msg, $rs, $code] = $this->createTTCurl(TTSdkService::LEVEL_ADSET, $post);
        if (empty($code)) {
            $info['id'] = $rs['data']['adgroup_id'];
            $build = [
                'status'   => DraftService::DRAFT_STATUS_PUB,
                'build_id' => $info['id']
            ];
            DraftAdSetModel::getIns()->updateBySignId($draftId, $build);
            $info['new'] = true;
        }

        $info['msg'] = $msg;
        $info['params'] = $post;
        $this->beParams = [
            TTSdkService::LEVEL_ADSET => $post
        ];
        return $info;
    }

    public function checkEmptyAndRemove(&$arr, $key)
    {
        if (empty($arr[$key])) {
            unset($arr[$key]);
        }
    }

    public function getBeParams($level, $id, $aid)
    {
        if (Tool::get($this->beParams, $level)) {
            return $this->beParams[$level];
        }
        return TTSdkService::getIns()->listInfoByIds($aid, [$id], $level);
    }

    public function publishAdSet($campaignId, $draftId, $aid): array
    {
        $info = [
            'id'     => 0,
            'msg'    => '',
            'new'    => false,
            'params' => []
        ];

        // 首先获取当前模板是否发布
        $rs = DraftAdSetModel::getIns()->findBySignId($draftId);

        $this->checkStatus($rs, $draftId, $info);
        if (!empty($info['msg'])) {
            return $info;
        }

        // 构造创建参数
        $params = json_decode($rs['params'], true);
        $post = [];
        // 广告系列ID
        $post['campaign_id'] = $campaignId;

        Tool::isExistKey(
            $params,
            [
                'name', 'status', 'start_time', 'end_time',
                'destination_type', 'is_dynamic_creative',
                'daily_min_spend_target', 'daily_spend_cap',
                'lifetime_min_spend_target', 'lifetime_spend_cap',
                'daily_budget', 'lifetime_budget', 'bid_strategy',
                'bid_amount', 'bid_constraints', 'bid_strategy', 'billing_event',
                'attribution_spec', 'optimization_goal', 'promoted_object',
                'optimization_sub_event', 'targeting'
            ],
            $post
        );


        // 竞价策略处理
        // 1、获取策略
        if (isset($post['daily_budget']) || isset($post['lifetime_budget'])) {
            $bidStrategy = $params['bid_strategy'];
        } else {
            $bidStrategy = $params['campaign']['bid_strategy'];
        }
        // 2、当策略不是LOWEST_COST_WITH_MIN_ROAS时 去掉bid_constraints字段
        if ($bidStrategy !== 'LOWEST_COST_WITH_MIN_ROAS') {
            unset($post['bid_constraints']);
        }


        // 异常设备版本判断 设置默认值
        if (Tool::get($post['targeting'], 'user_os')) {
            $post['targeting']['user_os'][0] = str_replace('undefined_to_', '2.0_and_above', $post['targeting']['user_os'][0]);
            $arr = explode('_', $post['targeting']['user_os'][0]);
            if (count($arr) < 5) {
                $ver = '_ver_2.0_and_above';
                $os = $arr[0] ?? 'Android';
                $post['targeting']['user_os'][0] = $os . $ver;
            }
        } else {
            // 不存在这个值
            unset($post['targeting']['user_device']);
        }


        // 复制的时候 改了广告系列的AAA 可能有坑
        if (Tool::isAAA($params['campaign'])) {
            $post = CommonService::getIns()->rebuildAdSetParamsInAAA($post);
        }


        $sdkService = new FbSdkService();
        [$result, $msg] = $sdkService->publishWithSdk($aid, $post, CommonService::LEVEL_ADSET);
        if (empty($msg)) {
            $info['id'] = $result['id'];
            $build = [
                'status'   => DraftService::DRAFT_STATUS_PUB,
                'build_id' => $result['id']
            ];
            DraftAdSetModel::getIns()->updateBySignId($draftId, $build);
            $info['new'] = true;
        }


        $info['params'] = $post;
        $info['msg'] = $msg;
        return $info;
    }

    // 切换系统 全局替换链接 要注意转义符
    public function replaceLinkWithOsChange($info, $adsetId)
    {
        // 获取素材的adSetId 素材草稿ID跟广告一致
        $adSetInfo = DraftAdSetModel::getIns()->findBySignId($adsetId);
        if (empty($adSetInfo)) {
            return $info;
        }
        $params = json_decode($adSetInfo['params'], true);
        if (!Tool::get($params, 'firstOsUrl') || $params['destination_type'] == 'WEBSITE') {
            return $info;
        }
        $changeUrl = Tool::get($params['promoted_object'], 'object_store_url');
        if (empty($changeUrl) || $changeUrl == $params['firstOsUrl']) {
            return $info;
        }
        // 系统切换了 替换link
        $fs = json_encode($params['firstOsUrl']);
        $ch = json_encode($changeUrl);
        $paramsStr = json_encode($info);
        $paramsStr = str_replace($fs, $ch, $paramsStr);
        // FB 创意的object_store_url恒为http,无法修改 下是兼容apple的链接
        $fs = str_replace("https", 'http', $fs);
        $paramsStr = str_replace($fs, $ch, $paramsStr);
        return json_decode($paramsStr, true);
    }


    public function fmtAdCreative($uid, $aid, $params): array
    {
        $info = [
            'msg'    => [],
            'params' => []
        ];
        // 先获取当前是否AAA
        $isAAA = Tool::isAAA($params['campaign']);
        // 如果切换系统 后端替换link链接
        $params['creative'] = $this->replaceLinkWithOsChange($params['creative'], $params['adset_id']);
        $commonServices = new CommonService();
        $commonServices->fmtCreativeMaterialParams($params, $aid, $info['msg'], $uid, 'draft', $isAAA);
        unset($params['creative']['id']);

        Tool::isExistKey(
            $params['creative'],
            ['use_page_actor_override', 'object_story_spec', 'asset_feed_spec', 'applink_treatment', 'product_set_id'],
            $info['params']
        );

        return $info;
    }

    public function publishAd(
        $draftId,
        $adSetId,
        $aid,
        $creativeInfo,
        $params
    ): array {
        $info = [
            'id'     => 0,
            'msg'    => '',
            'new'    => false,
            'params' => []
        ];


        // 构造创建参数
        $post = [
            'name'     => $params['name'] ?? '广告',
            'adset_id' => $adSetId,
            'creative' => $creativeInfo,
            'status'   => 'ACTIVE'
        ];

        if (!Tool::isAAA($params['campaign']) &&
            (Tool::get($params['adset']['promoted_object'] ?? [], 'pixel_id') ||
                Tool::get($params['adset']['promoted_object'] ?? [], 'product_set_id')) &&
            Tool::get($params, 'tracking_specs') &&
            Tool::get($params['adset'], 'optimization_goal') != 'VALUE' // vo 不写网域
        ) {
            $post['tracking_specs'] = $this->fmtTrackingSpecs($params['tracking_specs']);
            $post['conversion_domain'] = $params['conversion_domain'] ?? '';
        }

        $sdkService = new FbSdkService();
        [$result, $msg] = $sdkService->publishWithSdk($aid, $post, CommonService::LEVEL_AD);
        if (empty($msg)) {
            $info['id'] = $result['id'];
            $build = [
                'status'   => DraftService::DRAFT_STATUS_PUB,
                'build_id' => $result['id']
            ];
            DraftAdModel::getIns()->updateBySignId($draftId, $build);
            $info['new'] = true;
        }
        $info['params'] = $post;
        $info['msg'] = $msg;
        return $info;
    }

    public function fmtTrackingSpecs($list): array
    {
        $enableItem = ['offsite_conversion', 'mobile_app_install', 'app_custom_event'];
        $new = [];
        foreach ($list as $row) {
            if (!in_array($row['action.type'][0], $enableItem)) {
                continue;
            }
            $new[] = $row;
        }
        return $new;
    }

    public function publishTTAd(
        $params,
        $draftId,
        $adSetId,
        $isDynamic
    ): array {
        $info = [
            'id'     => 0,
            'msg'    => '',
            'new'    => false,
            'params' => []
        ];
        [$status, $msg, $rs, $code] = $this->createTTCurl(TTSdkService::LEVEL_AD, $params, $isDynamic);
        if (!$code) {
            if ($isDynamic) {
                $info['id'] = TTSdkService::addDynamicTail($adSetId);
            } else {
                $ids = $rs['data']['ad_ids'];
                $info['id'] = $ids[0];
            }
            $build = [
                'status'   => DraftService::DRAFT_STATUS_PUB,
                'build_id' => $info['id']
            ];
            DraftAdModel::getIns()->updateBySignId($draftId, $build);
            $info['new'] = true;
        }

        $info['msg'] = $msg;
        $info['params'] = $params;
        return $info;
    }
}
