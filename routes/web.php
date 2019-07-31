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

Auth::routes(['verify' => true]);
Route::get('/', 'HomeController@index');
Route::get('/login', 'HomeController@login')->name('login');
Route::get('/password/reset', 'HomeController@passwordReset')->name('password.request');
Route::get('/home', 'HomeController@home')->name('home')->middleware('verified');
