<?php

namespace App\Console\Commands\Stat;

use App\Models\Admin_Manager;
use App\Models\Commission\CommissionPerPeriodInfo;
use App\Models\Commission\CommissionReport;
use App\Models\Commission\CommissionSummary;
use App\Models\CreatorAssistantList;
use App\Services\User\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\Tool;

class CommissionSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CommissionSummaryCommand {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成提成总览';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $args = $this->arguments();
        $date = Tool::get($args, 'date', date("Ymd"));
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);

        // 汇总数据
        $this->doSummary($date);
        // 助理金分配
        $this->doAssistant($date);
        // $this->doSummary($date);

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }


    public function doSummary($date)
    {
        $summary = CommissionReport::getIns()
            ->select(
                'event_date',
                DB::Raw("MIN(start_date) as start_date"),
                DB::Raw("MAX(end_date) as end_date"),
                DB::Raw("SUM(total_commission) as total_commission")
            )
            ->where('event_date', $date)
            ->groupBy(['event_date'])
            ->first();
        if (!empty($summary)) {
            $summary = $summary->toArray();
            $total = [];
            $detail = CommissionReport::getIns()
                ->select('summary')
                ->where('event_date', $date)
                ->get()
                ->toArray();

            foreach ($detail as $row) {
                $sum = json_decode($row['summary'], true);
                foreach ($sum as $user => $one) {
                    if (empty($user)) {
                        continue;
                    }
                    $total[$user] = round(($total[$user] ?? 0) + $one['commission'], 2);
                }
            }
            $summary['summary'] = json_encode($total);
            $summary['total_commission'] = array_sum($total);
            CommissionSummary::getIns()->updateOrInsert(
                ['event_date' => $date],
                $summary
            );
        }
    }

    public function doAssistant($date)
    {
        $rs = CreatorAssistantList::getIns()->listAll();
        if (empty($rs)) {
            return;
        }
        // 获取总览
        $sum = CommissionSummary::getIns()->getByCond(['event_date' => $date]);
        if (empty($sum['summary'])) {
            return;
        }
        $list = [];
        $codeList = UserService::getIns()->baseAuthList('code');
        $summary = json_decode($sum['summary'], true);
        foreach ($rs as $row) {
            $row['code'] = strtolower(Tool::get($codeList, $row['uid']));
            if (empty($row['code']) ||
                $row['join_date'] < $sum['start_date'] || $row['join_date'] > $sum['end_date'] ||
                !Tool::get($summary, $row['code'])) {
                continue;
            }
            $row['leader_code'] = strtolower(Tool::get($codeList, $row['leader']));
            $row['commission'] = Tool::get($summary, $row['code']);
            $list[] = $row;
        }
        if (empty($list)) {
            return;
        }


        foreach ($list as $row) {
            // 未转正
            if (empty($row['positive_date'])) {
                $pay = $summary[$row['code']] * 0.5;
            } else {
                // 看转正日期 获取未转正之前的提成
                $data = CommissionPerPeriodInfo::getIns()->listByCond(['period' => $date, 'code' => $row['code']]);
                $total = 0; // 受影响的提成总额
                $dateInfo = $this->fmtOneDateToWM($row['positive_date']);
                if (empty($dateInfo)) {
                    continue;
                }
                foreach ($data as $one) {
                    if (!empty($one['transfer'])) {
                        continue;
                    }
                    // android
                    if ($one['os'] == 0) {
                        if ($one['event_date'] < $dateInfo[1]) {
                            $total += $one['commission'];
                        } elseif ($one['event_date'] == $dateInfo[1]) {
                            $total += $one['commission'] * $dateInfo[2];
                        }
                    } else {
                        if ($one['event_date'] < $dateInfo[3]) {
                            $total += $one['commission'];
                        } elseif ($one['event_date'] == $dateInfo[3]) {
                            $total += $one['commission'] * $dateInfo[4];
                        }
                    }
                }
                $pay = $total * 0.5;
            }

            $summary[$row['code']] = round($summary[$row['code']] - $pay, 2);
            $summary[$row['leader_code']] = round(($summary[$row['leader_code']] ?? 0) + $pay, 2);
            $this->addTransferRecord($date, $row['code'], $row['leader_code'], -$pay);
            $this->addTransferRecord($date, $row['leader_code'], $row['code'], $pay);
        }
        CommissionSummary::getIns()->updateByCond(['event_date' => $date], ['summary' => json_encode($summary)]);
    }

    public function fmtOneDateToWM($date): array
    {
        if (empty($date)) {
            return [];
        }
        // 周日为起始日
        $fmt = date("Y-W-w-m-d", strtotime($date));
        $arr = Tool::getArrayByComma($fmt, false, '-');
        $year = $arr[0];
        $w = $arr[1];
        if ($arr[2] == 0) {
            $w = $arr[1] + 1;
        }
        $wR = round(($arr[2] + 1) / 7, 2);
        $m = $arr[3];

        $ml = (strtotime(date('Y-m-d', mktime(0, 0, 0, +($m + 1), 1, $year))) -
                strtotime(date('Y-m-d', mktime(0, 0, 0, +($m), 1, $year)))) / 86400;

        $mR = round($arr[4] / $ml, 2);
        return [
            $year,
            $year . str_pad($w, 2, 0, STR_PAD_LEFT),
            $wR,
            $year . str_pad($m, 2, 0, STR_PAD_LEFT),
            $mR,
            $ml
        ];
    }

    public function addTransferRecord($period, $code, $transfer, $pay)
    {
        CommissionPerPeriodInfo::getIns()->updateOrInsert(
            ['period' => $period, 'code' => $code, 'transfer' => $transfer],
            [
                'commission' => $pay
            ]
        );
    }
}
