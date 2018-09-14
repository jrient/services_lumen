<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function() {
    return 'hello world';
});

$router->group([
    'prefix' => 'calendar/'
], function() use ($router) {
    $router->get('date/{date}', [
        'uses' => 'CalendarController@date'
    ]);
});

$router->group([
    'prefix' => 'wx/'
], function() use ($router) {
    $router->get('index', [
        'uses' => 'WxController@index'
    ]);
    $router->post('index', [
        'uses' => 'WxController@message'
    ]);
});
