<?php

namespace App\Console\Commands\Google;

use App\Models\Google\GoogleAdAccounts;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Google\AdAccountService;
use App\Services\Google\BaseService;
use App\Services\Google\CurlService;
use Illuminate\Console\Command;
use App\Helper\Tool;

class SyncAdAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:SyncAdAccountInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步GG广告账户的信息';


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
        $query = 'SELECT customer_client.client_customer, customer_client.level,'
            . ' customer_client.manager, customer_client.descriptive_name,'
            . ' customer_client.currency_code, customer_client.time_zone,customer_client.status,'
            . ' customer_client.id FROM customer_client WHERE customer_client.level <= 1 ';
        $rs = CurlService::getIns()->commonPostFetch(BaseService::getInstance()->customerId, $query);
        if (!Tool::get($rs, 'results')) {
            return;
        }

        $spendStartDate = date("Y-m-d", strtotime('-30 days'));
        // $spendStartDate = 20220901;
        $spendEndDate = date("Y-m-d");
        // $history = GoogleAdAccounts::getIns()->get()->toArray();
        // $history = array_column($history, 'created_at', 'aid');

        foreach ($rs['results'] as $one) {
            $row = $one['customerClient'];
            if (in_array($row['id'], AdAccountService::ABANDONED_ACCOUNT)) {
                continue;
            }
            $spend = 0;
            if (!$row['manager']) {
                $spend = $this->getOneAdAccountSpend($row['id'], $spendStartDate, $spendEndDate);
            }

            usleep(20000);
            $update = AdAccountService::fmtAccountInfo($row, $spend, 1, 0);

            if (empty($update)) {
                continue;
            }
            GoogleAdAccounts::getIns()->updateOrInsert(
                [
                    'aid' => $row['id']
                ],
                $update
            );
        }
    }

    /**
     * 获取一个广告账户的花费
     * @param $aid
     * @return float
     */
    public function getOneAdAccountSpend($aid, $start, $end)
    {

        $query = "SELECT customer.id, metrics.cost_micros FROM customer WHERE segments.date BETWEEN '{$start}' AND '{$end}'";
        // $query = "SELECT customer.id, metrics.cost_micros FROM customer WHERE segments.date DURING LAST_30_DAYS";
        $rs = CurlService::getIns()->commonPostFetch($aid, $query);
        return AdAccountService::getIns()->fmtPrice($rs['results'][0]['metrics']['costMicros'] ?? 0);
    }
}
