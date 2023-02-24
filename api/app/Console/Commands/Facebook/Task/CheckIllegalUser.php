<?php

namespace App\Console\Commands\Facebook\Task;

use App\Helper\AlarmHelper;
use App\Mail\FacebookEmailShipped;
use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookAdLevelMap;
use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
use App\Services\Common\DBService;
use App\Services\Facebook\FbSdkService;
use App\Services\RedisService;
use Illuminate\Console\Command;
use App\Helper\Tool;
use Illuminate\Support\Facades\Mail;

class CheckIllegalUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:CheckIllegalUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查非法用户关闭广告';


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
            // 独立运行
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $this->main();
        }
        \Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main()
    {
        $aids = AdAccountService::getIns()->getActiveAdAccount(['aid', 'id', 'name']);
        foreach ($aids as $row) {
            $info = CurlService::getIns()->getActiveCampaign($row['aid']);

            if (empty($info)) {
                continue;
            }

            foreach ($info as $one) {
                $activeInfo = FbSdkService::getIns()->getActivities($row['aid'], $one['id']);
                if (empty($activeInfo)) {
                    continue;
                }
                foreach ($activeInfo as $oneInfo) {
                    if ($oneInfo['actor_name'] === 'system') {
                        if (RedisService::getIns()->get('illegal_user_' . $oneInfo['object_id'])) {
                            continue;
                        }
                        $hit = [
                            'title'        => '[system]' . $oneInfo['translated_event_type'],
                            'detail'       => $oneInfo['object_name'] . "(" . $oneInfo['object_id'] . ")",
                            'extra'        => json_decode($oneInfo['extra_data'], true)['new_value'] ?? '',
                            'cname'        => $one['name'],
                            'curl'         => CurlService::buildFbLink(
                                $row['aid'],
                                FacebookAdLevelMap::LEVEL_CAMPAIGN,
                                $one['id']
                            ),
                            'account_name' => $row['name'],
                            'account_url'  => CurlService::buildFbLink(
                                $row['aid'],
                                FacebookAdLevelMap::LEVEL_ACCOUNT,
                                0
                            )
                        ];


                        $content = $hit['title'] . PHP_EOL;
                        $content .= "广告系列:" . $hit['cname'] . PHP_EOL;
                        $content .= "广告账户:" . $hit['account_name'] . PHP_EOL;
                        $content .= "对方操作:" . $hit['extra'] . PHP_EOL;
                        $content .= "详细:" . $hit['detail'] . PHP_EOL;
                        AlarmService::dingdingSend($content);

                        $mailAddress = AlarmHelper::getCreatorMailAddress(AdAccountService::getUserByAid($row['aid']));
                        Mail::to($mailAddress)->send(
                            new FacebookEmailShipped(
                                [$hit],
                                'illegal_user',
                                '捕获非法用户操作,请检查'
                            )
                        );
                        RedisService::getIns()->set('illegal_user_' . $oneInfo['object_id'], 1, RedisService::REDIS_EXPIRE_TIME_HOUR);
                    }
                }
            }
        }
    }
}
