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
    return view('welcome');
});
//Route::get('/token','TestController@getAccessToken');
//Route::get('/token2','TestController@getAccessToken2');
//Route::get('/token3','TestController@getAccessToken3');
//Route::get('/user/info','TestController@userInfo');
//Route::get('/test','TestController@index');
////Route::post('/login','TestController@login');
//Route::post('/user/reg','TestController@reg');
//Route::post('/user/login','TestController@login');
//Route::get('/user/center','TestController@center')->middleware('check.token','user.count');
//
//
//Route::get('/test/hash1','TestController@hash1');
//Route::get('/test/hash2','TestController@hash2');
//Route::get('/test1','TestController@test1');
//Route::get('/test2','TestController@test2');
//Route::get('/goods','TestController@goods');
//Route::post('/encrypt','TestController@encrypt');
//
//Route::get('/test/rsa','TestController@rsaEncrypt');
//Route::get('/test/sign','TestController@sign');
//Route::get('/test/rsaSign','TestController@rsaSign');
//Route::post('/test/rsaPostSign','TestController@rsaPostSign');
//Route::post('/test/aesSign','TestController@aesSign');
//Route::get('/test/header','TestController@header');->middleware('check.api')

Route::post('login','UserController@login')->middleware('check.api');
Route::post('register','UserController@register')->middleware('check.api');
Route::get('index','IndexController@index');