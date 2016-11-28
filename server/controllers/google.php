<?php
include dirname(__FILE__).'/app.php';
class Google extends App {
	
    public function getBrandTrendsTop(){
		
        $id = file_get_contents("php://input");
        if(!isset($id)) return false;
		$obj = json_decode($id);		

        $fichero = "../google_trends_top.php";
        $top = file_get_contents($fichero);
		$top_old = array();

		if(!empty($top)) {
			$top_old = unserialize($top); 
			if(isset($top_old['date']) && $top_old['date'] == date('Y-m-d')) {
				//pr($top_old);die();
				foreach($top_old['brands'] as $brand){
					if($brand['name'] == $obj->brand){	
						if(!empty($top_old['selection'])) {	
							return $top_old['selection'];
						}						
					}
				}				
			}
		}

        if(!empty($_SESSION["tend_top_".$obj->brand])) {
			//pr($_SESSION["tend_top_".$obj->brand]);
            return $_SESSION["tend_top_".$obj->brand]; 
        }

        $url = 'https://www.google.com/trends/fetchComponent?hl=es&q=nivea,+nivea%20men,&geo=ES&date=now+7-d&cmpt=q&tz=Etc/GMT-1&content=1&cid=TOP_QUERIES_'.$obj->brand.'_0'; 
        
        $curl = new Curl\Curl();
        $curl->get($url);
        if ($curl->error) {
             echo 'error '. $curl->error_code;          
        }      
		
		$top_old['date'] = date('Y-m-d');		
		$top_old['brands'][$obj->brand]['name'] = $obj->brand;
		$top_old['brands'][$obj->brand]['selection'] = $curl->response;
		$bw = serialize($top_old);
		file_put_contents($fichero, $bw);
		
        $_SESSION["tend_top_".$obj->brand] = $curl->response;
        return $curl->response;
    }

    public function getBrandTrendsRising(){
        $id = file_get_contents("php://input");
		
        if(!isset($id)) return false;
		$obj = json_decode($id);	

        $fichero = "../google_trends_rising.php";
        $rising = file_get_contents($fichero);
		$rising_old = array();

		if(!empty($rising)) {
			$rising_old = unserialize($rising); 
			if(isset($rising_old['date']) && $rising_old['date'] == date('Y-m-d')) {
				//pr($rising_old);die();
				foreach($rising_old['brands'] as $brand){
					if($brand['name'] == $obj->brand){	
						if(!empty($rising_old['selection'])) {	
							return $rising_old['selection'];
						}						
					}
				}				
			}
		}
		
        if(!empty($_SESSION["tend_rising_".$obj->brand])) {
            return $_SESSION["tend_rising_".$obj->brand]; 
        }

        $url = 'https://www.google.com/trends/fetchComponent?hl=es&q=nivea,+nivea%20men,&geo=ES&date=now+7-d&cmpt=q&tz=Etc/GMT-1&tz=Etc/GMT-1&content=1&cid=RISING_QUERIES_'.$obj->brand.'_0&export=5'; 

        $curl = new Curl\Curl();
        $curl->get($url);
        if ($curl->error) {
             echo 'error '. $curl->error_code;          
        }  
		
		$rising_old['date'] = date('Y-m-d');		
		$rising_old['brands'][$obj->brand]['name'] = $obj->brand;
		$rising_old['brands'][$obj->brand]['selection'] = $curl->response;
		$bw = serialize($rising_old);
		file_put_contents($fichero, $bw);
		
        $_SESSION["tend_rising_".$obj->brand] = $curl->response;
        return $curl->response;
    }

}

$google = new Google();

if(empty($_GET)) return false;

if(!empty($_GET['getBrandTrendsTop'])) {
    echo $google->getBrandTrendsTop();
}else if(!empty($_GET['getBrandTrendsRising'])) {
    echo $google->getBrandTrendsRising();
}