<?php

namespace App\Console\Commands\Task;

use App\Helper\Tool;
use App\Mail\EmailShipped;
use App\Models\TeacherClassLog;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TaskCommand extends Command
{
    protected $signature = 'Task';

    protected $description = 'Task';


    public function handle()
    {
        $this->checkChange();
    }

    public function checkChange()
    {
        $rs = TeacherClassLog::getIns()->listByCond([
            'created_at' => ['>', date("Ymd", strtotime('-7 day'))],
            'use'        => 0
        ]);
        if (empty($rs)) {
            return;
        }

        $tids = Tool::getUniqueArr($rs, 'tid', true);
        $tidRs = Term::getIns()->listByCond(['id' => ['in', $tids]]);
        $tidMap = array_column($tidRs, 'name', 'id');
        $alarmTable = [];

        $cnMap = [
            'start_time'   => '开始时间',
            'end_time'     => '结束时间',
            'class_locate' => '教室位置',
        ];
        foreach ($rs as $row) {
            $old = json_decode($row['old_record'], true);
            $new = json_decode($row['new_record'], true);
            $one = [
                'term_name'    => $tidMap[$row['tid']],
                'teacher_name' => $old['teacher_name'],
                'tel'          => $old['tel'],
                'school_name'  => $old['school_name'],
                'class_name'   => $old['class_name'],
                'updated_at'   => $old['updated_at'],
            ];
            $content = '';
            foreach ($new as $item => $val) {
                if ($val == $old[$item] || !Tool::get($cnMap, $item)) {
                    continue;
                }
                if (in_array($item, ['start_time', 'end_time'])) {
                    $val = Tool::fmtHMFromSec($val);
                    $old[$item] = Tool::fmtHMFromSec($old[$item]);
                }
                $content .= $cnMap[$item] . ' : ' . $old[$item] . '->' . $val . PHP_EOL;
            }
            if (empty($content)) {
                continue;
            }
            $one['content'] = $content;
            $alarmTable[] = $one;
        }
        if (empty($alarmTable)) {
            return;
        }
        Mail::to(['360686626@qq.com'])->send(
            new EmailShipped(
                $alarmTable,
                'tc_change',
                date("Ymd") . '教师班级变更记录'
            )
        );
        $rid = array_column($rs, 'id');
        TeacherClassLog::getIns()->updateByCond(
            ['id' => ['in', $rid]],
            ['use' => 1]
        );
    }
}
