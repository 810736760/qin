<?php

namespace App\Console\Commands\Facebook\PublicPage;

use Illuminate\Console\Command;

class SyncPageListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:SyncPageList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'facebook 公共主页列表';

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
        //
    }
}
