<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'VueController@index');

Auth::routes();
Route::post('password/reset/verify/email', 'PasswordResetController@reset')->name('password.reset.verify.email');