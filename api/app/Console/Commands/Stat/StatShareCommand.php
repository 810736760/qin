<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use App\Models\Setting\ShareConf;
use App\Models\Stat\ShareStat;
use App\Services\RedisService;
use App\Services\Setting\ShareService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class StatShareCommand extends Command
{
    protected $signature = 'StatShareCommand';

    protected $description = '统计点击链接';


    public function handle()
    {
        \Log::info($this->description . '开始');
        $startTime = microtime(true);

        // 统计昨日访问情况
        $date = date("Ymd", strtotime('-1 day'));
        // $date = date("Ymd");
        $items = ['View', 'Click'];


        foreach ($items as $index => $item) {
            $mapName = ShareService::getIns()->viewOrClickMapHashName($date, $item);
            $mapRs = RedisService::getIns()->hGetAll($mapName);
            if (empty($mapRs)) {
                continue;
            }
            $keys = array_keys($mapRs);
            $hitConf = ShareConf::getIns()
                ->select('id', 'total_view', 'total_click')
                ->whereIn('id', $keys)
                ->get()
                ->toArray();
            if (empty($hitConf)) {
                continue;
            }
            foreach ($hitConf as $rows) {
                if (!Tool::get($mapRs, $rows['id'])) {
                    continue;
                }
                $id = $rows['id'];
                $times = $mapRs[$id];

                $detailName = ShareService::getIns()->viewOrClickDetailHashName($id, $date, $item);
                $ips = RedisService::getIns()->hLen($detailName);
                ShareStat::getIns()->updateOrInsert(
                    [
                        'share_id'   => $id,
                        'event_date' => $date,
                        'type'       => $index
                    ],
                    [
                        'ip'    => $ips,
                        'times' => $times
                    ]
                );

                $keyName = 'total_' . strtolower($item);
                ShareConf::getIns()->updateByCond(
                    ['id' => $id],
                    [
                        $keyName => $times + $rows[$keyName]
                    ]
                );
            }
        }


        Log::info($this->description . '结束,耗时：' . (microtime(true) - $startTime));
    }
}
