<?php

namespace App\Console\Commands\Stat;

use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Stat\AbroadDayRecharge;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\Book;
use App\Models\Stat\ChannelPayGear;
use App\Models\Stat\ExchangeRate;
use App\Models\Stat\NewBookChannelName;
use App\Models\Stat\OldAdminManager;
use App\Models\Stat\OrderExtensions;
use App\Models\Stat\Orders;
use App\Services\ApiService;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\RedisService;
use App\Services\User\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncOldDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncOldDataCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步老项目Campaign数据至投放后台广告组数据，兼容之前的数据';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            $systemInfos = CompanyService::systemInfo();
            dump($systemInfos);
            $this->exchangeRate($systemInfos);
            $this->exchangeRateData($systemInfos);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }


    public function exchangeRateData($systemInfos)
    {
        $params = [
            'base'  => '\App\Models\Facebook\\',
            'table' => 'FacebookDayAdCampaignData',
        ];
        $this->requestCommon($systemInfos, $params, FacebookDayAdSetData::getIns(), 2);
        dump('FacebookDayAdSetData is done');
    }

    public function exchangeRate($systemInfos)
    {
        $params = [
            'base'  => '\App\Models\Facebook\\',
            'table' => 'FacebookAdCampaign',
        ];
        $this->requestCommon($systemInfos, $params, FacebookSet::getIns(), 1);
        dump('FacebookAdCampaign is done');
    }

    // once 都是用繁体的库
    public function requestCommon($systemInfos, $params, $model, $rule)
    {
        $params['page_size'] = 800;
        $params['wheres'] = [
            'created_at' => ['between', ['20210101', '20220101']]
        ];
        foreach ($systemInfos as $info) {
            $page = 1;

            $params['start_link'] = 0;
            do {
                $params['page'] = $page;
                dump($page . '-' . $info['platform']);
                $rs = ApiService::getIns()->listLink($info['platform'], $params);
                if (empty(Tool::get($rs, 'list')) || empty(Tool::get($rs['list'], 'data'))) {
                    break;
                }

                foreach ($rs['list']['data'] as $row) {
                    if ($rule == 1) {
                        $isMatched = preg_match('/(?<={).+(?=})/', $row['name'], $matches);
                        if (!$isMatched) {
                            continue;
                        }
                        $explode = explode('/', $matches[0]);
                        preg_match('/\d+/', $explode[1], $linkId);
                        preg_match('/\d+/', $explode[0], $bookId);
                        $aid = str_replace('act_', '', $row['account_id']);

                        $model->updateOrInsert(
                            ['sid' => $row['campaign_id']],
                            [

                                'name'          => $row['name'],
                                'cid'           => $row['campaign_id'],
                                'status'        => 0,
                                'platform'      => $info['platform'],
                                'aid'           => $aid,
                                'user'          => strtolower($explode[2] ?? ''),
                                'book_id'       => $bookId[0] ?? 0,
                                'link_id'       => $linkId[0] ?? 0,
                                'union_link_id' => ApiService::buildPlatformLink($info['platform'], $linkId[0] ?? 0),
                            ]
                        );
                    } else {
                        $model->updateOrInsert(
                            ['sid' => $row['campaign_id'], 'event_date' => date("Ymd", strtotime($row['date']))],
                            [

                                'spend'       => $row['spend'],
                                'revenue'     => $row['purchase_value'],
                                'install'     => $row['app_install'],
                                'impressions' => $row['impressions'],
                                'clicks'      => $row['clicks'],
                            ]
                        );
                    }
                }
                $page = $rs['list']['current_page'] < $rs['list']['last_page'] ? $page + 1 : 0;
                usleep(10000);
            } while ($page);
        }
    }
}
