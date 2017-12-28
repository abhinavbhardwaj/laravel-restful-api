<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Twitter;
use File;
use Validator;

class TwitterController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function twitterUserTimeLine(Request $request) {
        $input = $request->all();
        $screenName = $input['screen_name'];
        $fromDate = date('d-m-Y h:i:s a', strtotime($input['from_date']));
        $toDate = date('d-m-Y h:i:s a', strtotime($input['to_date']));
        $searchText = trim($input['search_text']);
        $count = 200;
        //we need all tweets from this particualr date range so lets start our loop
        $tweets = $this->_createTweetsData($screenName, $count, $fromDate, $toDate, $searchText);

	$return['total_tweets'] = count($tweets['all']);
	$return['total_matched_tweets'] = count($tweets['matchedSearch']);
	$return['matched_tweets'] = $tweets['matchedSearch'];
	$return['matched_tweets_data'] = count($tweetsss);
        return response()->json(['success' => $return], \Config::get('constants.status.success'));
    }

    /**
     * function to get and create tweets data
     * @param string $screenName twitter user name
     * @param int $count total tweets count default 200
     * @param date $fromDate date from which we need tweets
     * @param date $toDate date till we need tweets
     */
    private function _createTweetsData($screenName, $count = 200, $fromDate, $toDate, $searchText=null, $prevTweets = array(), $max_id = 0) {

        $tweets  = isset($prevTweets) ? $prevTweets : array();
        $searchArray  =  array();
        $matchedSearch  =  array();
        if (isset($max_id) && (int) $max_id > 0) {
            $tweetData = Twitter::getUserTimeline(['screen_name' => $screenName, 'count' => $count, 'max_id' => $max_id]);

        } else {
            $tweetData = Twitter::getUserTimeline(['screen_name' => $screenName, 'count' => $count]);
        }
	if(!empty($searchText)){
	    $searchArray = explode(",", $searchText);
	}


        foreach ($tweetData as $data) {
            $created_at = date('d-m-Y h:i:s a', strtotime($data->created_at));
            if (($created_at >= $fromDate) && ($created_at <= $toDate)) {
                $tweets['all'][] = $data;
		//check for the search
		if(count($searchArray) > 0){
		    foreach($searchArray as $search){
			if (strpos($data->text, $search) !== FALSE) {
			    $tweets['matchedSearch'][] = $data;
			}
		    }
		}

            }
            else{
                $tweets['Outtweets'][] = $data;
            }
        }
        $lastCreatedAt = date('d-m-Y h:i:s a', strtotime((last($tweets['Outtweets'])->created_at)));
        $lastId = (last($tweets['Outtweets'])->id);
        if(($lastCreatedAt >= $toDate) && (int) $lastId > 0 ){
            $this->_createTweetsData($screenName, $count, $fromDate, $toDate, $tweets['all'], $lastId);
        }else{
            return $tweets ;
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

}
