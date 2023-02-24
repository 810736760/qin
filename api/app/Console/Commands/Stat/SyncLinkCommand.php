<?php

namespace App\Console\Commands\Stat;

use App\Models\Stat\AbroadDayRecharge;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\Book;
use App\Models\Stat\ChannelPayGear;
use App\Models\Stat\ExchangeRate;
use App\Models\Stat\NewBookChannelName;
use App\Models\Stat\OldAdminManager;
use App\Models\Stat\OrderExtensions;
use App\Models\Stat\Orders;
use App\Models\Stat\Video;
use App\Models\Stat\VideoEpisodes;
use App\Services\ApiService;
use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\RedisService;
use App\Services\User\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncLinkCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据ID同步其他项目数据';


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

            $GLOBALS['co'] = $row['id'];
            $GLOBALS['old_co_id'] = $row['old_co_id'] ?? 0;
            $systemInfos = CompanyService::systemInfo();
            $this->adminManger($systemInfos);

            $uids = [];
            if ($row['old_co_id']) {
                $rs = UserService::getIns()->getUidInOldAdmin($row['id'], $row['old_co_id']);
                if (empty($rs)) {
                    continue;
                }
                $uids = array_column($rs, 'uid');
            }
            $this->abroadLink($systemInfos, $uids);
            $this->books($systemInfos);
            // $this->orders($systemInfos);
            $this->ordersExtensions($systemInfos);
            $this->channelPayGear($systemInfos);
            $this->newBookChannelName($systemInfos);
            $this->exchangeRate($systemInfos);

            $this->video($systemInfos);
            $this->episodes($systemInfos);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    // // once 都是用繁体的库
    // public function requestApi($systemInfos, $params, $model, $keyDate = 'link_id', $once = false)
    // {
    //     $params['page_size'] = 800;
    //     foreach ($systemInfos as $info) {
    //         if ($once) {
    //             if ($info['platform'] != 1) {
    //                 continue;
    //             }
    //         }
    //         // 获取最大linkId
    //         $rs = $model::getIns()
    //             ->where('platform', $info['platform'])
    //             ->orderByDesc($keyDate)
    //             ->first();
    //         if (empty($rs)) {
    //             $startId = 0;
    //         } else {
    //             $rs = $rs->toArray();
    //             $startId = Tool::get($rs, $keyDate, 0);
    //         }
    //         $page = 1;
    //         $model->delByCond([$keyDate => ['>', $startId], 'platform' => $info['platform']]);
    //         $params['start_link'] = $startId;
    //         do {
    //             $params['page'] = $page;
    //             $rs = ApiService::getIns()->listLink($info['platform'], $params);
    //             if (empty(Tool::get($rs, 'list')) || empty(Tool::get($rs['list'], 'data'))) {
    //                 break;
    //             }
    //
    //             foreach ($rs['list']['data'] as &$row) {
    //                 $row[$keyDate] = $row['id'];
    //                 $row['platform'] = $info['platform'];
    //                 if (array_key_exists('link_id', $row)) {
    //                     $row['union_link_id'] = ApiService::buildPlatformLink($info['platform'], $row['link_id']);
    //                 }
    //                 if (array_key_exists('order_id', $row)) {
    //                     $row['union_order_id'] = ApiService::buildPlatformLink($info['platform'], $row['order_id']);
    //                 }
    //
    //
    //                 unset($row['id']);
    //             }
    //             $model->insert($rs['list']['data']);
    //             $page = $rs['list']['current_page'] < $rs['list']['last_page'] ? $page + 1 : 0;
    //             usleep(10000);
    //         } while ($page);
    //     }
    // }

    public function abroadLink($systemInfos, $uids)
    {
        $params = [
            'table' => 'AbroadLink',
        ];
        if ($uids) {
            $params['wheres'] = [
                'promoter_user_id' => ['in', $uids]
            ];
        }
        ApiService::getIns()->requestCommon($systemInfos, $params, AbroadLink::getIns());
        dump('abroadLink is done');
    }


    public function books($systemInfos)
    {
        $params = [
            'table' => 'Book',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, Book::getIns(), 'book_id', true);
        dump('books is done');
    }


    public function orders($systemInfos)
    {
        $params = [
            'table' => 'Orders',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, Orders::getIns(), 'order_id');
        dump('orders is done');
    }

    public function newBookChannelName($systemInfos)
    {
        $params = [
            'table' => 'NewBookChannelName',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, NewBookChannelName::getIns(), 'name_id', true);
        dump('newBookChannelName is done');
    }


    public function ordersExtensions($systemInfos)
    {
        $params = [
            'table' => 'OrderExtension',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, OrderExtensions::getIns(), 'oe_id');
        dump('ordersExtensions is done');
    }


    public function adminManger($systemInfos)
    {
        $params = [
            'table' => 'LoginAdminManager',
        ];
        if ($GLOBALS['old_co_id']) {
            $params['wheres'] = [
                'company_id' => $GLOBALS['old_co_id']
            ];
        }
        ApiService::getIns()->requestCommon($systemInfos, $params, OldAdminManager::getIns(), 'uid', true);
        RedisService::getIns()->del(UserService::getOldAllUserCache(PublicService::getBusinessId()));
        dump('adminManger is done');
    }

    public function channelPayGear($systemInfos)
    {
        $params = [
            'table' => 'ChannelPayGear',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, ChannelPayGear::getIns(), 'cpg_id');
        dump('channelPayGear is done');
    }

    public function exchangeRate($systemInfos)
    {
        $params = [
            'table' => 'ExchangeRate',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, ExchangeRate::getIns(), 'e_id', true, 2);
        dump('ExchangeRate is done');
    }


    public function video($systemInfos)
    {
        $params = [
            'table' => 'Video',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, Video::getIns(), 'v_id');
        dump('video is done');
    }

    public function episodes($systemInfos)
    {
        $params = [
            'table' => 'VideoEpisodes',
        ];
        ApiService::getIns()->requestCommon($systemInfos, $params, VideoEpisodes::getIns(), 'episodes_id');
        dump('episodes is done');
    }

}
