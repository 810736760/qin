<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookAdAccount;
use App\Models\Facebook\FacebookApp;
use App\Models\Facebook\FacebookCampaign;
use App\Models\Facebook\FacebookAd;
use App\Models\Facebook\FacebookDataSnapshot;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Services\Common\DBService;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\CurlService;
use App\Services\Facebook\FbSdkService;
use App\Services\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncApp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步BM下的APP';


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
            $this->sync();
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function sync()
    {
        $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');
        if (empty($tokenMap)) {
            return;
        }

        $shop = ['google_play', 'itunes'];
        foreach ($tokenMap as $bmId => $token) {
            $aidRs = FacebookAdAccount::getIns()->where('bm_id', $bmId)
                ->orderByDesc('spend')->first();
            if (empty($aidRs)) {
                continue;
            }
            $list = FbSdkService::getIns()->getAppListByOs($aidRs->aid);
            if (empty($list['list'])) {
                continue;
            }
            foreach ($list['list'] as $row) {
                $attr = [
                    'app_id' => $row['id'],
                    'bm_id'  => $bmId
                ];
                $value = [
                    'name'     => $row['name'],
                    'icon_url' => $row['icon_url'],
                ];
                foreach ($shop as $item) {
                    $value[$item] = '';
                    if (Tool::get($row['object_store_urls'], $item)) {
                        $value[$item] = $row['object_store_urls'][$item];
                    }
                }
                FacebookApp::getIns()->updateOrInsert($attr, $value);
            }
        }
    }
}
