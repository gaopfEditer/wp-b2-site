<?php namespace B2\Modules\Common;

use \Firebase\JWT\JWT;
use B2\Modules\Templates\Modules\Sliders;
use B2\Modules\Common\Post;
use B2\Modules\Common\Verify;

class Wecatmp{

    //公众号设置项
    public static function get_wecat_option(){
        return array(
            'appid'          => b2_get_option('normal_weixin','weixin_appid'),
            'appsecret'      => b2_get_option('normal_weixin','weixin_appsecret'),
            'token'=>b2_get_option('normal_weixin','weixin_token'),
            'encodingaeskey'=>b2_get_option('normal_weixin','weixin_encodingaeskey')
        );
    }

    //获取并保存 ticket
    public static function get_ticket($type){
        $user_id = b2_get_current_user_id();
        try {
            // 实例接口
            $wechat = new \WeChat\Qrcode(self::get_wecat_option());
            
            // 执行操作
            $ticket = $wechat->create($user_id,2592000);

            if($ticket){
                $ticket['expire_seconds'] = time() + $ticket['expire_seconds'];
                return $ticket;
            }
            
        } catch (\Exception $e){
            // 异常处理
            return array('error'=>$e->getMessage());
        }
    }

    //消息回调
    public static function mp_notify(){

        if(!isset($_GET["signature"])) return 'fail';

        $api = \We::WeChatReceive(self::get_wecat_option());

        $msgType = $api->getMsgType();
        
        if(method_exists(__CLASS__,$msgType)){
            $data = $api->getReceive();
            self::$msgType($data,$api);
        }else{
            return 'success';
        }
    }

    //关注事件
    public static function event($data,$api){

        //如果是取消关注，加入黑名单
        if($data['Event'] === 'unsubscribe'){

            $allow = b2_get_option('verify_main','verify_allow');

            if(!$allow) return;

            $settings = b2_get_option('verify_main','verify_type');
            $user_id = 0;

            if(in_array('2',$settings)){
                
                $user = get_users(array('meta_key'=>'zrz_weixin_uid','meta_value'=>$data['FromUserName']));

                if($user){
                    $user_info = $user[0]->data;
                    $user_id = $user_info->ID;

                    $_data = array(
                        'date'=>current_time('mysql'),
                        'user_id'=>$user_id,
                        'mp'=>0,
                        'status' => 3
                    );

                    Verify::add_verify_data($_data);
                }

                delete_user_meta($user_id,'b2_title');
                wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            }

            do_action('b2_verify_unsubscribe',$user_id,$data);
            return 'success';
        }

        //如果是关注
        if($data['Event'] === 'subscribe' && isset($data['EventKey']) && !empty($data['EventKey'])){

            $key = str_replace('qrscene_','',$data['EventKey']);

            $sence_id = get_transient('sence_id_'.$key);

            if((int)$sence_id === 1){

                set_transient('sence_id_'.$key, $data['FromUserName'], 60*5);

                $text = sprintf(__('感谢您关注「%s」，%s','b2'),B2_BLOG_NAME,current_time('mysql'));

                $login_text = b2_get_option('normal_login','wx_mp_login_text');

                if($login_text){
                    $text =  $login_text;
                }

                $api->text($text)->reply();

                return 'success';
            }

            $allow = b2_get_option('verify_main','verify_allow');

            if(!$allow) return 'success';

            $user_id = (int)str_replace('qrscene_','',$data['EventKey']);

            $settings = b2_get_option('verify_main','verify_type');

            //添加一条记录
            $_data = array(
                'date'=>current_time('mysql'),
                'user_id'=>$user_id,
                'mp'=>1
            );
            Verify::add_verify_data($_data);

            //发送欢迎关注的句子
            $api->text(b2_get_option('verify_main','verify_mp_text'))->reply();

            //绑定微信登录
            $token = self::get_token();
            if(isset($token['error'])) return $token;

            $user_info = self::get_user_info(array(
                'access_token'=>$token,
                'openid'=>$data['FromUserName']
            ));

            if(isset($user_info['error'])) return $user_info;

            if(isset($user_info['openid'])){
                $user_info['avatar'] = '';
                $user_info['type'] = 'weixin';
                $user_info['uid'] = $user_info['openid'];

                //检查是否绑定
                if(!OAuth::check_binding($user_info)){
                    OAuth::binding($user_info,$user_id);
                }
            }
            

            do_action('b2_verify_subscribe',$user_id,$data);

            return 'success';
        }

        //如果是微信登录
        if(isset($data['EventKey']) && get_transient('sence_id_'.$data['EventKey'])){

            set_transient('sence_id_'.$data['EventKey'], $data['FromUserName'], 60*5);

            $api->text(sprintf(__('欢迎登录「%s」，%s','b2'),B2_BLOG_NAME,current_time('mysql')))->reply();

            return 'success';
        }
        
    }

