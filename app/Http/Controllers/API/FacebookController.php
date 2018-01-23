<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Facebook;
use File;
use Validator;

class FacebookController extends Controller { 

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function searchUserFeeds(Request $request) {

        $input = $request->all();
        $matchedSearch = array();
        $accessToken = $request->header('authToken');
        
        $receiveFormDate = $input['from_date'];
        $receiveToDate = $input['to_date'];
        
        $fromDate = strtotime($receiveFormDate.' 00:00');
        $toDate = strtotime($receiveToDate.' 23:59');
        
        $searchText = trim($input['search_query']); 
        try {
            $response = Facebook::get('/me/feed?limit=20000&since=' . $fromDate . '&until=' . $toDate, $accessToken); //current user all post
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => 'some error occur'], \Config::get('constants.status.unauthorized'));
        }

        $post = $response->getDecodedBody(); 
        if(count($post['data'])>0){
            $matchedSearch = $this->getMatchedSearch($post['data'], $searchText);
        }
        $return['totalFeeds'] = (isset($post)) ? count($post) : 0;
        $return['matchedFeeds'] = (isset($matchedSearch)) ? count($matchedSearch) : 0;
        $return['matchedFeedsData'] = (isset($matchedSearch)) ? $matchedSearch : 0; 
        //$return['totalFeedsData'] = (isset($post)) ? $post : 0;

        $return['from_date'] = $receiveFormDate;
        $return['to_date'] = $receiveToDate;
        $return['search_query'] = $searchText;
        return response()->json(['success' => $return], \Config::get('constants.status.success'));
    }

    /**
     * method to filter search text from a given array value
     * @param array $feedData all post array 
     * @param string $searchText 
     */
    private function getMatchedSearch($feedData, $searchText) {     
        $searchResult = array();
        $searchArray = explode(",", $searchText);  
        foreach ($feedData as $data) {    
            $message =  (!empty($data['message']))  ? $data['message'] : $data['story']; 
            
            if(empty($message))          
                continue;
             
                //check for the search
                if (count($searchArray) > 0) {
                    
                    foreach ($searchArray as $search) {  
                        
                        if (strpos(strtolower($message), trim(strtolower($search))) !== FALSE) { 
                            $searchResult[] = $data; 
                        }
                    }
                
                }  
        }   
        $searchResult = array_unique($searchResult, SORT_REGULAR); 
        return $searchResult;
    }
    
     /**
     * API to create a callback for the call from client
     * @param Request $request
     */
    public function callBack(Request $request) {
         $input = $request->all();
         $param = "";
         $i = 0;
        foreach($input as $key => $value){
            $con    =   ($i == 0) ? "?" : "&";
            $param .= $con .trim($key)."=".trim($value);
            $i++;
        }
        header('location:WYF://oauth-callback/facebook'.$param);die;

    }
    
     /**
     * API to create a callback for the call from client
     * @param Request $request
     */
    public function addPost(Request $request) {
         
         $input = $request->all();
         $accessToken = $request->header('authToken');
         
         if(!empty($input['message'])){
            $post['message'] = $input['message'];
         }
         if(!empty($input['title'])){
            $post['name'] = $input['title'];
         }
         if(!empty($input['link'])){
            $post['link'] = $input['link'];
         }
         if(!empty($input['description'])){
            $post['description'] = $input['description'];
         }
         if(!empty($input['picture'])){
            $post['picture'] = $input['picture'];
         }
          
        try {
            $response = Facebook::post('/me/feed', $post , $accessToken); //current user all post
            return response()->json(['success' => 'The post was submitted successfully to Facebook timeline.'], \Config::get('constants.status.success'));
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => 'some error occur'], \Config::get('constants.status.unauthorized'));
        }
    }
    
}
