<?php

namespace App\Services;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\TeacherClass;
use App\Models\TeacherClassLog;
use App\Models\Term;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class TeacherService extends Service
{


    /**
     * @return TeacherService
     */
    public static function getIns(): TeacherService
    {
        return parent::getInstance();
    }

    public function teacherClassList($params)
    {
        $tid = Tool::get($params, 'tid');
        $teacher = Tool::getArrayByComma(Tool::get($params, 'teacher'));
        $teacher_name = Tool::get($params, 'teacher_name');
        $tel = Tool::get($params, 'tel');
        $school = Tool::getArrayByComma(Tool::get($params, 'school'));
        $index = Tool::getArrayByComma(Tool::get($params, 'index'));
        $limit = Tool::get($params, 'page_size', 20);
        return TeacherClass::getIns()
            ->select(
                'id',
                'school_name',
                'class_name',
                'date_index',
                'teacher_name',
                'tel',
                DB::Raw('convert(price/100,decimal(10,2)) as price'),
                'start_time',
                'end_time',
                'class_locate'
            )
            ->where('tid', $tid)
            ->where('is_delete', 0)
            ->when($teacher, function ($q) use ($teacher) {
                return $q->whereIn('teacher_name', $teacher);
            })
            ->when($teacher_name, function ($q) use ($teacher_name) {
                return $q->where('teacher_name', $teacher_name);
            })
            ->when($tel, function ($q) use ($tel) {
                return $q->where('tel', $tel);
            })
            ->when($school, function ($q) use ($school) {
                return $q->whereIn('school_name', $school);
            })->when($index, function ($q) use ($index) {
                return $q->whereIn('date_index', $index);
            })
            ->orderByDesc('id')
            ->paginate($limit)
            ->appends($params)
            ->toArray();
    }

    public function checkKey($key)
    {
        return Term::getIns()->getByCond(['key' => $key])['id'] ?? 0;
    }

    public function teacherClassAddEdit($params): array
    {
        $id = Tool::get($params, 'id');
        $from = Tool::get($params, 'from');
        $keyLimit = [
            TeacherClass::getIns()->getTableColumn(),
            [
                'start_time', 'end_time', 'class_locate'
            ]
        ];
        $data = [];
        foreach ($keyLimit[$from] ?? [] as $item) {
            $val = Tool::get($params, $item);
            if (!isset($params[$item]) || $item === 'id') {
                continue;
            }
            $data[$item] = $val;
        }

        if (Tool::get($data, 'price')) {
            $data['price'] *= 100;
        }

        if ($from == 0 && empty($id)) {
            TeacherClass::getIns()->insert($data);
        } else {
            $rs = TeacherClass::getIns()->getById($id);
            if (empty($rs)) {
                return [
                    'ret' => ReturnCode::ERROR_BUSINESS,
                    'msg' => '未找到该课程'
                ];
            }
            TeacherClass::getIns()->updateByCond(['id' => $id], $data);
            if ($from) {
                TeacherClassLog::getIns()->insert(
                    [
                        'tid'        => $rs['tid'],
                        'sid'        => $id,
                        'old_record' => json_encode($rs),
                        'new_record' => json_encode($data)
                    ]
                );
            }
        }
        return [
            'ret' => ReturnCode::SUCCEED
        ];
    }

    public function teacherClassDel($params)
    {
        $id = Tool::get($params, 'id');
        $from = Tool::get($params, 'from');
        if ($from == 1) {
            return;
        }
        TeacherClass::getIns()->updateByCond(['id' => $id], ['is_delete' => 1]);
    }

    public function rebuildData($data, $tid)
    {
        TeacherClass::getIns()->delByCond(['tid' => $tid]);
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
                'tid'          => $tid,
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
                $one['start_time'] = Tool::f2Sec($time[0]);
                $one['end_time'] = Tool::f2Sec($time[1]);
            }

            TeacherClass::getIns()->insert(
                $one
            );
        }
    }
}
