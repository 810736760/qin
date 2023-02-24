<?php

namespace App\Console\Commands\Facebook\Material;

use App\Models\Material\MaterialTotal;
use App\Models\Material\OriginalMaterialData;
use App\Services\CompanyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MaterialTotalCommand extends Command
{
    protected $signature = 'MaterialTotalCommand {date?}';

    protected $description = '素材汇总表，用于素材库排序';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info($this->description . "开始执行");
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->main();
        }
        Log::info($this->description . "use time:" . (microtime(true) - $startTime));
    }

    public function main()
    {
        $date = $this->argument('date');
        if (is_null($date)) {
            $start_date = date('Ymd', strtotime('-1 day'));
            $end_date = date('Ymd');
        } else {
            $start_date = Carbon::parse($date);
            $end_date = Carbon::parse($start_date)->subDay()->format('Ymd');
        }
        $mids = OriginalMaterialData::getIns()
            ->whereBetween('date', [ $start_date, $end_date ])
            ->groupBy('mid')
            ->pluck('mid')
            ->toArray();
        $midData = OriginalMaterialData::getIns()
            ->selectRaw("
                    mid,
                    sum(spend) as spend,
                    sum(clicks) as clicks,
                      sum(purchase) as purchase,
                    sum(impressions) as impressions
                ")
            ->whereIn('mid', $mids)
            ->groupBy('mid')
            ->get()->toArray();

        foreach ($midData as $data) {
            try {
                MaterialTotal::getIns()->updateOrInsert(
                    [
                        'mid' => $data['mid']
                    ],
                    [
                        'mid'         => $data['mid'],
                        'spend'       => $data['spend'],
                        'clicks'      => $data['clicks'],
                        'purchase'    => $data['purchase'],
                        'impressions' => $data['impressions'],
                    ]
                );
            } catch (\Exception $e) {
                Log::info('素材汇总：' . $e->getMessage());
            }
        }
    }
}