    //储存验证码和openid
    // public static function save_code_openid($code,$openid){
    //     $data = get_option('b2_mp_login');
    //     $data = is_array($data) ? $data : array();

    //     foreach ($data as $k => $v) {

    //         if((int)$k === (int)$code){
    //             return false;
    //         }

    //         if(isset($v['time']) && ($v['time'] + 600) < time()){
    //             unset($data[$k]);
    //         }
    //     }

    //     $data[$code] = array(
    //         'openid'=>$openid,
    //         'time'=>time()
    //     );

    //     //储存信息
    //     update_option('b2_mp_login',$data);
    //     return true;
    // }

    public static function text($data,$api){

        do_action("b2_mp_wechat_text",$data,$api);
        
        $key = '';

        switch ($data['Content']) {
            case 'n':
                $args = array(
                    'post_type'=>'post',
                    'posts_per_page' => 1,
                    'post_status'=>'publish'
                );
                break;
            case 'r':
                $args = array(
                    'post_type'=>'post',
                    'posts_per_page' => 1,
                    'orderby' => 'rand',
                    'post_status'=>'publish',
                );
                break;
            default:

                $key = self::search_mp_key($data['Content']);

                if($key){
                    $args = array(
                        'post_type'=>array('post','page','document','shop','newsflashes','announcement','circle'),
                        'posts_per_page' => 1,
                        'post_status'=>'publish',
                        'search_tax_query'=>true,
                        'p'=>$key[0]['post_id']
                    );
                }else{
                    $args = array(
                        'post_type'=>array('post','page','document','shop','newsflashes','announcement','circle'),
                        'posts_per_page' => 1,
                        'post_status'=>'publish',
                        'search_tax_query'=>true,
                        's'=>$data['Content']
                    );
                }
                
                
                break;
        }

        $args['no_found_rows'] = true;

        $the_query = new \WP_Query( $args );

        $res = array();

        if ( $the_query->have_posts() ) {

            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                $res[] = array(
                    'Title'=>get_the_title($the_query->post->ID),
                    'Description'=>Sliders::get_des('',150,b2_get_excerpt($the_query->post->ID)),
                    'PicUrl'=>b2_get_thumb(array('thumb'=>Post::get_post_thumb($the_query->post->ID),'width'=>360,'height'=>200)),
                    'Url'=>$key ? get_the_permalink($key[0]['post_id']) : B2_HOME_URI.'/?s='.$data['Content'].'&type=post'
                );
            }
            
        }
        wp_reset_postdata();
        if(!empty($res)){
            $api->news($res)->reply();
        }else{
            $api->text(__('回复n：获取最新一篇文章；回复r：获取一篇随机文章；','b2'))->reply();
        }

    }

    public static function search_mp_key($key){
        

        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';

        //检查之前是否有相同的数据
        $res = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name WHERE `meta_key` = 'single_post_mp_back_key' AND `meta_value` = %s
                ",
                $key
            )
        ,ARRAY_A);

        return $res;
    }

    public static function remote_get($url){
        $res = wp_remote_get($url);

        if(is_wp_error($res)){
            return array('error'=>$res->get_error_message());
        }

        if(isset($res['errcode'])){
            return array('error'=>sprintf(__('错误代码：%s；错误信息：%s；请在百度中搜索相关错误代码进行修正。','b2'),$res['errcode'],$res['errmsg']));
        }

        $msg = json_decode($res['body'],true);
        
        return $msg;
    }


    //获取access_token
    public static function get_token(){


        $settings = self::get_wecat_option();

        // $jssdk = new \JSSDK($settings['appid'],$settings['appsecret']);

        // return $jssdk->getAccessToken();

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$settings['appid'].'&secret='.$settings['appsecret'];

        $msg = self::remote_get($url);

        if(isset($msg['error'])){
            return $msg;
        }

        return $msg['access_token'];
    }

    //获取用户基本信息
    public static function get_user_info($data){

        return self::remote_get('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN');
    }

    public static function get_sence_id(){

        $sence_id = rand(1000000,9999999);
        $save_sence_id = get_transient('sence_id_'.$sence_id);

        while ($save_sence_id) {
            $sence_id = rand(1000000,9999999);
            $save_sence_id = get_transient('sence_id_'.$sence_id);
        }

        set_transient('sence_id_'.$sence_id, 1, 60*5);

        return $sence_id;
    }

    //获取登录 ticket
    public static function get_login_ticket(){
        try {
            // 实例接口
            $wechat = new \WeChat\Qrcode(self::get_wecat_option());
            $sence_id = self::get_sence_id();
            // 执行操作
            $ticket = $wechat->create($sence_id,2592000);

            if($ticket){
                return array(
                    'ticket'=>$ticket,
                    'sence_id'=>$sence_id
                );
            }
            
        } catch (\Exception $e){
            // 异常处理
            return array('error'=>$e->getMessage());
        }
    }

    //获取登录二维码
    public static function get_login_qrcode(){
        
        $ticket = self::get_login_ticket();

        if(isset($ticket['error'])) return $ticket;

        try {

            $wechat = new \WeChat\Qrcode(self::get_wecat_option());
            return array(
                'qrcode'=>$wechat->url($ticket['ticket']['ticket']),
                'sence_id'=>$ticket['sence_id']
            );
            
        } catch (\Exception $e){
            return array('error'=>$e->getMessage());
        }
    }

    public static function mp_login($code){
        $ip = b2_get_user_ip();

        $count = wp_cache_get(md5($ip));
        if((int)$count >= 100){
            return array('error'=>__('登录失败','b2'));
        }

        $save_sence_id = get_transient('sence_id_'.$code);

        if($save_sence_id === false) return array('error'=>__('参数错误','b2'));

        if((int)$save_sence_id === 1) return 'waiting';

        $check = OAuth::check_binding(array('uid'=>$save_sence_id,'type'=>'weixin'));
        $check = $check ? (array)$check : false;

        $user_id = b2_get_current_user_id();
        if($user_id){
            //执行绑定
            if($check){
                return array('error'=>__('当前账户已存在，请不要重复绑定','b2'));
            }else{
                //绑定微信登录
                $token = self::get_token();
                if(isset($token['error'])) return $token;

                $user_info = self::get_user_info(array(
                    'access_token'=>$token,
                    'openid'=>$save_sence_id
                ));

                if(isset($user_info['error'])) return $user_info;

                if(isset($user_info['openid'])){
                    $user_info['avatar'] = '';
                    $user_info['type'] = 'weixin';
                    $user_info['uid'] = $user_info['openid'];

                    $_data = array(
                        'date'=>current_time('mysql'),
                        'user_id'=>$user_id,
                        'mp'=>1
                    );
                    Verify::add_verify_data($_data);

                    return OAuth::binding($user_info,$user_id);
                }
            }
        }

        if($check){
            if(!$count){
                wp_cache_add(md5($ip),1,'',60);
            }else{
                wp_cache_set(md5($ip),$count+1,'',60);
            }

            self::add_verify($check['ID']);

            return OAuth::user_login($check['ID']);
        }else{

            if(!b2_get_option('normal_login','allow_register')){
                return array('error'=>__('本站已关闭注册','b2'));
            }
            
            $token = self::get_token();
            if(isset($token['error'])) return $token;
            
            $user_info = self::get_user_info(array(
                'access_token'=>$token,
                'openid'=>$save_sence_id
            ));

            if(isset($user_info['errcode'])){
                return array('error'=>sprintf(__('错误代码%s,错误信息：%s'),$user_info['errcode'],$user_info['errmsg']));
            }

            if(isset($user_info['error'])) return $user_info;

            if(isset($user_info['openid'])){
                $user_info['avatar'] = '';
                $user_info['type'] = 'weixin';
                $user_info['uid'] = $user_info['openid'];
            }else{
                return array('error'=>__('请先关注我们的公众号','b2'));
            }

            $invitation = b2_get_option('invitation_main','required');

            if($invitation == 1 || $invitation == 2){
                if(class_exists('Jwt_Auth_Public')){
                    $issuedAt = time();
                    $expire = $issuedAt + 300;

                    $token = array(
                        "iss" => B2_HOME_URI,
                        "iat" => $issuedAt,
                        "nbf" => $issuedAt,
                        'exp'=>$expire,
                        'data'=>$user_info
                    );

                    $jwt = JWT::encode($token, AUTH_KEY);

                    if(!$count){
                        wp_cache_add(md5($ip),1,'',60);
                    }else{
                        wp_cache_set(md5($ip),$count+1,'',60);
                    }

                    return array(
                        'type'=>'invitation',
                        'token'=>$jwt
                    );
                }else{

                    if(!$count){
                        wp_cache_add(md5($ip),1,'',60);
                    }else{
                        wp_cache_set(md5($ip),$count+1,'',60);
                    }

                    return array('error'=>__('请安装 JWT Authentication for WP-API 插件','b2'));
                }
            }else{

                if(!$count){
                    wp_cache_add(md5($ip),1,'',60);
                }else{
                    wp_cache_set(md5($ip),$count+1,'',60);
                }
                

                return OAuth::create_user($user_info,'',true);
            }
        }
    }

    public static function mp_login_inv($token,$inv){

        $inv_settings = b2_get_inv_settings();

        //检查验证码
        if(($inv_settings['type'] == 1 && $inv) || $inv_settings['type'] == 2){
            $check_invitation = Invitation::invitationCheck($inv);
            if(isset($check_invitation['error'])){
                return $check_invitation;
            }
        }

        try{
            //检查验证码
            $decoded = JWT::decode($token, AUTH_KEY,array('HS256'));
            //return array('error'=>$decoded);
            if(!isset($decoded->data->uid) && !isset($decoded->data->unionid)){
                return array('error'=>__('参数错误','b2'));
            }

            $data = (array)$decoded->data;

        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return array('error'=>__('注册时间过期，请返回重新注册','b2'));
        }catch(\Exception $e) {  //其他错误
            return array('error'=>__('解码失败','b2'));
        }

        return OAuth::create_user($data,$inv,true);
    }

    public static function add_verify($user_id){
        $data = Verify::get_verify_data($user_id);
        if(!isset($data['error'])){
            if((int)$data['mp'] === 1){
                return;
            }

            if((int)$data['status'] === 3){
                return;
            }
        }

        Verify::add_verify_data(
            array(
                'user_id'=>$user_id,
                'mp'=>1
            )
        );
    }

    public static function message_success($data){

        if(!b2_get_option('normal_weixin_message','weixin_message_open')) return 'close';

        $config = Wecatmp::get_wecat_option();

        $template = apply_filters('b2_get_wx_msg_tmp', $data['type']);

        if(isset($template['temp_id']) && $template['temp_id']){
            $data['template_id'] = $template['temp_id'];
        }else{
            return array('error'=>__('模板错误','b2'));
        }

        $openid = get_user_meta($data['touser'],'zrz_weixin_uid',true);

        if(!$openid){
            return array('error'=>__('请先绑定微信登陆','b2'));
        }else{
            $data['touser'] = $openid;
        }

        try {

            unset($data['type']);

            // 实例接口
            $wechat = new \WeChat\Template($config);

            // 执行操作
            $result = $wechat->send($data);

            return 'success';
            
        } catch (\Exception $e){
            
            return false;
        }
    }
}
