<?php

//
// Route::get('/', function () {
//     return view('index');
// });
//
//
// Route::get('/{path}', function () {
//     return view('index');
// });
//
// Route::get('/{path}/{path2}', function () {
//     return view('index');
// });

Route::domain('www.zeenovel.com')->group(base_path('routes/share.php'));
Route::get('/', 'ShowController@index');
Route::get('/{path}', 'ShowController@index');
Route::get('/{path}/{path2}', 'ShowController@index');
