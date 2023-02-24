<?php

namespace App\Console\Commands\Facebook\Task;

use App\Services\CompanyService;
use App\Services\DingTalk\AlarmService;
use App\Services\Facebook\CommonService;
use Illuminate\Console\Command;

class CheckToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:CheckToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查管理员Token';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $args = $this->arguments();
        \Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        foreach (CompanyService::coList(1) as $row) {
            if ($row['id'] != 1) {
                continue;
            }
            $GLOBALS['co'] = $row['id'];
            [$msg, $tel] = CommonService::getIns()->checkToken();
            AlarmService::dingdingSend($msg, $tel, $row['dingding_secret'], $row['dingding_keyword']);
        }


        \Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }
}
