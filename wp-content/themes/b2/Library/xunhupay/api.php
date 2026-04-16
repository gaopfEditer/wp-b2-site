<?php 
class XunHu_Payment_Api{
    /**
     * http_post传输
     * @param array $url
     * @param string $jsonStr
     */
    public static function http_post_json($url, $jsonStr){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	            'Content-Type: application/json; charset=utf-8',
	            'Content-Length: ' . strlen($jsonStr)
	        )
	    );
	    $response = curl_exec($ch);
	    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	 
	    return $response;
	}
	 /**
     * url拼接
     * @param array $url
     * @param string $datas
     */
	public static function data_link($url,$datas){
		ksort($datas);
        reset($datas);
        $pre =array();
        foreach ($datas as $key => $data){
            if(is_null($data)||$data===''){
                continue;
            }
            if($key=='time_end'){
            continue;
            }
            $pre[$key]=$data;
        }

        $arg  = '';
        $qty = count($pre);
        $index=0;
		 foreach ($pre as $key=>$val){
		 		$val=urlencode($val);
			 	$arg.="$key=$val";
	            if($index++<($qty-1)){
	                $arg.="&amp;";
	            }	
        }
        return $url.'?'.$arg;
	}
   /**
     * 签名方法
     * @param array $datas
     * @param string $hashkey
     */
    public static function generate_xh_hash(array $datas,$hashkey){


        ksort($datas);
        reset($datas);
        $pre =array();
        foreach ($datas as $key => $data){
            if(is_null($data)||$data===''){
                continue;
            }
            if($key=='sign'){
                continue;
            }
            $pre[$key]=$data;
        }
        $arg  = '';
        $qty = count($pre);
        $index=0;

        foreach ($pre as $key=>$val){
            $arg.="$key=$val";
            if($index++<($qty-1)){
                $arg.="&";
            }
        }
        return strtoupper(md5($arg.'&key='.$hashkey));
    }
    
}
?>