<?php
/**
 * Created by PhpStorm.
 * User: xubin
 * Date: 6/7/22
 * Time: 5:53 PM
 */

use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/middle/{key}', 'ShareController@link'); // 分享链接
Route::post('/click', 'ShareController@linkClick'); // 点击链接
