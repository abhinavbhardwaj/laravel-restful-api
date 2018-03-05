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
        $file = public_path() . '/' . $path . '/' . $imageName;
        $success = file_put_contents($file, $data);

        if ($success) {
            return $path . '/' . $imageName;
        } else {
            return null;
        }
    }

    /*
     * Function to get feed from a specific rss
     * 
     */

     public static function getFeed($url, $img, $limit) {
        $rss = simplexml_load_file($url);
        $count = 0;
        $html = array();
        foreach ($rss->channel->item as $item) {
            $count++;
            if ($count > $limit) {
                break;
            }
            if (isset($item)) {
                $html[$count]['link'] = htmlspecialchars($item->link);
                $html[$count]['message'] = (!empty($item->description)) ? htmlspecialchars($item->description) : htmlspecialchars($item->title);
                $html[$count]['picture'] = $img;
            }
        }
        return $html;
    }

     public static function getFreshContent($limit = 3) {
        $html = "";
        $newsSource = array(
            array(
                "title" => "BBC",
                "url" => "http://feeds.bbci.co.uk/news/world/rss.xml",
                "img" => "https://www.kcet.org/sites/kl/files/styles/kl_image_hero/public/primary_media/bbc_world_news/BBCWorldNewslogo_630x233.jpg",
            ),
//            array(
//                "title" => "CNN",
//                "url" => "http://rss.cnn.com/rss/cnn_latest.rss",
//                "img" => "https://cdn.cnn.com/cnnnext/dam/assets/130305171203-cnn-espanol-logp-tease-only-story-top.jpg",
//            ),
            array(
                "title" => "Fox News",
                "url" => "http://feeds.foxnews.com/foxnews/latest",
                "img" => "https://pbs.twimg.com/profile_images/921416145302847493/NCfAvjNy_400x400.jpg",
            )
        );

        foreach ($newsSource as $source) {
            $htmldata[$source["title"]] = self::getFeed($source["url"], $source["img"], $limit);
        }
        return $htmldata;
    }

}
