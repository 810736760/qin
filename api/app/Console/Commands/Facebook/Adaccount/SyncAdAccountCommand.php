<?php

namespace App\Console\Commands\Facebook\Adaccount;

use App\Models\Facebook\FacebookAdAccount;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
use App\Services\Common\DBService;
use Illuminate\Console\Command;
use App\Helper\Tool;

class SyncAdAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncAdAccountInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步广告账户的信息';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $args = $this->arguments();
        $startTime = microtime(true);
        \Log::info($this->description . "开始执行", $args);
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->main($args);
        }
        \Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($args)
    {
        $all = BMService::getAllBM();
        foreach ($all as $one) {
            $msg = $this->insertOrUpdateAdAccounts($one);
            if ($msg) {
                \Log::info($this->description . "统计错误:" . $msg, $args);
            }
        }
    }

    /**
     * 插入更新facebook adaccounts
     *
     */
    private function insertOrUpdateAdAccounts($bm)
    {
        $params = [
            'fields' => AdAccountService::BASE_ITEM,
            'limit'  => CurlService::REQUEST_ONCE_IN_ONE_BATCH
        ];
        $account = [];
        $after = '';
        $unActive = [];
        do {
            if ($after) {
                $params['after'] = $after;
            }

            [$status, $curlMsg, $data] = CurlService::getIns()->curlRequest(
                $bm['bm_id'] . '/client_ad_accounts',
                $params,
                $bm['system_token'] ?: config('facebook.access_token')
            );

            if (!$status || Tool::get($data, 'error')) {
                \Log::info('更新广告账户失败', ['bInfo' => $bm, 'err' => $data['error']]);
                return json_encode($data['error']) ?? $curlMsg;
            }

            // 匹配账户类型

            foreach ($data['data'] as $row) {
                $tmp = AdAccountService::fmtAccountInfo($row, $bm['id']);

                if ($tmp['is_active'] == FacebookAdAccount::ACTIVE_OFF && !AdAccountService::isFFAccount($row['name'])) {
                    $unActive[] = 'act_' . $tmp['aid'];
                }
                $account[] = $tmp;
            }

            if (Tool::get($data['paging'] ?? [], 'next')) {
                usleep(20000);
                $after = $data['paging']['cursors']['after'];
            } else {
                $after = '';
            }
        } while ($after);

        if (empty($account)) {
            return '';
        }


        // // 插入新账户
        // $accountIds = FacebookAdAccount::pluck('aid')->toArray();
        // $accountName = array_column($account, 'name', 'aid');
        // $fbAccountIds = array_column($account, 'aid');
        // $insertAccountIds = array_diff($fbAccountIds, $accountIds);
        // if (!empty($insertAccountIds)) {
        //     $insert = [];
        //     foreach ($insertAccountIds as $insertAccountId) {
        //         $insert[] = [
        //             'aid'      => $insertAccountId,
        //             'is_agent' => Tool::isAgentByName($accountName[$insertAccountId] ?? '')
        //         ];
        //     }
        //     FacebookAdAccount::getIns()->insert($insert);
        // }
        // $table_name = 'facebook_ad_accounts';
        // DBService::getIns()->batchUpdate($table_name, $account, 'aid');

        // 对未活跃的账户再查一次当天消耗
        $todayActive = [];
        if ($unActive) {
            $aidChunk = array_chunk($unActive, CurlService::REQUEST_ONCE_IN_ONE_CHUNK);
            foreach ($aidChunk as $chunk) {
                [$status, $curlMsg, $data] = CurlService::getIns()->curlRequest(
                    'insights',
                    [
                        'ids'         => implode(',', $chunk),
                        'date_preset' => 'today'
                    ],
                    $bm['system_token'] ?: config('facebook.access_token')
                );
                if (!$status || Tool::get($data, 'error')) {
                    \Log::info('获取广告账户成效失败', ['bInfo' => $bm, 'err' => $data['error']]);
                    continue;
                }

                foreach ($data as $row) {
                    if (count($row['data'])) {
                        $todayActive[] = $row['data'][0]['account_id'];
                    }
                }
            }
        }

        foreach ($account as $one) {
            if (in_array($one['aid'], $todayActive)) {
                $one['is_active'] = FacebookAdAccount::ACTIVE_ON;
            }
            FacebookAdAccount::getIns()->updateOrInsert(['aid' => $one['aid']], $one);
        }


        return '';
    }
}
