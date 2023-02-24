<?php

namespace App\Console\Commands\Facebook\Page;

use App\Helper\Tool;
use App\Services\CompanyService;
use App\Services\Facebook\BMService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Facebook\FacebookPage;
use App\Services\Facebook\FbSdkService;
use App\Models\Facebook\FacebookPagePost;
use Illuminate\Support\Facades\Log;

class SyncPageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncPage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'facebook 同步公告主页信息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $bmInfo = BMService::getAllBM();
            foreach ($bmInfo as $one) {
                $GLOBALS['bmId'] = $one['id'];
                $this->main();
            }
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    public function main()
    {
        $pages = FbSdkService::getIns()->getFbAccount();
        $list = $pages['list'] ?? [];
        if (empty($list)) {
            return;
        }
        foreach ($list as $item) {
            $pageId = $item['id'] ?? ''; // 主页id
            $name = $item['name'] ?? ''; // 主页名称
            $pageAccessToken = $item['access_token'] ?? ''; // 主页access_token

            FacebookPage::getIns()->updateOrInsert(
                [
                    'page_id' => $pageId
                ],
                [
                    'name'              => $name,
                    'page_access_token' => $pageAccessToken
                ]
            );

            $posts = FbSdkService::getIns()->getPagePost($pageId, $pageAccessToken)['list'] ?? [];

            if (!$posts) {
                continue;
            }

            // 帖子数据
            foreach ($posts as $post) {
                $postId = explode('_', $post['id'])[1] ?? ''; // 帖子id

                FacebookPagePost::getIns()->updateOrInsert(
                    [
                        'page_id' => $pageId,
                        'post_id' => $postId
                    ],
                    [
                        'message'             => $post['message'] ?? '',
                        'comment_count'       => count($post['comments']['data'] ?? []),
                        'can_reply_privately' => $post['can_reply_privately'] ?? 0,
                        'created_time'        => isset($post['created_time']) ? Carbon::parse($post['created_time'])->addHour(8) : '',
                    ]
                );
            }
        }
    }
}
