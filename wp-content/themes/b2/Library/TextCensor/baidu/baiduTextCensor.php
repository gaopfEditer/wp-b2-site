<?php
class b2BaiduTextCensor{
    //appid
    public $appId = '';

    //apiKey
    public $apiKey = '';

    //secretKey
    public $secretKey = '';

    public $reSave = false;

    public function __construct(){
        $this->appId = trim(b2_get_option('normal_safe','baidu_appId'));
        $this->apiKey = trim(b2_get_option('normal_safe','baidu_apiKey'));
        $this->secretKey = trim(b2_get_option('normal_safe','baidu_secretKey'));
    }

    public function getTokenPath(){
        return B2_THEME_DIR . '/Library/TextCensor/baidu/token/' . md5($this->apiKey);
    }

    //写入token
    public function saveToken($obj){

        $obj['time'] = strtotime(current_time('Y-m-d H:i:s'));
        @file_put_contents($this->getTokenPath(), json_encode($obj));
    }

    //读取缓存token
    public function readToken(){
        $content = @file_get_contents($this->getTokenPath());

        if ($content !== false) {
            $obj = json_decode($content, true);
            if (isset($obj['expires_in']) && $obj['time'] + $obj['expires_in'] - 30 > strtotime(current_time('Y-m-d H:i:s'))) {
                return $obj;
            }
        }

        return false;
    }

    //token请求
    public function get_token($refresh = false){

        $obj = $this->readToken();

        if($obj) {
            return $obj;
        }else{
            $this->reSave = true;
        }

        $obj = $this->wpRequest(
            'https://aip.baidubce.com/oauth/2.0/token',
            array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->apiKey,
                'client_secret' => $this->secretKey,
            )
        );

        if(!isset($obj['scope']) || strpos($obj['scope'],'brain_all_scope') === false){
            return array('error'=>__('无权调用百度api','b2'));
        }

        return $obj;
    }

    public function wpRequest($url, $params = ""){
        $args = array(
            'headers' => array(
                'content-type' => 'application/x-www-form-urlencoded'
            ),
            'body'=>$params
        );
        
        $response = wp_remote_post($url, $args);

        if(is_wp_error($response)){
            return array('error'=>$response->get_error_message());
        }

        return json_decode($response['body'],true);
    }
}