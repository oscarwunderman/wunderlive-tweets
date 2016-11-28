<?php
include dirname(__FILE__).'/app.php';
class Brandwatch extends App {

    public function getCloud(){
		
        $name = file_get_contents("php://input");
        if(!isset($name)) return false;
		$obj = json_decode($name);
		
        $fichero = "../bw.php";
        $bw_file = file_get_contents($fichero);
		$bw_old = array();
	
		if(!empty($bw_file)) {
			$bw_old = unserialize($bw_file); 
			if(isset($bw_old['date']) && $bw_old['date'] == date('Y-m-d')) {
				//pr($bw_old);die();
				foreach($bw_old['brands'] as $brand){
					if($brand['name'] == $obj->brand){	
						if(!empty($bw_old['selection'])) {	
							return $bw_old['selection'];
						}
					}
				}
			}
		}

        if(!empty($_SESSION["cloud_".$obj->brand])) {
            return json_encode($_SESSION["cloud_".$obj->brand]); 
        }

        $start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
        $start_time = date('H:i:s',strtotime(date('H:i:s')));
        $startDate = $start_date .'T'. $start_time.'.000Z';

        $end_date = date('Y-m-d');
        $end_time = date('H:i:s');
        $endDate = $end_date .'T'. $end_time .'.000Z';

        //$name = 'cepsa';
        switch ($obj->brand) {
            case 'nivea': 
                //$start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d')));
                //$start_time = date('H:i:s',strtotime(date('H:i:s')));
                //$startDate = $start_date .'T'. $start_time.'.000Z';           
                $BrandWatchId = BRANDWATCH_QUERYIDNIVEAGENERAL;
                break;
            case 'nivea_tw':
                // cloud cepsa españa fb
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d')));
                //$start_time = date('H:i:s',strtotime(date('H:i:s')));
               // $startDate = $start_date .'T'. $start_time.'.000Z';        
                $BrandWatchId = BRANDWATCH_QUERYIDNIVEATW;
                break;
            case 'nivea_fb':
                // cloud repsol tw
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
                //$start_date = date('Y-m-d',strtotime(date('Y-m-d')));
                //$start_time = '00:00:00';
                //$startDate = $start_date .'T'. $start_time.'.000Z'; 
                $BrandWatchId = BRANDWATCH_QUERYIDNIVEAFB;
                break;
            case 'nivea_men':
                // cloud bp
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
                //$start_date = date('Y-m-d',strtotime(date('Y-m-d')));
                //$start_time = '00:00:00';
                //$startDate = $start_date .'T'. $start_time.'.000Z'; 
                $BrandWatchId = BRANDWATCH_QUERYIDNIVEAMENGENERAL;
                break;
            case 'nivea_men_tw':
                // cloud galp
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
                //$start_date = date('Y-m-d',strtotime(date('Y-m-d')));
                //$start_time = '00:00:00';
                //$startDate = $start_date .'T'. $start_time.'.000Z'; 
                $BrandWatchId = BRANDWATCH_QUERYIDNIVEAMENTWITTER;
                break;
			case 'nivea_men_fb':
                // cloud galp
				//$start_date = date('Y-m-d',strtotime(date('Y-m-d'). '- 1 week'));
                //$start_date = date('Y-m-d',strtotime(date('Y-m-d')));
                //$start_time = '00:00:00';
                //$startDate = $start_date .'T'. $start_time.'.000Z'; 
                $BrandWatchId = BRANDWATCH_QUERYIDNIVEAMENFACEBOOK;
                break;   
            default:
                //# code...
                break;
        }

        $url = 'https://newapi.brandwatch.com/projects/'. BRANDWATCH_PROJECTID .'/data/volume/topics/queries'; 
        $params = array(
            'queryId'       =>  $BrandWatchId,
            'startDate'     =>  $start_date,
            'endDate'       =>  $endDate,
            'access_token'  =>  BRANDWATCH_TOKEN
        );

        $curl = new Curl\Curl();
        $curl->get($url,$params);
        //pr($curl);die;
        if ($curl->error) {
            if($curl->error == 'invalid_token'){ //TODO -> revisar
                $url_token = 'https://newapi.brandwatch.com/oauth/token';
                $query_token = array(
                    'username'  => BRANDWATCH_USERNAME,
                    'password'  => urlencode(BRANDWATCH_PASSWORD),
                    'grant_type'=> 'api-password',
                    'client_id' => 'brandwatch-api-client');
                $curl->post($url_token, $query_token);   
                //pr($curl);
                //$query = array('access_token' => $curl->response->access_token);
                //$curl->get($url, $query);          
            } else {
                echo 'error '. $curl->error_code;
            }           
        }      
        $response_object = json_decode($curl->response); 

        $url_mentions = 'https://newapi.brandwatch.com/projects/'. BRANDWATCH_PROJECTID .'/data/mentions'; 
        $params_mentions = array(
            'queryId'       =>  $BrandWatchId,
            'startDate'     =>  $start_date,
            'endDate'       =>  $endDate,
            'access_token'  =>  BRANDWATCH_TOKEN,
            'pageSize'      =>  '1000',
            'orderDirection'=>  'desc'
        );

        $curl->get($url_mentions,$params_mentions);

        if ($curl->error) {
            if($curl->error == 'invalid_token'){ //TODO -> revisar
                $url_token = 'https://newapi.brandwatch.com/oauth/token';
                $query_token = array(
                    'username'  => BRANDWATCH_USERNAME,
                    'password'  => urlencode(BRANDWATCH_PASSWORD),
                    'grant_type'=> 'api-password',
                    'client_id' => 'brandwatch-api-client');
                $curl->post($url_token, $query_token);   
                //pr($curl->response);
                //$query = array('access_token' => $curl->response->access_token);
                //$curl->get($url, $query);          
            } else {
                echo 'error '. $curl->error_code;
            }           
        }      
        $response_object_mentions = json_decode($curl->response); 
		$data = array();
		if(isset($response_object->topics)){
			foreach ($response_object->topics as $key => $value) {     
				$data[$key]['text'] = $value->label;
				$data[$key]['weight'] = $value->volume;
				foreach ($response_object_mentions->results as $key_mentions => $value_mentions) {
					if($value->label )
					if (strpos($value_mentions->title, $value->label) !== false || strpos($value_mentions->snippet, $value->label) !== false){                
						$data[$key]['mentions'][$key_mentions]['title'] = $value_mentions->title;
						$data[$key]['mentions'][$key_mentions]['snippet'] = $value_mentions->snippet;
						$data[$key]['mentions'][$key_mentions]['url'] = $value_mentions->url;
					}
				}     
			}      
		}
		
		$bw_old['date'] = date('Y-m-d');
		$bw_old['brands'][$obj->brand]['name'] = $obj->brand;
		$bw_old['brands'][$obj->brand]['selection'] = $data;
		$bw = serialize($bw_old);
		file_put_contents($fichero, $bw);
		
        $_SESSION["cloud_".$obj->brand] = $data;
        return json_encode($data);          
    }
}

$brandwatch = new Brandwatch();

if(empty($_GET)) return false;

if(!empty($_GET['getCloud'])) {
    echo $brandwatch->getCloud();
}