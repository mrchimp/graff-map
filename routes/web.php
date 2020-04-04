<?php

use Illuminate\Support\Facades\Route;

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
    return view('home');
});

Route::get('photos', 'PhotoController@index')->name('photos.index');
Route::post('photos', 'PhotoController@store')->name('photos.store');
Route::get('points', 'PointController@index')->name('points.index');
