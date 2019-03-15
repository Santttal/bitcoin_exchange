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
