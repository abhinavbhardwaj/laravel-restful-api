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
        $fbPost = DB::table($this->table)
                     ->select(DB::raw('group_concat(distinct fb_id) as fb_ids'))
                     ->where('user_id', '=', $userId) 
                     ->get();  
        $resultArray = $fbPost->toArray();  
        return $resultArray[0];
    }
}
