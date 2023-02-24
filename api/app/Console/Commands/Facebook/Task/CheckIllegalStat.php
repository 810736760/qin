<?php

namespace App\Console\Commands\Facebook\Task;

use App\Helper\Tool;
use App\Libs\MultiRequest;
use App\Models\Facebook\IllegalAdStat;
use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\CurlService;
use App\Services\Facebook\InsightsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckIllegalStat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:CheckIllegalStat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查广告是否违规';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->getDisapprovedInfo();
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function getDisapprovedInfo()
    {
        $aidInfo = AdAccountService::getIns()->getActiveAdAccount();

        $filtering = '[{"field":"effective_status","operator":"IN","value":["' . InsightsService::CMD_ILLEGAL . '"]}]';
        $level = InsightsService::LEVEL_AD;
        $multiRequest = [];

        DB::beginTransaction();
        try {
            // 获取对应账户的ID下的封禁广告ID
            foreach ($aidInfo as $info) {
                $aid = $info['aid'];
                $GLOBALS['bmId'] = $info['bm_id'];
                $adInsightsService = new InsightsService();
                $multiRequest[] = $adInsightsService->getMatchId(
                    $aid,
                    ['account_id', 'updated_time', 'created_time', 'ad_review_feedback{global}', 'name', 'adset_id', 'campaign_id'],
                    $level,
                    $filtering,
                    '',
                    '',
                    '',
                    '',
                    CurlService::RESPONSE_LIMIT_TIMES_MAX,
                    false,
                    true
                );
            }
            // IllegalAdStat::getIns()->updateByCond(
            //     ['aid' => ['in', $aidArr], 'status' => IllegalAdStat::STATUS_ILLEGAL],
            //     ['status' => IllegalAdStat::STATUS_RESTORE]
            // );
            $chunkRequest = array_chunk($multiRequest, CurlService::REQUEST_ONCE_IN_ONE_CHUNK);
            foreach ($chunkRequest as $oneRequest) {
                $response = Tool::fmtMultiData(MultiRequest::multiFetch($oneRequest, false));

                foreach ($response as $oneRs) {

                    if (empty($oneRs) || empty(Tool::get($oneRs, 'data'))) {
                        continue;
                    }
                    $tmp = [];
                    $ads = [];
                    foreach ($oneRs['data'] as $one) {

                        $value = [
                            'ad_id'  => $one['id'],
                            'aid'    => $one['account_id'],
                            'name'   => $one['name'],
                            'sid'    => $one['adset_id'],
                            'cid'    => $one['campaign_id'],
                            'status' => IllegalAdStat::STATUS_ILLEGAL,
                            'reason' => json_encode($one['ad_review_feedback']['global'] ?? [])
                        ];
                        $tmp[] = $value;
                        $ads[] = $one['id'];
                    }

                    $rs = IllegalAdStat::getIns()->listByCond(['ad_id' => ['in', $ads]], ['ad_id']);

                    $exist = array_column($rs, 'ad_id');

                    $effect = array_filter($tmp, function ($q) use ($exist) {

                        if (empty($exist) || !in_array($q['ad_id'], $exist)) {
                            return true;
                        } else {
                            return false;
                        }

                    });

                    if (empty($effect)) {
                        continue;
                    }
                    IllegalAdStat::getIns()->insert($effect);
                }

            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($this->description . $exception);
        }
    }
}
