<?php

namespace App\Console\Commands\RabbitMq;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\Facebook\DraftAdModel;
use App\Models\Facebook\DraftAdSetModel;
use App\Models\Facebook\DraftCampaignModel;
use App\Models\Task\Task;
use App\Services\Facebook\CommonService;
use App\Services\Facebook\DraftService;
use App\Services\Facebook\FbSdkService;
use App\Services\Facebook\TaskService;
use App\Services\RedisService;
use Exception;
use Illuminate\Console\Command;

class RabbitMqConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RabbitMqConsume {ConsumerName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $consumeName = $this->argument('ConsumerName');
        $className = "App\\Libs\\RabbitMq\\" . $consumeName;
        if (!class_exists($className)) {
            throw new \Exception("This consumer is not exist----" . $consumeName);
        }
        $consumer = new $className;
        $consumer->consume();
    }
}
