<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SchedulePost;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use Validator;
use Helper;

class SchedulePostController extends Controller {

    public function index() {

        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM2MGQ3NmE1ZGM2NjU4ZDM5MGQ0ODYyMmUzYjVkYmVjOGQ3NjIzNDQ2YTg0OTc4YjY1ZWUxM2Q3MjEzZWZmODQ4YmViNzlhMDk0ZGI2ZDVlIn0.eyJhdWQiOiIxIiwianRpIjoiMzYwZDc2YTVkYzY2NThkMzkwZDQ4NjIyZTNiNWRiZWM4ZDc2MjM0NDZhODQ5NzhiNjVlZTEzZDcyMTNlZmY4NDhiZWI3OWEwOTRkYjZkNWUiLCJpYXQiOjE1MTk4MzQ3ODMsIm5iZiI6MTUxOTgzNDc4MywiZXhwIjoxNTUxMzcwNzgzLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.TpdE-ZqT3vWXX8QxQzFp6a2eLTE0HZXJPXYkpBRN3UEoErAW9hiLM6I_EMM-U5kc7GBmDsnK4Myif9H_wf8-JYvIC3WWYR_BJws-06kXUbS1w7HifWH1NWg9fzl7Cow4GGtaijR0XDf94oX5MhGFpUGnr99ZaHLkFPVdrQbV-EJ0iILPN94ZWwdxNhiuMQgU7kjSztu5PDyvw216IzVHxUd1X4wa-j2LOUnblAt05pf2b_L3Gmbk2kBeJp0I0mHf0pzRBOm1jdqtRpRUAqLJvnL6EL7SMgc3aGnb8XsmwMbKDKUZ340oOwuOB97Vt2vuSx_Gcjhq8TLTWChn7UClIKP4FGU1UJFMA5VRsuuxJJ8v6RO4BKFjRrAfUnLYtdr6gkhgCdSJL7MidO5oYDsVujMN1rVhrRFV_IsovRhJjXc0ZnptmqMZM9wCPSQtYBUMvUBh9vCMKEXKF0HAVwb1E8GKQB_mcd1B0ArQrgur8vidBbpK14YQfWJfQu6wb2Ylw5m2X629LuUsbyE2Ro1hxdptt6EaGXtECN0cS468GbLOKtToy-uLWGSWLpmoXh-VCwMwIKDTtREp2wDHe1F-tFSTx9xIQCWNmRPItEqc5DACzqu-Sghchihyt_naTa0t8LbxeYUOqwvOR5Xs7tiGbHEW-zQsRbHPswpS40O0o1A';
        $client = new \GuzzleHttp\Client();
//        $apiUrl = url("api/get-details"); 
        $apiUrl = url("api/schedule-post");
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $body['accountType'] = 'facebook';
        $body['postData'] = '{"message":"this is my test tweet","description":"","link":"","picture":"","title":"this is my test tweet"}';
        $body['scheduleDate'] = '08-03-2018T22:54:57';
        $body['authToken'] = 'EAAFOLVFAu1MBANF95B8GX1bg5GZCW8irtRLA4ZBn9eChhp18YPMZCPbcJszaTmywZBpxHHPjRCUURn9zMBSKcOAnYSNp9qt2ZBI2IT5BbmKTmOpL3WeSpFA97h45ykAXYWnakSJ8E7XwW0wvS1oMVYvrt1cH8ULTbpD8SDbhS3nYkKWeIarCi';

//        $body = $client->request("GET", $apiUrl, ['headers' => $headers])->getBody();
        $body = $client->request("POST", $apiUrl, ['headers' => $headers, 'body' => json_encode($body)])->getBody();

        $contents = (string) $body;
        $data = json_decode($contents);
        dd($data);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function schedulePost(Request $request) {

        $validator = Validator::make($request->all(), [
                    'accountType' => 'required',
                    'authToken' => 'required',
                    'postData' => 'required',
                    'scheduleDate' => 'required'
        ]);

        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], \Config::get('constants.status.unauthorized'));
        }

        $input = $request->all();
        $authorization = $request->header('Authorization');
        $authSecret = (!empty($input['authSecret'])) ? $input['authSecret'] : '';
        $userId = Auth::user()->id;
        $addPost = SchedulePost::create(
                        array(
                            'user_id' => $userId,
                            'authorization' => trim($authorization),
                            'account_type' => trim(strtolower($input['accountType'])),
                            'auth_token' => trim($input['authToken']),
                            'auth_secret' => trim($authSecret),
                            'post_data' => json_encode($input['postData']),
                            'schedule_date' => date('Y-m-d H:i:s', strtotime($input['scheduleDate'])),
        ));

        if ($addPost) {
            return response()->json(['success' => 'Post saved successfully.'], \Config::get('constants.status.success'));
        } else {
            return response()->json(['error' => 'Some error occur please try again'], \Config::get('constants.status.unauthorized'));
        }
    }

    /*     * *
     * Function to get allschedule post via user access token
     */

    public function listSchedulePost() {
        $userId = Auth::user()->id;
        $db = new SchedulePost();
        $schedulePost = $db->getPostByUser($userId);
        if ((!empty($schedulePost))) {
            foreach ($schedulePost as $key => $data) {
                switch ($data->status) {
                    case (1):
                        $status = 'Pending';
                        break;
                    case (2):
                        $status = 'Done';
                        break;
                    case (3):
                        $status = 'Inactive';
                        break;
                }

                $returnData[$key] = $data;
                $returnData[$key]->status = $status;
                $returnData[$key]->post_data = json_decode($data->post_data);
            }
            return response()->json(['success' => $returnData], \Config::get('constants.status.success'));
        } else {
            return response()->json(['error' => 'No post avaliable, Please try again'], \Config::get('constants.status.unauthorized'));
        }
    }

    /*
     * Function to get allschedule post via user access token
     */

    public function changeStatus(Request $request) {

        $validator = Validator::make($request->all(), [
                    'postIds' => 'required',
                    'status' => 'required'
        ]);
        $success = false;
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], \Config::get('constants.status.unauthorized'));
        }
        $db = new SchedulePost();
        $input = $request->all();
        $sPostIds = (!empty($input['postIds'])) ? explode(",", $input['postIds']) : array();
        $userId = Auth::user()->id;
        foreach ($sPostIds as $sPostId) {
            if (strtolower($input['status']) == 'inactive') {
                $changeStatus = $db->updatePostByID($sPostId, $userId, array('status' => 3, 'updated_at' => now()));
                $message[] = "Post status updated successfully for id $sPostId";

                if ($changeStatus > 0)
                    $success = true;
            }
            if (strtolower($input['status']) == 'pending') {
                $changeStatus = $db->updatePostByID($sPostId, $userId, array('status' => 1, 'updated_at' => now()));
                $message[] = "Post status updated successfully for id $sPostId";
                if ($changeStatus > 0)
                    $success = true;
            } elseif (strtolower($input['status']) == 'delete') {
                $changeStatus = $db->deletePostByID($sPostId, $userId);
                $message[] = "Post deleted successfully for id $sPostId";

                if ($changeStatus > 0)
                    $success = true;
            }
        }
        if ($success === true) {
            return response()->json(['success' => $message], \Config::get('constants.status.success'));
        } else {
            return response()->json(['error' => 'No post avaliable, Please try again'], \Config::get('constants.status.unauthorized'));
        }
    }

    /*
     * Function to get allschedule post via user access token
     */

    public function updatePost(Request $request) {

        $validator = Validator::make($request->all(), [
                    'postId' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], \Config::get('constants.status.unauthorized'));
        }
        $db = new SchedulePost();
        $input = $request->all();
        $sPostId = (int) $input['postId'];
        $userId = Auth::user()->id;

        if (isset($input['status'])) {
            $update['status'] = (strtolower($input['status']) == 'inactive') ? 3 : 1;
        }
        if (isset($input['postData'])) {
            $update['post_data'] = json_encode($input['postData']);
        }
        if (isset($input['accountType'])) {
            $update['account_type'] = "'" . $input['accountType'] . "'";
        }
        if (isset($input['authToken'])) {
            $update['auth_token'] = "'" . $input['authToken'] . "'";
        }
         if (isset($input['authSecret'])) {
            $update['auth_secret'] = "'" . $input['authSecret'] . "'";
        }
        if (isset($input['scheduleDate'])) {
            $update['schedule_date'] = date('Y-m-d H:i:s', strtotime($input['scheduleDate']));
        }
        $update['updated_at'] = now();
        unset($update['postId']);
        $changeStatus = $db->updatePostByID($sPostId, $userId, $update);
        $message = "Post updated successfully";


        if ((int) $changeStatus > 0) {
            return response()->json(['success' => $message], \Config::get('constants.status.success'));
        } else {
            return response()->json(['error' => 'No post avaliable, Please try again'], \Config::get('constants.status.unauthorized'));
        }
    }

}
