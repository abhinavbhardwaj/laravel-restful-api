<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SchedulePost;
use Illuminate\Support\Facades\Auth;
use Validator; 
use Helper;

class SchedulePostController extends Controller
{
    
    public function index() {
        echo 'Ye test hai';
        die;
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    public function schedulePost(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'accountType' => 'required', 
            'authToken' => 'required', 
            'postData' => 'required', 
            'scheduleDate' => 'required'

        ]);


        if ($validator->fails()) {

            return response()->json(['error'=>$validator->errors()], \Config::get('constants.status.unauthorized'));            

        }

        $input = $request->all();
        $authSecret  = (!empty($input['authSecret']))  ? $input['authSecret'] : '';
        $userId = Auth::user()->id;  
        $addPost = SchedulePost::create(
                            array( 
                                'user_id'=> $userId,
                                'account_type'=> trim(strtolower($input['accountType'])),
                                'auth_token'=> trim($input['authToken']),
                                'auth_secret'=> trim($authSecret),
                                'post_data'=> json_encode($input['postData']),
                                'schedule_date'=> date('Y-m-d H:i:s', strtotime($input['scheduleDate'])),
                    )); 
        
        if($addPost){
            return response()->json(['success'=>'Post saved successfully.'], \Config::get('constants.status.success'));
        }
        else{
            return response()->json(['error' => 'Some error occur please try again'], \Config::get('constants.status.unauthorized'));
        }
    }
    

    /***
     * Function to get allschedule post via user access token
     */
    public function listSchedulePost(){
        $userId = Auth::user()->id;
        $db = new SchedulePost();
        $schedulePost = $db->getPostByUser( $userId);
        if((!empty($schedulePost))){
            foreach($schedulePost as $key => $data){
                switch ($data->status){
                    case (1):
                     $status  = 'Pending';
                    break;
                    case (2):
                     $status  = 'Done';
                    break;
                    case (3):
                     $status  = 'Inactive';
                    break;

                }

                $returnData[$key] = $data;
                $returnData[$key]->status = $status;
                $returnData[$key]->post_data = json_decode($data->post_data);
            }
            return response()->json(['success' => $returnData], \Config::get('constants.status.success'));
        }
        else{
            return response()->json(['error' => 'No post avaliable, Please try again'], \Config::get('constants.status.unauthorized'));
        }
    }

    /***
     * Function to get allschedule post via user access token
     */
    public function changeStatus(Request $request){

        $validator = Validator::make($request->all(), [ 
            'postId' => 'required|integer', 
            'status' => 'required' 
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], \Config::get('constants.status.unauthorized'));  
        }
        $db = new SchedulePost();
        $input = $request->all();  
        $sPostId = (int)$input['postId']; 
        $userId = Auth::user()->id;  
        
        if(strtolower($input['status']) == 'inactive'){  
            $changeStatus = $db->updatePostByID( $sPostId, $userId, array('status'=>3));
            $message = "Post status updated successfully";            
        }
        if(strtolower($input['status']) == 'pending'){  
            $changeStatus = $db->updatePostByID( $sPostId, $userId, array('status'=>1));
            $message = "Post status updated successfully";            
        }
        elseif(strtolower($input['status']) == 'delete'){
            $changeStatus = $db->deletePostByID($sPostId, $userId);
            $message = "Post deleted successfully";
        } 
        
        if((int)$changeStatus > 0){  
            return response()->json(['success' => $message], \Config::get('constants.status.success'));
        }
        else{
            return response()->json(['error' => 'No post avaliable, Please try again'], \Config::get('constants.status.unauthorized'));
        } 
    }
}
