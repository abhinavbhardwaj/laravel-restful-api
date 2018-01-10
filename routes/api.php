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
	
 /** twitter specific routes **/
Route::post('twitter-user-timeline', ['uses'=>'API\TwitterController@twitterUserTimeLine','middleware' => 'twitter-wrapper']);
    Route::post('get-twetter-feed', ['uses'=>'API\TwitterController@searchTweets','middleware' => 'twitter-wrapper']);
    Route::post('delete-tweet', ['uses'=>'API\TwitterController@deleteTweet','middleware' => 'twitter-wrapper']);

 /** Face book specific routes **/
    Route::post('/facebook/search-feeds', ['uses'=>'API\FacebookController@searchUserFeeds']);  
    Route::get('/callback/facebook', ['uses'=>'API\FacebookController@callBack']);  
    
});
