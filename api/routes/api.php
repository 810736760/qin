<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


use Illuminate\Support\Facades\Route;

// 公共权限 无需token
Route::post('login', 'ApiLoginController@login');
Route::post('refresh', 'ApiLoginController@refresh');

Route::group(['middleware' => ['AdminLog']], function () {
    Route::post('tc/class_teacher_list/{key}', 'TeacherController@tcClassTeacherList');
    Route::post('tc/class_teacher_add_edit/{key}', 'TeacherController@tcClassTeacherAddEdit');
    Route::post('class_teacher_import_excel', 'TeacherController@classTeacherExcel');
});

// 管理员权限 无须放到权限页
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('userInfo', 'ApiLoginController@getUserInfo');
    Route::post('logout', 'ApiLoginController@logout');
    Route::post('editPassword', 'ApiLoginController@savePass');
});

Route::group(['middleware' => ['jwt.auth', 'CheckAuth', 'AdminLog']], function () {
    Route::post('class_teacher_list', 'TeacherController@classTeacherList');
    Route::post('class_teacher_add_edit', 'TeacherController@classTeacherAddEdit');
    Route::post('class_teacher_conf', 'TeacherController@classTeacherConf');
    Route::post('class_teacher_add_del', 'TeacherController@classTeacherDel');

});
