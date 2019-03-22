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

//登录
Route::get('/login','Login\IndexController@login');
Route::post('/dologin','Login\IndexController@doLogin');
//注册
Route::get('/register','Login\IndexController@register');
Route::post('/doRegister','Login\IndexController@doRegister');

