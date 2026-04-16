<?php namespace B2\Modules\Common;

use \Firebase\JWT\JWT;

class Sms{

     public static function percentEncode($string) {
         $string = urlencode ( $string );
         $string = preg_replace ( '/\+/', '%20', $string );
         $string = preg_replace ( '/\*/', '%2A', $string );
         $string = preg_replace ( '/%7E/', '~', $string );
         return $string;
     }

     public static function send_code($code,$phone){
        $sms_type = b2_get_option('normal_login','phome_select');
   
        $res = self::$sms_type($phone,$code);
        if(isset($res['error'])){
            return $res;
        }     

        //对验证码和手机号码进行加密
        $issuedAt = time();
        $expire = $issuedAt + 180;//5分钟时效

        $token = array(
            "iss" => B2_HOME_URI,
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            'exp'=>$expire,
            'data'=>array(
                'code'=>md5(md5(AUTH_KEY.strtolower($code))),
                'username'=>$phone
            )
        );

        $jwt = JWT::encode($token, AUTH_KEY);

        return $jwt;
     }

     public static function yunpian($phone,$code){
        $text = str_replace('#code#',$code,b2_get_option('normal_login','yunpian_text'));
        $resout = wp_remote_post('https://sms.yunpian.com/v2/sms/single_send.json',
            array(
                'method'      => 'POST',
                'timeout'     => 45,
                'body'        => array(
                    'apikey'=>b2_get_option('normal_login','apikey'),
                    'text'=>$text,
                    'mobile'=>$phone,
                    'register'=>true
                )
            ));

        if(is_wp_error( $resout )){
            return array('error'=>$resout->get_error_message());
        }

        $resout = json_decode(trim($resout['body']));

        if(isset($resout->code) && $resout->code != 0){
            return array('error'=>$resout->msg);
        }else{
            return true;
        }
     }

     public static function zhongzheng($phone,$code){
        $content = str_replace('#code#',$code,b2_get_option('normal_login','zz_temp'));	
        
        $resout = wp_remote_post('http://service.winic.org:8009/sys_port/gateway/index.asp',
            array(
                'method'      => 'POST',
                'timeout'     => 45,
                'body'        => array(
                    'id'=>urlencode(iconv("utf-8","gb2312",b2_get_option('normal_login','zz_id'))),
                    'pwd'=>b2_get_option('normal_login','zz_password'),
                    'to'=>$phone,
                    'Content'=>iconv("UTF-8","GB2312",$content),
                    'time'=>''
                )
            ));
            return $resout;
        if(is_wp_error( $resout )){
            return array('error'=>$resout->get_error_message());
        }

        $resout = json_decode(trim($resout['body']));

        if(isset($resout->code) && $resout->code != 0){
            return array('error'=>$resout->msg);
        }else{
            return true;
        }
     }

     public static function juhe($phone,$code){
        $resout = wp_remote_post('http://v.juhe.cn/sms/send',
            array(
                'method'      => 'POST',
                'timeout'     => 45,
                'body'=>array(
                    'mobile'=>$phone,
                    'tpl_id'=>b2_get_option('normal_login','tpl_id'),
                    'key'=>b2_get_option('normal_login','juhe_key'),
                    'tpl_value'=>b2_get_option('normal_login','tpl_id') > 235931 ? '#code#='.$code : urlencode('#code#='.$code)
                )
            )
        );

        if(is_wp_error( $resout )){
            return array('error'=>$resout->get_error_message());
        }

        $resout = json_decode(trim($resout['body']));

        if(isset($resout->error_code) && $resout->error_code != 0){
            return array('error'=>$resout->reason);
        }else{
            return true;
        }
     }

     /**
      * 签名
      *
      */
     public static function computeSignature($parameters, $accessKeySecret) {
         ksort ( $parameters );
         $canonicalizedQueryString = '';
         foreach ( $parameters as $key => $value ) {
             $canonicalizedQueryString .= '&' . self::percentEncode ( $key ) . '=' . self::percentEncode ( $value );
         }
         $stringToSign = 'GET&%2F&' . self::percentencode ( substr ( $canonicalizedQueryString, 1 ) );
         $signature = base64_encode ( hash_hmac ( 'sha1', $stringToSign, $accessKeySecret . '&', true ) );
         return $signature;
     }

     /**
	* 赛邮
	*/ 
	public static function submail($phone,$code){
        $resout = wp_remote_post('https://api.mysubmail.com/message/xsend.json',
            array(
                'method'      => 'POST',
                'timeout'     => 45,
                'body'=>array(
                    'to'=>$phone,
                    'appid'=>b2_get_option('normal_login','saiyou_app_id'),
                    'signature'=>b2_get_option('normal_login','saiyou_app_key'),
					'project'=>b2_get_option('normal_login','saiyou_project'),
					'sign_type' => b2_get_option('normal_login','saiyou_sign_type'),
					'vars' => '{"code":"' . $code . '"}'
                )
            )
        );

        if(is_wp_error( $resout )){
            return array('error'=>$resout->get_error_message());
        }

        $resout = json_decode(trim($resout['body']));

        if(isset($resout->error_code) && $resout->error_code != 0){
            return array('error'=>$resout->reason);
        }else{
            return true;
        }
     }

