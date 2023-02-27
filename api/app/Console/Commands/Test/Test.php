<?php

namespace App\Console\Commands\Test;

use App\Helper\EncryptionHelper;
use App\Helper\Tool;

use App\Models\TeacherClass;
use App\Services\RedisService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel;

class Test extends Command
{
    protected $signature = 'Test {start_date?} {end_date?} {type?}';

    protected $description = '测试';

    public function handle()
    {
        $this->loadExcelForUser();
        echo date("Ymd");
    }

    public function loadExcelForUser()
    {
        $excel = App::make('excel');
        $data = [];
        $filePath = "material/" . '1.xlsx';
        $excel->load($filePath, function ($reader) use (&$data) {
            $data = $reader->getSheet(0)->toArray();
        });
        dump($data);
        $week = [
            '周日',
            '周一',
            '周二',
            '周三',
            '周四',
            '周五',
            '周六'

        ];
        $wIndex = array_flip($week);
        foreach ($data as $index => $row) {
            if ($index === 0) {
                continue;
            }
            $one = [
                'tid'          => 1,
                'tel'          => $row[4],
                'school_name'  => $row[0],
                'class_name'   => $row[1],
                'date_index'   => $wIndex[$row[2]],
                'teacher_name' => $row[3],
                'price'        => $row[5] * 100,
                'class_locate' => $row[7] ?? ''
            ];
            if (!empty($row[6])) {
                $time = explode('-', $row[6]);
                $one['start_time'] = $this->f2Sec($time[0]);
                $one['end_time'] = $this->f2Sec($time[1]);
            }
            dump($one);
            TeacherClass::getIns()->insert(
                $one
            );
        }
    }

    public function f2Sec($str)
    {
        $h = explode(':', $str);
        return $h[0] * 3600 + $h[1] * 60;
    }
}
