<?php

use Illuminate\Http\Request;

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


Route::get('/log', ['uses' => 'ApiLogController@Log']);
Route::post('/log', ['uses' => 'ApiLogController@postLog']);

// Route::get('/createKey', ['uses' => 'ApiLogController@createKey']);


Route::get('/pegawai-adbt/{key}', 'ApiAdbtController@getPegawai');
Route::get('/createKey/adbt', 'ApiAdbtController@createKey');
