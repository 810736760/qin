<?php

namespace App\Console\Commands\Task;

use App\Services\DingTalk\AlarmService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TaskCommand extends Command
{
    protected $signature = 'Task';

    protected $description = 'Task';


    public function handle()
    {
        $w = date('w');
        if ($w == 0 || $w == 6) {
            return;
        }
        AlarmService::dingdingSend(
            '下班打卡了',
            [],
            'f8083f3214fc046bff240f0f65b700cd6195afedd493e232af4177dc6954ba3d',
            '打卡',
            true
        );
    }
}
