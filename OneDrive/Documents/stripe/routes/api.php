<?php

use App\Http\Controllers\StripeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('payment/intent', 'StripeController@create');
Route::post('payment/customer/{id}', 'StripeController@confirm');
