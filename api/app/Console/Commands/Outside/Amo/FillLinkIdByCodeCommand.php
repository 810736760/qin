<?php

namespace App\Console\Commands\Outside\Amo;

use App\Helper\Tool;
use App\Models\Facebook\FacebookSet;
use App\Models\Outside\Amo\AmoCodes;
use App\Models\Outside\Amo\AmoCodesLinkMap;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\OldAdminManager;
use App\Services\ApiService;
use App\Services\Stat\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FillLinkIdByCodeCommand extends Command
{
    protected $signature = 'outside:AmoFillLinkIdByCode {platform?}';

    protected $description = '补充之前由code生成的LinkId';


    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        $platform = Tool::get($args, 'platform', 100);

        $rs = FacebookSet::getIns()
            ->from(FacebookSet::getIns()->getTableName() . ' as sets')
            ->leftJoin(AmoCodesLinkMap::getIns()->getTableName() . ' as map', 'map.union_link_id', 'sets.union_link_id')
            ->select('sets.union_link_id', 'sets.name', 'sets.user')
            ->where('sets.platform', $platform)
            ->whereNull('map.code')
            ->groupBy(['sets.union_link_id'])
            ->get()
            ->toArray();

        if (empty($rs)) {
            Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
            return;
        }

        $data = [];
        foreach ($rs as $row) {
            $codeRs = Tool::decodeUrlQuery($row['name']);
            if (!Tool::get($codeRs, 'code')) {
                continue;
            }
            $codeRs['code'] = trim($codeRs['code']);
            if (in_array($codeRs['code'], AmoCodes::DEFAULT_CODE)) {
                continue;
            }
            AmoCodesLinkMap::getIns()->updateOrInsert(
                ['union_link_id' => $row['union_link_id']],
                ['code' => $codeRs['code']]
            );
            $data[$row['union_link_id']] = [
                'union_link_id' => $row['union_link_id'],
                'code'          => $codeRs['code'],
                'link_id'       => ApiService::rebuildPlatformLink($platform, $row['union_link_id']),
                'user'          => $row['user']
            ];
        }

        $codeInfo = AmoCodes::getIns()->listByCond(['code' => ['in', array_column($data, 'code')]]);
        $codeMap = array_column($codeInfo, null, 'code');

        // $user = Tool::getUniqueArr($data, 'user');
        $userData = OldAdminManager::getIns()->select(
            DB::Raw('LOWER(code) as code'),
            'uid'
        )
            ->pluck('uid', 'code')->toArray();


        foreach ($data as $linkId => $row) {
            $codeData = Tool::get($codeMap, $row['code'], []);
            $link = str_replace('{code}', $row['code'], ReportService::AMP_DEEP_LINK_URL);
            $one = [
                'link_id'          => ApiService::rebuildPlatformLink($platform, $row['union_link_id']),
                'platform'         => $platform,
                'link'             => $link,
                'name'             => $link,
                'promoter_user_id' => $row['user'] ? ($userData[$row['user']] ?? 0) : 0,
            ];


            if (!empty($codeData)) {
                $one['system'] = $codeData['os'] - 1;
                $one['link_type'] = $codeData['is_pixel'] ? 3 : 0;
                $one['is_default'] = $codeData['is_default'];
                $one['book_id'] = $codeData['book_id'];
                $one['ad_market'] = $codeData['media_source'];
                if ($one['link_type'] == 3) {
                    $one['link'] = str_replace('{code}', $row['code'], ReportService::AMP_SHARE_URL);
                }
            }


            AbroadLink::getIns()->updateOrInsert(
                ['union_link_id' => $linkId],
                $one
            );
        }


        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }
}