     public static function tencent($phone,$code){
        $templId = b2_get_option('normal_login','tencent_id');
        $appid = b2_get_option('normal_login','tencent_SmsSdkAppid');
        $appkey = b2_get_option('normal_login','tencent_appkey');
         
        $random = rand(100000, 999999);//生成随机数
        $curTime = time();
        $wholeUrl = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms". "?sdkappid=" . $appid . "&random=" . $random;
        
        // 按照协议组织 post 包体
        $data = new \stdClass();
        $tel = new \stdClass();
        $tel->nationcode = "86";
        $tel->mobile = ''.$phone;
        $data->tel = $tel;
        $data->sig=hash("sha256", "appkey=".$appkey."&random=".$random."&time=".$curTime."&mobile=".$phone);// 生成签名
        $data->tpl_id = $templId;
        $data->params = array($code);
        $data->sign = b2_get_option('normal_login','tencent_Sign');
        $data->time = $curTime;
        $data->extend = '';
        $data->ext = '';
        
       return self::sendCurlPost($wholeUrl, $data);
     }

    public static function sendCurlPost($url, $dataObj){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($curl);

        if (false == $ret) {
            curl_close($curl);
            return array('error'=>curl_error($curl));
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                curl_close($curl);
                return array('error'=>$rsp.'----'.curl_error($curl));
            } else {
                    $result = $ret;
            }
        }

        curl_close($curl);
    
        $result = json_decode ( $result, true );
        if($result['result'] == 0) return 'success';

        return array('error'=>$result['errmsg']);
    }

     public static function others($phone,$code){

        $url = str_replace(array('#phone#','#code#','{code}'),array($phone,$code,$code),b2_get_option('normal_login','others_url'));

        $urlencode = b2_get_option('normal_login','others_urlencode');
        if((int)$urlencode === 1){
            
            // 获取最后一个参数名和值
            $last_param_pos = strrpos($url, '&');
            $last_param_str = substr($url, $last_param_pos+1);
            $last_param_arr = explode('=', $last_param_str);
            $last_param_name = $last_param_arr[0];
            $last_param_value = $last_param_arr[1];
            
            // 对参数值进行 URL 编码
            $encoded_param_value = urlencode($last_param_value);
            
            // 替换原始网址字符串中的参数值为编码后的值
            $new_url = str_replace($last_param_value, $encoded_param_value, $url);
        
            $url = $new_url;
        }

        $resout = wp_remote_post($url);

        if(is_wp_error( $resout )){
            return array('error'=>$resout->get_error_message());
        }

        //如果返回的内容和设置完全一致，发送成功
        if($resout['body'] === b2_get_option('normal_login','others_back')) return true;

        if(strpos($resout['body'],b2_get_option('normal_login','others_back')) !== false) return true;
        
        //短信宝专用
        if(isset($resout['response']['message']) && $resout['response']['message'] == b2_get_option('normal_login','others_back')) return true;
        
        return array('error'=>$resout['body']);
     }
 
     public static function aliyun($mobile, $verify_code) {
         $params = array (
                 'SignName' => b2_get_option('normal_login','sign_name'),
                 'Format' => 'JSON',
                 'Version' => '2017-05-25',
                 'AccessKeyId' => b2_get_option('normal_login','accesskey_id'),
                 'SignatureVersion' => '1.0',
                 'SignatureMethod' => 'HMAC-SHA1',
                 'SignatureNonce' => uniqid (),
                 'Timestamp' => gmdate ( 'Y-m-d\TH:i:s\Z' ),
                 'Action' => 'SendSms',
                 'TemplateCode' => b2_get_option('normal_login','template_code'),
                 'PhoneNumbers' => $mobile,
                 'TemplateParam' => '{"code":"' . $verify_code . '"}'
         );
 
         $params ['Signature'] = self::computeSignature ( $params, b2_get_option('normal_login','access_key_secret') );
         $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query ( $params );
 
         $ch = curl_init ();
         curl_setopt ( $ch, CURLOPT_URL, $url );
         curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
         curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
         curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
         curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );
         $result = curl_exec ( $ch );
         curl_close ( $ch );
         $result = json_decode ( $result, true );
 
         if (isset ( $result ['Code'] ) && $result ['Code'] === 'OK') {
             return true;
         }else{
             return array('error'=>$result['Message'].__('。错误代码：','b2').$result ['Code']);
         }
     }
     /**
      * 获取详细错误信息
      */
     public static function getErrorMessage($status) {
         // 阿里云的短信 乱八七糟的(其实是用的阿里大于)
         // https://api.alidayu.com/doc2/apiDetail?spm=a3142.7629140.1.19.SmdYoA&apiId=25450
         $message = array (
                 'InvalidDayuStatus.Malformed' => __('账户短信开通状态不正确','b2'),
                 'InvalidSignName.Malformed' => __('短信签名不正确或签名状态不正确','b2'),
                 'InvalidTemplateCode.MalFormed' => __('短信模板Code不正确或者模板状态不正确','b2'),
                 'InvalidRecNum.Malformed' => __('目标手机号不正确，单次发送数量不能超过100','b2'),
                 'InvalidParamString.MalFormed' => __('短信模板中变量不是json格式','b2'),
                 'InvalidParamStringTemplate.Malformed' => __('短信模板中变量与模板内容不匹配','b2'),
                 'InvalidSendSms' => __('触发业务流控','b2'),
                 'InvalidDayu.Malformed' => __('变量不能是url，可以将变量固化在模板中','b2')
         );
         if (isset ( $message [$status] )) {
             return $message [$status];
         }
         return $status;
     }
}