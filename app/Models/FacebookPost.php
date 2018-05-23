<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FacebookPost extends Model
{
     
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facebook_post';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fb_id', 'title', 'message','picture','user_id',
    ];
    
    /**
     * method to get all post id by user id 
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public function getPostByUser( $userId) {
        $result = array();
         //DB::enableQueryLog();
        $fbPost = DB::table($this->table)
                     ->select(DB::raw('distinct fb_id'))
                     ->where('user_id', '=', $userId)
                     ->get();
                     // dd(DB::getQueryLog()); die;
        $resultArray = $fbPost->toArray();
        foreach($resultArray as $key => $value){
            $result[]  = trim($value->fb_id);
        }
        return $result;
    }
}
