<?php

namespace App\Console\Commands\Facebook\Material;

use App\Helper\Tool;
use App\Models\Material\Material;
use App\Models\Material\MaterialHashId;
use App\Models\Material\OriginalMaterialData;
use App\Services\CompanyService;
use App\Services\Facebook\AdAccountService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CurlService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OriginalDataCommand extends Command
{
    protected $signature = 'OriginalDataCommand {date?}';

    protected $description = '素材原始数据采集';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        dump("素材原始数据采集 开始");
        Log::info($this->description . "开始执行:" . date('Y-m-d H:m:s'));
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $this->main();
        }
        Log::info($this->description . "use time:" . (microtime(true) - $startTime));
    }

    public function main()
    {
        $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');
        $accountId = AdAccountService::getIns()->getActiveAdAccount([
            'aid',
            'platform',
            'id',
            'bm_id',
            'timezone_offset_hours_utc'
        ]);
        $fields = json_encode([
            'account_id',
            'ad_id',
            'adset_id',
            'spend',
            'impressions',
            'clicks',
            'actions',
            'unique_actions'
        ]);
        $params = [
            'fields'                     => $fields,
            'action_attribution_windows' => ['1d_click', '1d_view'],
            'breakdowns'                 => 'media_asset',
            'time_range'                 => [
                'since' => "",
                'until' => ""
            ],
            'level'                      => 'ad',
            'limit'                      => 1000
        ];
        foreach ($accountId as $item) {
            $date = $this->getDate($item['timezone_offset_hours_utc']);
            $params['time_range']['since'] = $params['time_range']['until'] = $date;
            $token = Tool::get($tokenMap, $item['bm_id'], config('facebook.access_token'));
            [$status, $curlMsg, $data] = CurlService::getIns()->curlRequest(
                Tool::fmtAid($item['aid']) . "/insights",
                $params,
                $token
            );
            if (!isset($data['data'])) {
                if (isset($data['error'])) {
                    Log::info('素材采集异常' . json_encode($data));
                }
                continue;
            }
            $data = $data['data'];
            foreach ($data as &$value) {
                $value["date"] = date('Ymd', strtotime($date));
                $value["spend"] = $value["spend"] ?? 0;
                $value["impressions"] = $value["impressions"] ?? 0;
                $value["clicks"] = $value["clicks"] ?? 0;
                $value["hash_id"] = $value["media_asset"]["video_id"] ?? ($value["media_asset"]["hash"] ?? 0);
                if (empty($value["hash_id"])) {
                    Log::info('素材数据异常' . json_encode($value));
                }
                $value['aid'] = $value["account_id"];
                $value["mid"] = MaterialHashId::getIns()
                        ->where('hash_id', $value["hash_id"])->value('mid') ?? 0;
                if ($value["mid"] != 0) {
                    $value["admin_id"] = Material::getIns()
                            ->whereKey($value["mid"])->value('owner_id') ?? 0;
                }
                $ac = array_column($value['actions'] ?? [], 'value', 'action_type');
                $dayV = array_column($value['unique_actions'] ?? [], 'value', 'action_type');
                $value['purchase'] = $ac['omni_purchase'] ?? 0;
                $value['unique_purchase'] = $dayV['omni_purchase'] ?? 0;
                unset($value["account_id"]);
                unset($value['media_asset']);
                unset($value['date_start']);
                unset($value['date_stop']);
                unset($value['actions']);
                OriginalMaterialData::getIns()
                    ->updateOrInsert(
                        [
                            'aid'      => $value['aid'],
                            'ad_id'    => $value['ad_id'],
                            'adset_id' => $value['adset_id'],
                            'hash_id'  => $value['hash_id'],
                            'date'     => $value["date"]
                        ],
                        $value
                    );
            }
        }
    }

    // 获取对应时区-时间
    public function getDate($timezone)
    {
        $param_date = $this->argument('date') ?? "";
        if ($param_date == "") {
            $date = Tool::getTodayDateWithTimeZone($timezone, config('app.timezone_offset'));
            if (date("H") == '01') {
                $date = Carbon::parse($param_date)
                    ->subDay()
                    ->format("Y-m-d");
            }
        } else {
            $date = date("Y-m-d", strtotime($param_date));
        }
        return $date;
    }
}
