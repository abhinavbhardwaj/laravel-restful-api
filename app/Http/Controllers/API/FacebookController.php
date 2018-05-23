<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Facebook;
use File;
use Validator;
use Helper;
use Illuminate\Support\Facades\Auth;
use App\Models\FacebookPost;

class FacebookController extends Controller { 

    protected $_userAddedPost;
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

        $prevPost = isset($input['prev_ids']) ? $input['prev_ids']: null;
        
        $searchText = trim($input['search_query']);
        try {
            $response = Facebook::get('/me/feed?limit=20000&since=' . $fromDate . '&until=' . $toDate, $accessToken);
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => $e->getMessage()], \Config::get('constants.status.unauthorized'));
        }
        
        $post = $response->getDecodedBody();
        if($prevPost){
            $post = $this->getNewPost($post, $prevPost);
        }
        
        if(count($post['data'])>0){
            $this->_userAddedPost = $this->setUserPost();
            $matchedSearch = $this->getMatchedSearch($post['data'], $searchText);
        }

        $return['totalFeeds'] = (isset($post['data'])) ? count($post['data']) : 0;
        $return['matchedFeeds'] = (isset($matchedSearch)) ? count($matchedSearch) : 0;
        $return['matchedFeedsData'] = (isset($matchedSearch)) ? $matchedSearch : 0; 
        //$return['totalFeedsData'] = (isset($post)) ? $post : 0;

        $return['from_date'] = $receiveFormDate;
        $return['to_date'] = $receiveToDate;
        $return['search_query'] = $searchText;
        return response()->json(['success' => $return], \Config::get('constants.status.success'));
    }
    
    /**
     * Function to exclude given post from the post array
     * @param string $post
     */
    public function getNewPost($postData, $prevPosts) {
        $prevPost = explode(',', $prevPosts);
        $prevPost=array_map('trim',$prevPost);
        foreach($postData['data'] as $key => $value){
           if (!in_array($value['id'], $prevPost)) {
               $return['data'][] = $value;
            } 
        }  
        
        return $return;

    }

    /**
     * Method to check if is there any post added by app
     * 
     * @param array $postData
     * @return array $postData with updated status
     */
    private function setUserPost(){ 
        $fbPost = array();
        $fbDb = new FacebookPost();
        $userId = Auth::user()->id; 
        $fbPost = $fbDb->getPostByUser( $userId);
        //if((!empty($fbPost->fb_ids))){
        //    $fb_ids = explode(',', $fbPost->fb_ids);
        //}
        return $fbPost;
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
                            $data['message'] =  (!empty($data['message']))  ? $data['message'] : $data['story'];
                            unset($data['story']);
                                
                            $data['addedByapp'] = (in_array($data['id'], $this->_userAddedPost)) ? 1 : 0;
                           
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
    public function addStory(Request $request) {   
        $post['name'] = null;
        $post['message'] = null;
        $post['link'] = null;
        $post['description'] = null;
        $imagePath = null;
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
             $imagePath = Helper::uploadImage($input['picture']);
             if(!empty($imagePath)){
                 $imageUrl = url("public/$imagePath");
                 $post['picture'] = $imageUrl;
             }

         }

        try {
            $response = Facebook::post('/me/feed', $post , $accessToken); //current user all post
            $responseBody = json_decode($response->getBody());
            
            $userId = Auth::user()->id;  
            $fbpost = FacebookPost::create(
                            array(
                                'fb_id' => $responseBody->id, 
                                'title' => $post['name'], 
                                'description' => $post['description'], 
                                'link' => $post['link'],                                 
                                'message' => $post['message'], 
                                'picture' => $imagePath, 
                                'user_id'=> $userId
                    ));
            
            return response()->json(['success' => 'The post was submitted successfully to Facebook timeline.'], \Config::get('constants.status.success'));
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            //echo "Some error";echo $e->getMessage(); die;
            return response()->json(['error' => $e->getMessage()], \Config::get('constants.status.unauthorized'));
        }
    }




     /**
     * API to create a callback for the call from client
     * @param Request $request
     */
    public function addPost(Request $request) { 
        
         $input = $request->all();
         $accessToken = $request->header('authToken');
         $imagePath = null;
         
         if(!empty($input['message'])){
            $post['message'] = $input['message'];
         }
         
         if(!empty($input['picture'])){ 
             $imagePath = Helper::uploadImage($input['picture']);
             if(!empty($imagePath)){ 
                $image = public_path() . '/'.$imagePath;
                $post['source'] = Facebook::fileToUpload($image);
             }
              
         }  
         
        try {
            $response = Facebook::post('/me/photos', $post , $accessToken); //current user all post
            $responseBody = json_decode($response->getBody());
            
            $userId = Auth::user()->id;  
            $fbpost = FacebookPost::create(
                            array(
                                'fb_id' => $responseBody->post_id, 
                                'message' => $post['message'], 
                                'picture' => $imagePath, 
                                'user_id'=> $userId
                    ));
            
            return response()->json(['success' => 'The post was submitted successfully to Facebook timeline.'], \Config::get('constants.status.success'));
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => $e->getMessage()], \Config::get('constants.status.unauthorized'));
        }
    }
    
      /**
     * API to create a callback for the call from client
     * @param Request $request
     */
    public function deletePost(Request $request) {
         $input = $request->all();
         $accessToken = $request->header('authToken');
         $postIds = explode(',', $input['postIds']);

         if(count($postIds) == 0){
              return response()->json(['error' => 'No post found'], \Config::get('constants.status.unauthorized'));
         }

         foreach($postIds as $postId):
            try {
                $response = Facebook::delete(trim($postId), array () , $accessToken);
                $return['success'][$postId] = 'Deleted successfully';
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                 $return['error'][$postId] = $e->getMessage();
            }
        endforeach;
        return response()->json(['data' => $return], \Config::get('constants.status.success'));
    }

}