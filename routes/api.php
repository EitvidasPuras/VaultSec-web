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

Route::post('/login', 'Api\AuthController@login')->middleware('throttle:7,1')->name('login');
Route::post('/register', 'Api\AuthController@register')->middleware('throttle:7,1')->name('register');

/*
 * Admin subsystem will only be available on the web, not on the android app
 * */
Route::group(['middleware' => ['auth:api', 'is_admin']], function () {
    Route::get('/admin/users', 'Api\UserController@allUsers');
    Route::get('/admin/passwords', 'Api\VaultPasswordController@indexAdmin');
    Route::get('/admin/notes', 'Api\VaultNoteController@indexAdmin');
    Route::get('/admin/active', 'Api\UserController@currentlyActiveUsers');
    // ------------ For test purposes and future features -----------
    Route::get('/admin/random', 'Api\UserController@getRandom');
    // --------------------------------------------------------------
});

Route::group(['middleware' => 'auth:api', 'throttle:30,1'], function () {
    Route::post('/logout', 'Api\AuthController@logout')->name('logout');
    Route::get('/user', 'Api\UserController@userInformation');

    /* ----- PASSWORDS ----- */
    Route::delete('passwords/delete', 'Api\VaultPasswordController@destroy')->name('passwords.destroy');
    Route::post('passwords/recover', 'Api\VaultPasswordController@restoreDeleted')->name('passwords.recover');
    Route::post('passwords/singular', 'Api\VaultPasswordController@storeSingle')->name('passwords.store.singular');
    Route::apiResource('passwords', 'Api\VaultPasswordController', [
        'except' => ['destroy', 'show']
    ]);

    /* ----- PASSWORDS ----- */
    /* ----- NOTES ----- */
    Route::delete('notes/delete', 'Api\VaultNoteController@destroy')->name('notes.destroy');
    Route::post('notes/recover', 'Api\VaultNoteController@restoreDeleted')->name('notes.recover');
    Route::post('notes/singular', 'Api\VaultNoteController@storeSingle')->name('notes.store.singular');
    Route::apiResource('notes', 'Api\VaultNoteController', [
        'except' => ['destroy', 'show']
    ]);
    /* ----- NOTES ----- */
    /* ----- CARDS ----- */
    Route::delete('cards/delete', 'Api\VaultPaymentCardController@destroy')->name('cards.destroy');
    Route::post('cards/recover', 'Api\VaultPaymentCardController@restoreDeleted')->name('cards.recover');
    Route::post('cards/singular', 'Api\VaultPaymentCardController@storeSingle')->name('cards.store.singular');
    Route::apiResource('cards', 'Api\VaultPaymentCardController', [
        'except' => ['destroy', 'show']
    ]);
    /* ----- CARDS ----- */

    Route::get('the_vault', 'Api\VaultController@index');
});
