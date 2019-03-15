<?php

use Illuminate\Support\Facades\Redis;

Route::get('/', function () {
//    $data = [
//        'event' => 'UserSignedUp',
//        'data' => [
//            'username' => 'Andrey'
//        ]
//    ];
//    Redis::publish('test-channel', json_encode($data));

//    return 'Done';
    return view('welcome');
});

Auth::routes();

Route::get('/', 'HomeController@index')->name('dashboard');
Route::get('/add-offer', 'OfferController@create')->name('create-offer');
Route::post('/add-offer', 'OfferController@store')->name('store-offer');
Route::delete('/delete-offer', 'OfferController@delete')->name('delete-offer');
Route::put('/change-status', 'OfferController@changeStatus')->name('change-status-offer');
Route::get('/offer/{id}/edit', 'OfferController@edit')->name('edit-offer');
Route::put('/offer/{id}', 'OfferController@update')->name('update-offer');
