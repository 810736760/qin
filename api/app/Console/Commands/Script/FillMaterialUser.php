<?php

namespace App\Console\Commands\Script;

use App\Services\MaterialService;
use Illuminate\Console\Command;

class FillMaterialUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'script:fillMaterialUser {start?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '补充素材指派表';


    /**
     * php artisan script:fillMaterialUser
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $args = $this->arguments();
        \Log::info($this->description . "开始执行", $args);
        dump($this->description . "开始执行");
        $startTime = microtime(true);
        $start = $args['start'] == null ? 0 : $args['start'];
        MaterialService::getIns()->fillMaterialWithUsersId($start);
        $msg = $this->description . "use time:" . (microtime(true) - $startTime);
        \Log::info($msg, $args);
        dump($msg);
    }
}
