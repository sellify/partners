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

Route::get('/', function () {
    $earning = \App\Earning::with('shop.user:id,commission')
                           ->whereHas('shop', function ($query) {
                               return $query->whereNotNull('shops.user_id');
                           })
                                                            //->whereDoesntHave('commissions')
    ->get()->toArray();
    /*$earning = \App\Earning::join('shops', 'shops.id', '=', 'earnings.shop_id')
        ->where('shops.app_id', \DB::raw('earnings.app_id'))
                           ->whereNotNull('shops.user_id')
        ->select('shops.user_id', 'earnings.id', 'earnings.amount')->get();*/
    pr($earning);

    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::get('/home', 'HomeController@index')->name('home');
