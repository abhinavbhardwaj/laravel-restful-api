<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
 
use App\Models\SchedulePost;
use Illuminate\Support\Facades\Log;
use \GuzzleHttp\Client as client; 

class CustomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedulePost:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will publish schedule Post';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $db = new SchedulePost(); 
        $response = false;
        $schedulePost = $db->getPostByUser(0, 'current_time', array(1));
        $client = new Client();
        foreach ($schedulePost as $postKey => $postData){
             
            if($postData->account_type=='twitter'){
               $response = $this->processTwitterPost($client, $postData);
            }
            elseif($postData->account_type=='facebook'){
               $response =  $this->processFaceBookPost($client, $postData);
            } 
            if($response){
                $changeStatus = $db->updatePostByID($postData->id, $postData->user_id, array('status' => 2, 'updated_at'=> now()));
            }
        } 
    }

    /**
     * Method to post twittes on twitter
     * @param array $postData
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public function processTwitterPost($client, $postData) {
        $apiUrl = "http://api.alphaklick.com/api/add-tweet";
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $postData->authorization; 
        $headers['authToken'] = $postData->auth_token; 
        $headers['authSecret'] = $postData->auth_secret; 
        
        $body  = $postData->post_data;
        
//        $body = $client->request("GET", $apiUrl, ['headers' => $headers])->getBody();
        try{
        $body = $client->request("POST", $apiUrl, ['headers' => $headers, 'body'=> $body])->getBody(); 
//        $contents = (string) $body;
//        $data = json_decode($contents);  prd($data);
            return true;
        }
        catch (\Exception $e){ 
            \Log::info('\n\n\r Error in process Twitter Post for : '.$postData->id);
            \Log::info($e->getMessage());
            return false;
        }  
    }
    
    /**
     * Method to post Facebook on twitter
     * @param array $postData
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public function processFaceBookPost($client, $postData) { 
        $apiUrl = "http://api.alphaklick.com/api/facebook/add-story"; 
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $postData->authorization; 
        $headers['authToken'] = $postData->auth_token;  
        $body  = $postData->post_data;
        try{
        $body = $client->request("POST", $apiUrl, ['headers' => $headers, 'body'=> $body])->getBody(); 
        $contents = (string) $body;
       // $data = json_decode($contents);prd($data);
            return true;
        }
        catch (\Exception $e){ 
            \Log::info('\n\n\r Error in process FaceBook Post for : '.$postData->id);
            \Log::info($e->getMessage()); 
            return false;
        }  
    }
}