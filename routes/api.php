<?php

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

Route::post('/login', 'Api\AuthController@login')->name('login');
Route::post('/register', 'Api\AuthController@register')->name('register');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/admin/users', 'Api\UserController@allUsers');
    Route::get('/admin/passwords', 'Api\VaultPasswordController@indexAdmin');
    Route::get('/admin/activeusers', 'Api\UserController@currentlyActiveUsers');
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', 'Api\AuthController@logout')->name('logout');
    Route::get('/admin/details', 'Api\UserController@userInformation');
    Route::apiResource('passwords', 'Api\VaultPasswordController');
});
