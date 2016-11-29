<?php
include dirname(__FILE__).'/app.php';
use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter extends App {

    private $categories = array();
    private $accounts;
    private $connection;
    private $categories_data;
	//private $tweets;

    public function __construct(){
        require_once('../twitter_data.php');
        $this->categories_data = $json;        
    }

    public function saveData($data) {
        print_r($data);
        die();
        $fichero = "../tweets_history.php";
        $tweets_history = file_get_contents($fichero);
        $tweets_history = serialize($data);
        file_put_contents($fichero, $tweets_history);
    }

    public function getData($cat=null) {
        $fichero = "../tweets_history.php";
        $tweets_history = file_get_contents($fichero);
        $tweets_history = unserialize($tweets_history);

        if($cat !== null){
            return $tweets_history[$cat];
        } else {
            return $tweets_history;
        } 
    }

    public function updateTweets(){
        setlocale(LC_TIME,"ES").'<br>';
        date_default_timezone_set('Europe/Madrid');

        $this->connection = new TwitterOAuth(TW_CONSUMER_KEY, TW_CONSUMER_SECRET, TW_ACCESS_TOKEN, TW_ACCESS_TOKEN_SECRET);

        for ($i=0; $i < count($this->categories_data); $i++) { 
        //$i = 4;
            for ($j=0; $j < count($this->categories_data[$i]['accounts']); $j++) { 
                //$j = 4;
                /*if(!empty($_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]])) continue;*/
                $statuses = $this->connection->get("statuses/user_timeline", array(
                    "screen_name"       =>  $this->categories_data[$i]['accounts'][$j],
                    "count"             =>  30,
                    //"trim_user"         =>  true,
                    "exclude_replies"   =>  true,
                    "include_rts"       =>  false
                    )); 

                if(empty($statuses)){
                    echo 'Tweet Error: Unknow tweet'.'<br>';
                    continue;   
                } else if(!empty($statuses->errors)) {
                    echo 'Tweet Brandwatch Error: Twitter Error'.'<br>'; 
                    echo 'account: '. $this->categories_data[$i]['accounts'][$j].'<br>';               
                    echo 'Twitter '. strtolower($statuses->errors[0]->message).'<br>';
                    continue;               
                }
                $k=0;
                foreach ($statuses as $tweet) {
                    $_SESSION["tweet"][$i]['category'] = $this->categories_data[$i]['category'];
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['tw_id'] = $tweet->id;
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['text'] = trim($tweet->text);
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['retweets'] = $tweet->retweet_count;
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['account'] = $this->categories_data[$i]['accounts'][$j];
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['name'] = $tweet->user->name;
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['photo'] = $tweet->user->profile_image_url_https;

                    $time_sc = strtotime($tweet->created_at);                   
                    $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['tw_created'] = strftime('%d %b %Y',$time_sc);

                    if (!empty($tweet->entities->urls)) {
                        $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['url'] = $tweet->entities->urls[0]->expanded_url;
                    }
                    if (!empty($tweet->entities->media)) {
                        $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['media'] = $tweet->entities->media[0]->media_url;
                        $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['media_url'] = null;
                    } else if (!empty($tweet->entities->urls)){
                        $media_url = $this->getImg($tweet->entities->urls[0]->expanded_url);
                        $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['media'] = null;
                        $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['media_url'] = $media_url;
                    }
                    if (!empty($tweet->extended_entities) && !empty($tweet->extended_entities->media) && $tweet->extended_entities->media[0]->type == 'video') {
                        $videos = $tweet->extended_entities->media[0]->video_info->variants;
                        foreach ($videos as $key => $value) {
                            //if($value->content_type == 'video/mp4' && $value->bitrate < '832000') {
                                $_SESSION["tweet"][$i][$this->categories_data[$i]['accounts'][$j]][$k]['video'] = $value->url;
                            //}
                        }
                    }
                    $k++;
                }        
            }
        }

        if(empty($_SESSION["tweet"])) return false;

        $limits = $this->getTrends();

        $tweets_filtered = array();
        if(!empty($limits)) {
            for ($i=0; $i < count($_SESSION["tweet"]); $i++) {  
                foreach ($_SESSION["tweet"][$i] as $key => $value) { 
                    if($key != 'category') {  
                        for ($j=0; $j < count($value); $j++) { 
                            if($value[$j]['retweets'] >= $limits[$i][$key]['limit']){
                                $tweets_filtered[$i][] = $value[$j];
                            }
                        }
                    }else{
                        $tweets_filtered[$i]['category'] = $_SESSION["tweet"][$i][$key];
                    }
                }
            }
        }

        $tweets_filtered = $this->aasort($tweets_filtered);

        if(!empty($tweets_filtered)) {            
            $this->saveData($tweets_filtered); 
            return 'ok'; 
        }   
        return 'ko';     
    }

    private function aasort(&$array, $field="retweets") {
        $clean_array=array();
        foreach ($array as $key => $value) {
            $sorter=array();
            $ret=array();
            reset($array[$key]);
            foreach ($array[$key] as $key2 => $value2) {
                if(isset($value2[$field])){
                    $sorter[$key2] = $value2[$field];
                }
            }

            arsort($sorter);
  
            $i=1;
            foreach ($sorter as $key2 => $value2) {
                if($i>10) break;
                $ret[$key2] = $value2;
                $i++;

            }
            
            foreach ($ret as $key2 => $value2) {
                $clean_array[$key]['category'] = $array[$key]['category'];
                if(isset($array[$key][$key2])) {
                    $clean_array[$key][] = $array[$key][$key2];
                }
            }
        }
        return $clean_array;
    }

    private function getTrends(){
        $tweets = $_SESSION["tweet"];
        $limits = array();
        $max = 0;
        //pr($tweets);
        for ($i=0; $i < count($tweets); $i++) {  
            foreach ($tweets[$i] as $key => $value) { 
                if($key != 'category') {  
                    for ($j=0; $j < count($value); $j++) {  
                        if(!empty($value[$j]['retweets'])){
                            $limits[$i][$key]['limit'][] = $value[$j]['retweets'];
                        }
                    }
                    $media = 0;
                    if(!empty($limits[$i][$key]['limit'])) {                    
                        sort($limits[$i][$key]['limit']);
                        unset($limits[$i][$key]['limit'][count($limits[$i][$key]['limit'])-1]);
                        unset($limits[$i][$key]['limit'][count($limits[$i][$key]['limit'])-1]);
                        unset($limits[$i][$key]['limit'][0]);
                        unset($limits[$i][$key]['limit'][1]);
                        $limit_media = array_values($limits[$i][$key]['limit']);

                        for ($k=0; $k < count($limit_media); $k++) { 
                            $media += $limit_media[$k];
                        }
                        if($media == 0 || count($limit_media) == 0) {
                            $limits[$i][$key]['limit'] = $media;
                        } else {
                            $limits[$i][$key]['limit'] = floor($media / count($limit_media));
                        }
                    } else {
                        $limits[$i][$key]['limit'] = $media;
                    }

                }else{
                    $limits[$i]['category'] = $tweets[$i]['category'];
                }
            }

        }
        return $limits;
    }

    private function getImg($url=null){
        $url_web = '';

        if(empty($url)) return '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $http_data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if(!empty($http_data)){
            if (preg_match("/og:image(.*)/", htmlspecialchars($http_data), $matches)) {
                if (preg_match("/(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?/", $matches[1], $matches2)) {  
                    $url_web = $matches2[0];
                }
            }    
        }
        return $url_web;
    }

    public function getTweets() {
        $cat = file_get_contents("php://input");
		$selection = '';
        if(empty($cat)) {
			$selection = 19;
		} else {
			$cat_array = explode('=',$cat);
			if(isset($cat_array[1])) $selection = $cat_array[1];
		}

		$data = $this->getData($selection);

        if(empty($data)) {
            return false;
        }

        return json_encode($data);
    }

    public function getCategories() {

        for ($i=0; $i < count($this->categories_data); $i++) { 
            $this->categories[$i]['id'] = $i;
            $this->categories[$i]['name'] = $this->categories_data[$i]['category'];            
        }

        return json_encode($this->categories);
    }

}

$twitter = new Twitter();

if(empty($_GET)) return false;

if(!empty($_GET['getTweets'])) {
    echo $twitter->getTweets();
}else if(!empty($_GET['getCategories'])) {
    echo $twitter->getCategories();
}else if(!empty($_GET['updateTweets'])) {
    echo $twitter->updateTweets();
}