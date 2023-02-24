<?php

namespace App\Console\Commands\Facebook\SyncStat;

use App\Models\Facebook\Shop\FacebookShopMenu;
use App\Models\Facebook\Shop\FacebookShopProduct;
use App\Models\Facebook\Shop\FacebookShopProductSets;
use App\Services\CompanyService;
use App\Services\Facebook\BMService;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\CurlService;
use App\Services\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class SyncShopMenuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncShopMenu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步商品目录';


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
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];

            $this->syncData();
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function syncData()
    {
        $list = BMService::getAllBM();

        // 暂定50个目录 100个商品
        $baseParams = [];
        $baseParams['fields'] = 'owned_product_catalogs.limit(50){product_sets{id,name,products.limit(100)},id,name}';

        foreach ($list as $row) {
            if ($row['bm_id'] != 1027747631133957) {
                continue;
            }
            [$status, $curlMsg, $rs] = CurlService::getIns()->curlRequest(
                $row['bm_id'],
                $baseParams,
                $row['system_token']
            );
            if (!Tool::get($rs, 'owned_product_catalogs') || !Tool::get($rs['owned_product_catalogs'], 'data')) {
                continue;
            }
            foreach ($rs['owned_product_catalogs']['data'] as $one) {
                FacebookShopMenu::getIns()->updateOrInsert(
                    ['menu_id' => $one['id']],
                    ['name' => $one['name'], 'bm_id' => $row['id']]
                );
                if (!Tool::get($one, 'product_sets') || !Tool::get($one['product_sets'], 'data')) {
                    continue;
                }
                foreach ($one['product_sets']['data'] as $sets) {
                    FacebookShopProductSets::getIns()->updateOrInsert(
                        ['sets_id' => $sets['id']],
                        ['name' => $sets['name'], 'bm_id' => $row['id'], 'menu_id' => $one['id']]
                    );

                    if (!Tool::get($sets, 'products') || !Tool::get($sets['products'], 'data')) {
                        continue;
                    }
                    foreach ($sets['products']['data'] as $product) {
                        FacebookShopProduct::getIns()->updateOrInsert(
                            ['product_id' => $product['id']],
                            [
                                'name'    => $product['name'], 'bm_id' => $row['id'], 'menu_id' => $one['id'],
                                'sets_id' => $product['id'], 'retailer_id' => $product['retailer_id']
                            ]
                        );
                    }
                }
            }
        }
    }
}
