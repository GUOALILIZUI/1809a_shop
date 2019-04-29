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

//自定义菜单
Route::any('custom','IndexController@custom');

Route::any('we','IndexController@we');
Route::any('weshow','IndexController@weshow');
Route::any('cc','IndexController@cc');


//群发
Route::any('GroupsUser','Groups\GroupsController@GroupsUser');

//微信支付
Route::get('pay','WeiXin\WeiXinPayController@pay');
Route::post('payBack','WeiXin\WeiXinPayController@payBack');



Route::any('img','Source\SourceController@img');
Route::any('index','Source\SourceController@index');


Route::any('code','Code\CodeController@code');

