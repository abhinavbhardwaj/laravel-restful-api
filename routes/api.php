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
Route::post('login', 'API\UserController@login');

Route::post('register', 'API\UserController@register');


Route::group(['middleware' => 'auth:api'], function(){

	Route::get('get-details', 'API\UserController@details');
    Route::get('get-user-account', 'API\AccountController@index');
    Route::post('schedule-post', 'API\SchedulePostController@schedulePost');
    Route::get('list-schedule-post', 'API\SchedulePostController@listSchedulePost');
    Route::post('/schedule/change-status', 'API\SchedulePostController@changeStatus');
	
 /** twitter specific routes **/
	Route::post('twitter-user-timeline', ['uses'=>'API\TwitterController@twitterUserTimeLine','middleware' => 'twitter-wrapper']);
    Route::post('get-twetter-feed', ['uses'=>'API\TwitterController@searchTweets','middleware' => 'twitter-wrapper']);
    Route::post('delete-tweet', ['uses'=>'API\TwitterController@deleteTweet','middleware' => 'twitter-wrapper']);
	Route::post('add-tweet', ['uses'=>'API\TwitterController@addTweet','middleware' => 'twitter-wrapper']);
 Route::post('bulk-add-tweet', ['uses'=>'API\TwitterController@bulkAddTweet','middleware' => 'twitter-wrapper']);

 /** Facebook specific routes **/
    Route::post('/facebook/search-feeds', ['uses'=>'API\FacebookController@searchUserFeeds']);
	Route::get('/callback/facebook', ['uses'=>'API\FacebookController@callBack']);
	Route::post('/facebook/delete-post', ['uses'=>'API\FacebookController@deletePost']);
	Route::post('/facebook/add-post', ['uses'=>'API\FacebookController@addPost']);
	Route::post('/facebook/add-story', ['uses'=>'API\FacebookController@addStory']);

});
