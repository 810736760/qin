<?php

namespace App\Console\Commands\Tiktok\Sync;

use App\Models\Facebook\FacebookPixel;

use App\Models\Tiktok\TiktokPixel;
use App\Services\CompanyService;
use App\Services\Tiktok\AdAccountService;
use App\Services\Tiktok\CurlService;
use App\Services\Tiktok\InsightsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncPixelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:SyncPixel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步TT下的像素代码';


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
            if ($row['id'] != 1) {
                continue;
            }
            $aidRs = AdAccountService::getIns()->getActiveAdAccount(['aid']);
            foreach ($aidRs as $one) {
                $this->main($one);
            }
            // $this->main(['aid' => 7078923687503675394]);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main($info)
    {
        [$status, $msg, $rs, $code] = CurlService::getIns()->tkCurl(
            'pixel/list/',
            [
                'advertiser_id' => $info['aid'],
                'page'          => 1,
                'page_size'     => 20
            ]
        );
        if ($code || !Tool::get($rs['data'], 'pixels')) {
            return;
        }
        foreach ($rs['data']['pixels'] as $one) {
            $attr = [
                'pixel_id' => $one['pixel_code'],
            ];
            $value = [
                'name'            => $one['pixel_name'],
                'aid'             => $info['aid'],
                'pixel_number_id' => $one['pixel_id']
            ];
            TiktokPixel::getIns()->updateOrInsert($attr, $value);
        }
    }
}
