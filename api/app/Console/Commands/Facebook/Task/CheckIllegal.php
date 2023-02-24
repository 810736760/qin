<?php

namespace App\Console\Commands\Facebook\Task;

use App\Helper\Tool;
use App\Libs\MultiRequest;
use App\Models\Facebook\IllegalAd;
use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\CurlService;
use App\Services\Facebook\InsightsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckIllegal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:CheckIllegal';

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
        $aidArr = array_column($aidInfo, 'aid');

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
                    ['account_id', 'updated_time', 'created_time', 'ad_review_feedback{global}'],
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
            IllegalAd::getIns()->updateByCond(
                ['aid' => ['in', $aidArr], 'status' => IllegalAd::STATUS_ILLEGAL],
                ['status' => IllegalAd::STATUS_RESTORE]
            );
            $chunkRequest = array_chunk($multiRequest, CurlService::REQUEST_ONCE_IN_ONE_CHUNK);
            foreach ($chunkRequest as $oneRequest) {
                $response = Tool::fmtMultiData(MultiRequest::multiFetch($oneRequest, false));
                foreach ($response as $oneRs) {
                    if (empty($oneRs) || empty(Tool::get($oneRs, 'data'))) {
                        continue;
                    }
                    foreach ($oneRs['data'] as $one) {
                        $keyCondition = [
                            'ad_id' => $one['id']
                        ];
                        $value = [
                            'created_time' => $one['updated_time'],
                            'aid'          => $one['account_id'],
                        ];
                        if (Tool::get($one, 'ad_review_feedback')) {
                            $value['status'] = IllegalAd::STATUS_ILLEGAL;
                            $value['is_ignore'] = 0;
                            $value['reason'] = json_encode($one['ad_review_feedback']['global'] ?? []);
                        }
                        IllegalAd::getIns()->updateOrInsert(
                            $keyCondition,
                            $value
                        );
                    }
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($this->description . $exception);
        }
    }
}
