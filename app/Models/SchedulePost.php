<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SchedulePost extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'schedule_post';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'account_type','auth_token','auth_secret','post_data','schedule_date',
    ];
    
    /**
     * method to get all post id by user id 
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public function getPostByUser( $userId) { 
        $sPost = DB::table($this->table)
                     ->select('*')
                     ->where('user_id', '=', $userId) 
                     ->where('status', '!=', 3) 
                     ->get();  
        $resultArray = $sPost->toArray(); 
        return $resultArray;
    }
    
      /**
     * method to get all post id by user id 
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public function updatePostByID( $userId) { 
        $sPost = DB::table($this->table)
                     ->select('*')
                     ->where('user_id', '=', $userId) 
                     ->get();  
        $resultArray = $sPost->toArray();  
        return $resultArray;
    }
}
