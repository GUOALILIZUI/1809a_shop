<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('goods', GoodsController::class);
    $router->resource('order', OrderController::class);
    $router->resource('users', UsersController::class);
    $router->resource('img', ImgController::class);
    $router->resource('voice', VoiceController::class);
    $router->resource('source', SourceController::class);
    $router->resource('group', GroupController::class);
    $router->resource('label', LabelController::class);
    $router->resource('tag', TagController::class);
    $router->post('upload', 'SourceController@upload');
    $router->post('index', 'SourceController@index');
    $router->post('openId', 'GroupController@openId');
    $router->post('LabelAdd', 'LabelController@LabelAdd');
    $router->post('userLabel', 'UsersController@userLabel');
    $router->post('tag', 'TagController@tag');

});
