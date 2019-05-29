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

Route::get('/', 'PagesController@homepage');
Route::get('about', 'PagesController@about');

Route::group(['mileware' => 'web'], function () {
    Route::get('siswa','SiswaController@index');
    Route::get('siswa/create', 'SiswaController@create');
    Route::post('siswa', 'SiswaController@store');
    Route::get('siswa/{siswa}', 'SiswaController@show');
    Route::get('siswa/{siswa}/edit', 'SiswaController@edit');
    Route::patch('siswa/{siswa}', 'SiswaController@update');
    Route::delete('siswa/{siswa}', 'SiswaController@destroy');
});
