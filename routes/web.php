<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//Route::get('/', function () {
//	phpinfo();
//});


//接口配置
Route::any('Info','IndexController@Info');
Route::any('check','IndexController@check');
Route::any('accessToken','IndexController@accessToken');