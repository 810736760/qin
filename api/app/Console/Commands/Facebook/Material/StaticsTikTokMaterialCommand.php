<?php

namespace App\Console\Commands\Facebook\Material;

use App\Helper\Tool;
use App\Models\Material\Material;
use App\Models\Material\OriginalMaterialData;
use App\Models\Tiktok\TikTokAd;
use App\Models\Tiktok\TiktokDayAdData;
use App\Models\Tiktok\TikTokMaterialHashId;
use App\Models\Tiktok\TikTokSet;
use App\Services\CompanyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StaticsTikTokMaterialCommand extends Command
{
    protected $signature = 'StaticsTikTokMaterialCommand {date?}';

    protected $description = 'TT素材数据归到素材统计表里';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $args = $this->arguments();
        $date = Tool::get($args, 'date') ?: date("Ymd");
        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];

            dump($this->description . date("Y-m-d H:m:s"));
            $this->main($date);
        }
    }

    public function main($date)
    {
        $endDate = date("Ymd", strtotime($date) + 86400);
        // 单日更新的广告数据 匹配素材id
        $rs = TiktokDayAdData::getIns()
            ->from(TiktokDayAdData::getIns()->getTableName() . ' as data')
            ->leftJoin(TikTokAd::getIns()->getTableName() . ' as ad', 'ad.ad_id', 'data.ad_id')
            ->leftJoin(TikTokMaterialHashId::getIns()->getTableName() . ' as hash', 'hash.hash_id', 'ad.hash_id')
            ->leftJoin(Material::getIns()->getTableName() . ' as m', 'm.id', 'hash.mid')
            ->select(
                'hash.mid',
                DB::Raw('m.owner_id as admin_id'),
                'ad.aid',
                'data.ad_id',
                DB::Raw('ad.sid as adset_id'),
                'hash.hash_id',
                DB::Raw('data.event_date as date'),
                'data.spend',
                'data.clicks',
                'data.spend',
                'data.impressions',
                'data.purchase'
            )
            ->whereBetween('data.updated_at', [$date, $endDate])
            ->whereNotNull('hash.mid')
            ->cursor();


        foreach ($rs as $row) {
            $row = $row->toArray();
            $row['from'] = 1;
            OriginalMaterialData::getIns()->updateOrInsert(
                ['ad_id' => $row['ad_id'], 'date' => $row['date']],
                $row
            );
        }
    }
}
