<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/register', 'UserController@register');
    Route::post('/login', 'UserController@login');
    Route::post('/logout', 'UserController@logout');
    Route::get('/getUserInfo', 'UserController@userInfo');
    Route::get('/getUsers', 'UserController@getUsers');
    Route::post('/createOrder', 'OrderController@createOrder');
    Route::get('/getUserOrder', 'OrderController@getUserOrder');
});

