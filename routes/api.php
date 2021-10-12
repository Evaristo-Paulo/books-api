<?php

use Illuminate\Http\Request;
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

Route::prefix('auth')->name('auth.')->middleware('api')->group(function () {
   route::get('/login', 'AuthController@login')->name('login');
   route::post('/register', 'AuthController@register')->name('register');
   route::post('/logout', 'AuthController@logout')->name('logout');
   route::get('/profile', 'AuthController@profile')->name('profile');
   route::post('/refresh', 'AuthController@refresh')->name('refresh');
});

Route::prefix('hotels')->name('hotels.')->middleware('api')->group(function () {
    route::get('/', 'HotelController@list')->name('list');
 });
 
