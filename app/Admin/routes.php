<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('/user',PostController::class);
    $router->resource('/maters',MatersShowController::class);
    $router->get('/materialAdd','MatersController@index');
    $router->post('/materialAddDo','MatersController@materialAddDo');
    $router->get('/MenuAdd','MenuController@index');
    $router->post('/menuAddDo','MenuController@menuAddDo');
    $router->resource('/MenuShow',MenuShowController::class);


});
