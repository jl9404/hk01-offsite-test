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
    return view('payment.form');
})->name('payment.form');
Route::get('/query', function () {
    return view('payment.query');
})->name('payment.query');


Route::group(['middleware' => 'throttle:5,10'], function() {
    Route::post('/payment', 'PaymentController@store')->name('payment.store');
    Route::post('/query', 'PaymentController@query');
});

Route::post('/notify/paypal', 'NotifierController@paypal')->name('notify.paypal');