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
    'prefix' => 'calendar/'
], function() use ($router) {
    $router->get('date', [
        'uses' => 'CalendarController@date',
        'as' => 'calendarDate'
    ]);
});

//---------以下为测试
//$router->get('testController/{a}/{b}', [
//    'uses' => 'ExampleController@test1',
//    'as' => 'testController'
//]);
//
//$router->group([
//    'prefix' => 'accounts/{accountId}',
//    'middleware' => 'test1:{accountId}'
//], function () use ($router) {
//    $router->get('detail', function ($accountId) {
//        // Matches The "/accounts/{accountId}/detail" URL
//        //echo route();
////        var_dump(App\Http\Middleware\ExampleMiddleware::class);
//    });
//});
//
//$router->get('user[{name}]', function ($name = null) {
//    return $name;
//});
//
//$router->get('param/{a}', [
//    'as' => 'param',
//    function ($c) {
////    var_dump($_ENV, app()->environment());
//    echo route('param', ['c' => 1]);
//}]);
//
//$router->group([
//    'namespace' => 'test2',
//    'prefix' => 'test2'
//], function() use ($router) {
//    $router->get('t', function () {
//        echo 1;
//    });
//    $router->get('s', function () {
//        echo 2;
//    });
//});
//
//$router->get('test', function () use ($router) {
//    echo '2';
//});
//
//$router->get('profile', [
//    'as' => 'profile', 'uses' => 'Controller@test'
//]);
//
//$router->get('/', function () use ($router) {
//    return $router->app->version();
//});
