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

Route::get('schedule-post', 'API\SchedulePostController@index');
Route::get('twitterUserTimeLine', 'API\TwitterController@twitterUserTimeLine');

Route::post('tweet', ['as'=>'post.tweet','uses'=>'API\TwitterController@tweet']);
