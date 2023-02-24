<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\FacebookPixel;

use App\Services\CompanyService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
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
    protected $signature = 'facebook:SyncPixel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步BM下的像素代码';


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
        $tokenMap = array_column(BMService::getAllBM(), null, 'id');
        if (empty($tokenMap)) {
            return;
        }
        foreach ($tokenMap as $bmId => $row) {
            [$status, $curlMsg, $rs] = CurlService::getIns()->curlRequest(
                $row['bm_id'] . '/adspixels',
                ['fields' => 'name', 'limit' => 50],
                $row['system_token']
            );

            foreach ($rs['data'] as $one) {
                $attr = [
                    'pixel_id' => $one['id'],
                ];
                $value = [
                    'name'  => $one['name'],
                    'bm_id' => $bmId
                ];
                FacebookPixel::getIns()->updateOrInsert($attr, $value);
            }
        }
    }
}
