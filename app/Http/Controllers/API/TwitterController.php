<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Twitter;
use File;
use Validator;

class TwitterController extends Controller {
    public $allTweets = null; 
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function twitterUserTimeLine(Request $request) {
        $input = $request->all();
        $screenName = $input['screen_name'];
        $fromDate =  strtotime($input['from_date']);
        $toDate = strtotime($input['to_date']);
        $searchText = trim($input['search_query']);
        $count = 200;
        //we need all tweets from this particualr date range so lets start our loop
        $tweetsData[] = $this->_createTweetsData($screenName, $count, $fromDate, $toDate, $searchText); 
        if(isset($this->allTweets)){
            $return['totalTweet'] = (isset($this->allTweets['all'])) ? count($this->allTweets['all']) : 0;
            $return['matchedTweet'] = (isset($this->allTweets['matchedSearch'])) ? count($this->allTweets['matchedSearch']) : 0;
            $return['matchedTweetsData'] = (isset($this->allTweets['matchedSearch'])) ? $this->allTweets['matchedSearch'] : 0; 
            //$return['totalTweetData'] = (isset($this->allTweets['all'])) ? $this->allTweets['all'] : 0;

            $return['from_date'] = date('d-m-Y', $fromDate);
            $return['to_date'] = date('d-m-Y',$toDate);
            $return['search_query'] = $searchText;

            return response()->json(['success' => $return], \Config::get('constants.status.success')); 
        } 
        }

    /**
     * function to get and create tweets data
     * @param string $screenName twitter user name
     * @param int $count total tweets count default 200
     * @param date $fromDate date from which we need tweets
     * @param date $toDate date till we need tweets
     */
    public function _createTweetsData($screenName, $count = 200, $fromDate, $toDate, $searchText = null, $prevTweets = array(),$prevMatchedSearch=array(), $max_id = 0) {
        
        $tweets['all'] = isset($prevTweets) ? $prevTweets : array();
        $searchArray = array();
        $finalData = null; 
        
        $tweets['matchedSearch'] = isset($prevMatchedSearch) ? $prevMatchedSearch : array();
        
        if (isset($max_id) && (int) $max_id > 0) {
            $tweetData = Twitter::getUserTimeline(['screen_name' => $screenName, 'count' => $count, 'max_id' => $max_id]);
        } else {
            $tweetData = Twitter::getUserTimeline(['screen_name' => $screenName, 'count' => $count]);
        }
        if (!empty($searchText)) {
            $searchArray = explode(",", $searchText);
        }
        foreach ($tweetData as $data) {
            $created_at =  strtotime($data->created_at);
            if (($created_at >= $fromDate) && ($created_at <= $toDate)) {
                $tweets['all'][] = $data;
                //check for the search
                if (count($searchArray) > 0) {
                    foreach ($searchArray as $search) {
                        if (strpos(strtolower($data->text), trim(strtolower($search))) !== FALSE) { 
                            $tweets['matchedSearch'][] = $data;
                        }
                    }
                }
            } else { 
                $tweets['outTweets'][] = $data;
            }
        } 
        
        if(isset($tweets['outTweets']) && count($tweets['outTweets']) > 0 ){
            $lastCreatedAt =  strtotime((last($tweets['outTweets'])->created_at));
            $lastId = (last($tweets['outTweets'])->id);
        }
        else{
            $lastCreatedAt = null;
            $lastId = 0; 
        }
        if (($lastCreatedAt >= $toDate) && (int) $lastId > 0) {  
                $this->_createTweetsData($screenName, $count, $fromDate, $toDate, $searchText, $tweets['all'],$tweets['matchedSearch'], $lastId); 
        }else{  
            $this->allTweets = $tweets;
        } 
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function searchTweets(Request $request) {

        $validator = Validator::make($request->all(), [
                    'query' => 'required',
                    'until' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], \Config::get('constants.status.unauthorized'));
        }

        $input = $request->all();
        $query = $input['query'];

        $count = isset($input['count']) ? $input['count'] : 100;
        $until = $input['until'];
        $data = Twitter::getSearch(['q' => $query, 'count' => $count, 'until' => $until]);
        return response()->json(['success' => $data], \Config::get('constants.status.success'));
    }

    /**
     * Destroys the status specified by the required ID parameter. 
     * The authenticating user must be the author of the specified status. Returns the destroyed status if successful.
     *
     * Parameters :
     * - trim_user (0|1)
     * @return Returns the destroyed status if successful
     */
    public function deleteTweet(Request $request) {

//        $validator = Validator::make($request->all(), [
//            'id' => 'required',
//        ]);
//
//        if ($validator->fails()) {
//
//            return response()->json(['error'=>$validator->errors()], \Config::get('constants.status.unauthorized'));
//
//        }

        $input = $request->all();
        $postIds = $input['post_id'];
        $parameters = array('trim_user' => false);

        foreach ($postIds as $id) {
            $response[] = Twitter::destroyTweet(trim($id), $parameters);
        }

//        $parameters = array('trim_user' => $input['trim_user']);

        return response()->json(['success' => $response], \Config::get('constants.status.success'));
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function tweet(Request $request) {
        $this->validate($request, [
            'tweet' => 'required'
        ]);


        $newTwitte = ['status' => $request->tweet];


        if (!empty($request->images)) {
            foreach ($request->images as $key => $value) {
                $uploaded_media = Twitter::uploadMedia(['media' => File::get($value->getRealPath())]);
                if (!empty($uploaded_media)) {
                    $newTwitte['media_ids'][$uploaded_media->media_id_string] = $uploaded_media->media_id_string;
                }
            }
        }


        $twitter = Twitter::postTweet($newTwitte);


        return back();
    }
    
     /**
     * Create a new tweet on a tweeter account.
     *
     * @return void
     */
    public function addTweet(Request $request) { 
        $input = $request->all();
        $tweet = $input['tweet']; 
        $twitterResponse = Twitter::postTweet(array('status' => "$tweet", 'format' => 'json')); 
        return response()->json(['success' => $twitterResponse], \Config::get('constants.status.success'));
    }

}
