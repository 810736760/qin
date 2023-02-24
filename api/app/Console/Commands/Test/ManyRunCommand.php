<?php

namespace App\Console\Commands\Test;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ManyRunCommand extends Command
{

    protected $signature = 'ManyRunCommand {start_date?} {end_date?} {format?} {call?}';

    protected $description = '多日期跑数据 must:end_date > start_date;';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $args = $this->argument();
        $start_date = Carbon::parse($args['start_date'] ?? date('Y-m-d'));
        $end_date = Carbon::parse($args['end_date'] ?? date('Y-m-d'));
        $format = $args['format'] ?? "Ymd";
        $commands = [];
        if (isset($args['call'])) {
            $commands = explode(',', $args['call']);
        }
        while ($end_date >= $start_date) {
            foreach ($commands as $command) {
                Artisan::call($command, [ 'date' => $start_date->format($format) ]);
            }
            $start_date->addDay();
        }
    }
}
