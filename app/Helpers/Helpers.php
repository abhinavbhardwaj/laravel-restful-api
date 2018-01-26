<?php

namespace App\Helpers;

class Helpers {

    /**
     * Function to print string/array in a readable manner
     * @param string $string
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public static function prd($string, $noDie = false) {
        echo "<pre>";
        print_r($string);
        echo "</pre>";

        if ($noDie === false)
            die;
    }
    
    /**
     * Method to get image from bese64 encoded data and save it
     * @param type $encodedImage
     * @param string $path path where we store all images
     * @author Abhinav Bhardwaj <abhinav.bhardwaj@engineer.com>
     */
    public static function uploadImage($encodedImage) {
        
        $path = env("UPLOAD_IMAGE_DIR", ""); 
        $data = trim($encodedImage);
        $data = str_replace('data:image/png;base64,', '', $data);
        $data = str_replace(' ', '+', $data);
        $data = base64_decode($data);
        
        $imageName = uniqid() . '.png';
        $file = public_path() . '/' . $path.'/'. $imageName;  
        $success = file_put_contents($file, $data);
        
        if($success){
            return $path.'/'.$imageName;
        }
        else {
            return null;
        } 
    }

}
