<?php

namespace App\Http\Controllers;

use App\Helper\Tool;
use App\Models\TeacherClass;
use App\Models\Term;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use App\Definition\ReturnCode;

class TeacherController extends Controller
{
    public function classTeacherList(Request $request)
    {
        $this->emptyRs($request->input('tid'));
        $this->responseApi(
            ReturnCode::SUCCEED,
            '',
            TeacherService::getIns()->teacherClassList($request->all())
        );
    }

    public function tcClassTeacherList($key, Request $request)
    {
        $params = $request->all();
        $this->emptyRs($tid = TeacherService::getIns()->checkKey($key));
        $params['tid'] = $tid;
        $this->responseApi(
            ReturnCode::SUCCEED,
            '',
            TeacherService::getIns()->teacherClassList($params)
        );
    }

    public function classTeacherConf(Request $request)
    {
        $this->emptyRs($tid = $request->input('tid'));
        // 获取期数
        $terms = Term::getIns()->listAll(['*'], 'event_date');
        $rs = TeacherClass::getIns()->listByCond(['tid' => $tid, 'is_delete' => 0]);
        // 获取教师
        $teacher = Tool::getUniqueArr($rs, 'teacher_name', true);
        // 获取学校
        $school = Tool::getUniqueArr($rs, 'school_name', true);
        $this->responseApi(
            ReturnCode::SUCCEED,
            '',
            compact('terms', 'teacher', 'school')
        );
    }


    public function tcClassTeacherAddEdit($key, Request $request)
    {
        $params = $request->all();
        $this->emptyRs($tid = TeacherService::getIns()->checkKey($key));
        $params['tid'] = $tid;
        $info = TeacherService::getIns()->teacherClassAddEdit($params);
        $this->responseApi(
            $info['ret'],
            $info['msg'] ?? ''
        );
    }

    public function classTeacherAddEdit(Request $request)
    {
        $info = TeacherService::getIns()->teacherClassAddEdit($request->all());
        $this->responseApi(
            $info['ret'],
            $info['msg'] ?? ''
        );
    }

    public function classTeacherDel(Request $request)
    {
        TeacherService::getIns()->teacherClassDel($request->all());
        $this->responseApi(
            ReturnCode::SUCCEED
        );
    }
}
