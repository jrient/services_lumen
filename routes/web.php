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


$router->group([
    'namespace' => 'test2',
    'prefix' => 'test2'
], function() use ($router) {
    $router->get('t', function () {
        echo 1;
    });
    $router->get('s', function () {
        echo 2;
    });
});

$router->get('test', function () use ($router) {
    echo '2';
});

$router->get('profile', [
    'as' => 'profile', 'uses' => 'Controller@test'
]);

$router->get('/', function () use ($router) {
    return $router->app->version();
});
