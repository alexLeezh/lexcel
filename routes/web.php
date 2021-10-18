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
// $router->post('login','UserController@login');
$router->post('demo','ExampleController@example');

$router->group(['middleware' => 'cross'], function () use ($router) {
    $router->post('login', 'UserController@login');
    $router->post('register', 'UserController@register');


});


$router->group(['prefix' => 'admin','middleware'=>['cross']], function () use ($router) {
	$router->get('import',[ 'as' => 'admin.import',  'uses'=>'IndexController@import']);
	$router->get('export',[ 'as' => 'admin.export',  'uses'=>'IndexController@export']);
});

$router->group(['prefix' => 'api/v1','middleware'=>['auth:api','cross']], function() use ($router) {
	$router->post('logout','UserController@logout');
  	$router->post('refresh','UserController@refreshToken');
    $router->post('upload','MainController@up');
    $router->get('uplist','MainController@ls');
    $router->get('generate','ReportController@generate');
    $router->get('report','ReportController@ls');
    $router->delete('del/{id}','MainController@delete');
    $router->get('userlist', 'UserController@ls');
    $router->post('modfiy', 'UserController@modfiy');
    $router->post('changepw', 'UserController@changepw');
});

$router->get('resource/{asset}', [
    'as' => 'storage.resource',
    'uses' => 'ResourceController@index',
]);
