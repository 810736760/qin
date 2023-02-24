<?php

namespace App\Console\Commands\Facebook\Material;

use App\Models\Material\Material;
use App\Models\Material\OriginalMaterialData;
use App\Services\CompanyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MaterialCheckUseCommand extends Command
{
    protected $signature = 'MaterialCheckUseCommand';

    protected $description = '素材监测是否长时间未使用';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '512M');
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
        $start_date = date('Ymd', strtotime('-31 day'));
        $end_date = date('Ymd', strtotime('-1 day'));
        $mids = OriginalMaterialData::getIns()
            ->select(
                'mid',
                DB::Raw("sum(spend) as spend")
            )
            ->whereBetween('date', [$start_date, $end_date])
            ->groupBy(['mid'])
            ->get()
            ->toArray();

        $midChunks = array_chunk($mids, 1000);
        Material::getIns()->updateByCond(['created_at' => ['<', date("Y-m-d", strtotime($start_date))]], ['is_using' => 0]);
        foreach ($midChunks as $chunk) {
            $m = array_filter($chunk, function ($a) {
                return $a['spend'] >= 100;
            });
            if ($m) {
                Material::getIns()->updateByCond(['id' => ['in', array_column($m, 'mid')]], ['is_using' => 1]);
            }
        }
    }
}
