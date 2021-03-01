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

//header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');

Route::post('/login', ['before' => 'throttle:5,1', 'uses' => 'Api\AuthController@login'])
    ->name('login');
//Route::post('/login', 'Api\AuthController@login')->name('login');
Route::post('/register', 'Api\AuthController@register')->name('register');

Route::group(['middleware' => ['auth:api', 'is_admin']], function () {
    Route::get('/admin/users', 'Api\UserController@allUsers');
    Route::get('/admin/passwords', 'Api\VaultPasswordController@indexAdmin');
    Route::get('/admin/notes', 'Api\VaultNoteController@indexAdmin');
    Route::get('/admin/active', 'Api\UserController@currentlyActiveUsers');
    Route::get('/admin/files', 'Api\VaultFileController@indexAdmin');
    // ------------ For test purposes and future features -----------
    Route::get('/admin/random', 'Api\UserController@getRandom');
    // --------------------------------------------------------------
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', 'Api\AuthController@logout')->name('logout');
    Route::get('/user', 'Api\UserController@userInformation');
    Route::apiResource('passwords', 'Api\VaultPasswordController');
    Route::delete('notes/delete', 'Api\VaultNoteController@destroy')->name('notes.destroy');
    Route::post('notes/recover', 'Api\VaultNoteController@restoreDeleted')->name('notes.recover');
    Route::apiResource('notes', 'Api\VaultNoteController', [
        'except' => 'destroy'
    ]);
    Route::apiResource('files', 'Api\VaultFileController');
    Route::get('the_vault', 'Api\VaultController@index');
});
