<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('contoh1', 'ContohController@index');

Route::get('contoh/{id}', 'ContohController@show');

Route::post('contoh/save', 'ContohController@store');

Route::post('contoh/{id}/update', 'ContohController@update');

Route::post('contoh/{id}/delete', 'ContohController@destroy');


