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

class StaticsMaterialCommand extends Command
{
    protected $signature = 'StaticsMaterialCommand {date?}';

    protected $description = '非动态素材原始数据采集';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            dump("静态素材原始数据采集: " . date("Y-m-d H:m:s"));
            $this->main();
        }
    }

    public function main()
    {
        // 获取活跃广告ID
        $tokenMap = array_column(BMService::getAllBM(), 'system_token', 'id');
        if (empty($tokenMap)) {
            return;
        }
        $ActiveAccountIds = AdAccountService::getIns()->getActiveAdAccount([
            'aid',
            'platform',
            'id',
            'bm_id',
            'timezone_offset_hours_utc'
        ]);
        // 获取活跃账户
        $params = [
            'limit'                           => 200,
            'sort'                            => 'spend_descending',
            'level'                           => 'ad',
            'time_range'                      => [
                'since' => "",
                'until' => ""
            ],
            'fields'                          => [
                'account_id',
                'ad_id',
                'adset_id',
                'spend',
                'impressions',
                'clicks',
                'actions'
            ],
            'use_unified_attribution_setting' => true
        ];
        foreach ($ActiveAccountIds as $account) {
            $token = Tool::get($tokenMap, $account['bm_id'], config('facebook.access_token'));
            $date = $this->getDate($account['timezone_offset_hours_utc']);
            $params['time_range']['since'] = $date;
            $params['time_range']['until'] = $date;
            [$status, $curlMsg, $ad_ids] = CurlService::getIns()->curlRequest(
                Tool::fmtAid($account['aid']) . "/insights",
                $params,
                $token
            );
            if (Tool::get($ad_ids, 'error') || is_null($ad_ids)) {
                continue;
            }

            $idArr = array_column($ad_ids['data'], 'ad_id');
            if (empty($idArr)) {
                continue;
            }

            $insightMap = array_column($ad_ids['data'], null, 'ad_id');
            $chunk = array_chunk($idArr, 50);
            foreach ($chunk as $oneChunk) {
                $id_str = implode(',', $oneChunk);
                // 通过广告ID判断是否为静态广告
                [$status, $curlMsg, $is_creative_ad] = CurlService::getIns()->curlRequest(
                    '',
                    [
                        'ids'    => $id_str,
                        'fields' => [
                            'adset{is_dynamic_creative}',
                            'creative{object_story_spec{video_data{video_id},link_data{image_hash}}}'
                        ]
                    ],
                    $token
                );
                if (Tool::get($is_creative_ad, 'error') || is_null($is_creative_ad)) {
                    continue;
                }
                $not_creative_ad = [];


                foreach ($is_creative_ad as $ad_id => $content) {
                    // 非动态素材
                    if (isset($content['adset']) && !$content['adset']['is_dynamic_creative']) {
                        if (isset($content['creative']['object_story_spec']['video_data']['video_id'])) {
                            $not_creative_ad[$ad_id] = $content['creative']['object_story_spec']['video_data']['video_id'];
                        }
                        if (isset($content['creative']['object_story_spec']['link_data']['image_hash'])) {
                            $not_creative_ad[$ad_id] = $content['creative']['object_story_spec']['link_data']['image_hash'];
                        }
                        if (!Tool::get($insightMap, $ad_id) || !Tool::get($not_creative_ad, $ad_id)) {
                            continue;
                        }
                        $this->insert_data($insightMap[$ad_id], $not_creative_ad[$ad_id], $date);
                    }
                }
            }

        }
    }

    public function insert_data($arr, $hash_or_id, $date)
    {
        $insert_data["date"] = date('Ymd', strtotime($date));
        $insert_data["spend"] = $arr["spend"] ?? 0;
        $insert_data["impressions"] = $arr["impressions"] ?? 0;
        $insert_data["clicks"] = $arr["clicks"] ?? 0;
        $insert_data["hash_id"] = $hash_or_id ?? "";
        $insert_data['aid'] = $arr["account_id"] ?? 0;
        $insert_data['ad_id'] = $arr["ad_id"] ?? 0;
        $insert_data['adset_id'] = $arr["adset_id"] ?? 0;
        $insert_data["mid"] = MaterialHashId::getIns()
                ->where('hash_id', $hash_or_id)->value('mid') ?? 0;
        $ac = array_column($arr['actions'] ?? [], 'value', 'action_type');
        $insert_data['purchase'] = $ac['omni_purchase'] ?? 0;
        if ($insert_data["mid"] != 0) {
            $insert_data["admin_id"] = Material::getIns()
                    ->whereKey($insert_data["mid"])->value('owner_id') ?? 0;
        }
        unset($insert_data['actions']);
        OriginalMaterialData::getIns()->updateOrInsert(
            [
                'aid'      => $insert_data['aid'],
                'ad_id'    => $insert_data['ad_id'],
                'adset_id' => $insert_data['adset_id'],
                'hash_id'  => $insert_data['hash_id'],
                'date'     => $insert_data["date"]
            ],
            $insert_data
        );
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
