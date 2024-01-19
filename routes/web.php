<?php

use Illuminate\Support\Facades\Route;

Route::get('payment/{id}', 'PaymentController@show')->name('payment');
Route::get('payment-method/{user}', 'PaymentController@setup')->name('setup.payment_intent');
Route::post('webhook', 'WebhookController@handleWebhook')->name('webhook');
