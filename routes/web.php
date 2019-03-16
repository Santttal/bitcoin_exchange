<?php

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/', 'HomeController@index')->name('dashboard');
Route::put('/change-status', 'OfferController@changeStatus')->name('offers.change-status');
Route::resource('offers', 'OfferController')->except(['index', 'show']);

Route::post('/trades', 'TradeController@store')->name('trades.store');
Route::put('/trades/{id}', 'TradeController@update')->name('trades.update');
