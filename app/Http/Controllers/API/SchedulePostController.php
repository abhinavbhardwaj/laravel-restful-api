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

        $input['authSecret'] = null; //setting defaut value 
        $input = $request->all();  
        $userId = Auth::user()->id;  
        $addPost = SchedulePost::create(
                            array( 
                                'user_id'=> $userId,
                                'account_type'=> trim(strtolower($input['accountType'])),
                                'auth_token'=> trim($input['authToken']),
                                'auth_secret'=> trim($input['authSecret']),
                                'post_data'=> json_encode($input['postData']),
                                'schedule_date'=> date('Y-m-d H:i:s', strtotime($input['scheduleDate'])),
                    )); 
        
        if($addPost){
            return response()->json(['success'=>'The post saved successfully.'], \Config::get('constants.status.success'));
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
}
