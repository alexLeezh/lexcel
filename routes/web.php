<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('index','AuthController@login');
$router->post('login','UserController@login');
$router->post('demo','ExampleController@example');

$router->group(['prefix' => 'admin','middleware'=>'auth:api'], function () use ($router) {
	$router->get('import',[ 'as' => 'admin.import',  'uses'=>'IndexController@import']);
	$router->get('export',[ 'as' => 'admin.export',  'uses'=>'IndexController@export']);
});

$router->group(['prefix' => 'api/v1','middleware'=>'auth:api'], function() use ($router) {
	$router->post('logout','UserController@logout');
  	$router->post('refresh','UserController@refreshToken');
    $router->post('upload','MainController@up');
    $router->get('uplist','MainController@ls');
    $router->get('generate','ReportController@generate');
    $router->delete('del/{id}','MainController@delete');
});
