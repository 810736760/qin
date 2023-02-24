<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use App\Models\Commission\CommissionInfo;
use App\Models\Commission\CommissionPerPeriodInfo;
use App\Models\Commission\CommissionReport;
use App\Models\Commission\CommissionSummary;
use Illuminate\Console\Command;

class CommissionRollBackCommand extends Command
{
    protected $signature = 'CommissionRollBack {period?}';

    protected $description = '提成统计回滚';


    public function handle()
    {
        $args = $this->arguments();
        $period = Tool::get($args, 'period') ?: 20221101;
        $rs = CommissionPerPeriodInfo::getIns()->listByCond(
            [
                'period' => $period
            ]
        );

        if (empty($rs)) {
            dump('无数据');
            return;
        }

        $eventDates = Tool::getUniqueArr($rs, 'event_date', true);
        $info = CommissionInfo::getIns()->listByCond([
            'event_date' => ['in', $eventDates]
        ]);
        if (empty($info)) {
            dump('无记录数据');
            return;
        }

        $fmtInfo = [];
        foreach ($info as $row) {
            $fmtInfo[implode("-", [$row['event_date'], $row['platform'], $row['os']])] = json_decode($row['info'], true);
        }



        $change = [];
        foreach ($rs as $one) {
            $key = implode("-", [$one['event_date'], $one['platform'], $one['os']]);
            if (!Tool::get($fmtInfo, $key)) {
                continue;
            }


            $m = !empty($one['union_link_id']) ? $one['union_link_id'] : $one['code'];
           
            $fmtInfo[$key][$m] -= $one['commission'];
            $fmtInfo[$key][$m] = round($fmtInfo[$key][$m], 2);
            $change[$key] = 1;
        }


        foreach ($change as $key => $val) {
            $k = explode('-', $key);
            CommissionInfo::getIns()->updateByCond(
                ['event_date' => $k[0], 'platform' => $k[1], 'os' => $k[2]],
                ['info' => json_encode($fmtInfo[$key])]
            );
        }


        CommissionPerPeriodInfo::getIns()->delByCond(['period' => $period]);
        CommissionReport::getIns()->delByCond(['event_date' => $period]);
        CommissionSummary::getIns()->delByCond(['event_date' => $period]);


        dump($this->description . ' is done');
    }
}
