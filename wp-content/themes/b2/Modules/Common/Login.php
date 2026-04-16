<?php namespace B2\Modules\Common;

use \Firebase\JWT\JWT;

use B2\Modules\Common\Distribution;

use B2\Modules\Common\User;
use B2\Modules\Common\Circle;
use B2\Modules\Common\Shop;

class Login{

    public function init(){
        add_filter('jwt_auth_token_before_dispatch', array($this,'rebulid_jwt_token'),10,3);
        //add_filter( 'jwt_auth_expire', array($this,'jwt_auth_expire'));
        //邮件支持html
        add_filter( 'wp_mail_content_type',array($this,'mail_content_type'));
        //用户注册钩子
        add_action( 'user_register', array($this,'user_register'),10,1);

        add_action( 'delete_user', [$this,'delete_user_action']);

    }

    public function delete_user_action($user_id){
        delete_option('b2_site_user_count');
    }

    public function user_register($user_id){

        $credit = (int)b2_get_option('normal_gold','credit_login');
        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$user_id,
            'gold_type'=>0,
            'no'=>$credit,
            'msg'=>sprintf(__('欢迎来到%s！','b2'),B2_BLOG_NAME),
            'type'=>'register',
            'type_text'=>__('注册奖励','b2')
        ]);

        Message::update_data([
            'date'=>current_time('mysql'),
            'from'=>0,
            'to'=>$user_id,
            'post_id'=>0,
            'msg'=>b2_get_option('normal_login','register_msg'),
            'type'=>'register',
            'type_text'=>__('新用户消息','b2')
        ]);

        $ref = b2_getcookie('ref');

        if($ref){
            apply_filters('b2_check_distribution', $user_id,$ref);
        }

        b2_deletecookie('ref');

        delete_option('b2_site_user_count');

        //积分记录
        // Message::add_message(array(
        //     'user_id'=>$user_id,
        //     'msg_type'=>4,
        //     'msg_read'=>0,
        //     'msg_date'=>current_time('mysql'),
        //     'msg_users'=>0,
        //     'msg_credit'=>$credit,
        //     'msg_credit_total'=>$total,
        //     'msg_key'=>'',
        //     'msg_value'=>''
        // ));
    }

    public function mail_content_type(){
        return "text/html";
    }

    public function jwt_auth_expire( $issuedAt ) {

        return $issuedAt;
    }

    public function rebulid_jwt_token($data,$user){
  
        $expiration = (int)b2_get_option('normal_login','login_keep')*DAY_IN_SECONDS;

        b2_setcookie('b2_token',$data['token'],$expiration);

        //pc端设置cookie
        $allow_cookie = apply_filters('b2_login_cookie', b2_get_option('normal_login','allow_cookie'));
        if((string)$allow_cookie === '1'){
           wp_set_auth_cookie($user->data->ID,true);
        //    wp_set_current_user( $user->data->ID );
        }

        $_data = apply_filters('b2_current_user_data', $user->data->ID);

        $_data['token'] = $data['token'];

        $issuedAt = time();
        $notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);

        $_data['exp'] = $expire;

        do_action('b2_user_login',$user);

        do_action( 'wp_login',$user->user_login,$user);

        return $_data;
    }

    //登出
    public static function login_out(){

        $user_id = b2_get_current_user_id();

        $allow_cookie = apply_filters('b2_login_cookie', b2_get_option('normal_login','allow_cookie'));
        if((string)$allow_cookie === '1'){
            wp_logout();
        }
        b2_deletecookie('b2_token');
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');

        do_action('b2_login_out', $allow_cookie);

        return array(
            'oauth'=> ''
        );
    }

    public static function get_user_info($ref){

        $current_user_id = b2_get_current_user_id();


        if(!$current_user_id){
            return array('error'=>__('登录已过期，请重新登录','b2'));
        }

        $role = array(
            'write'=>User::check_user_role($current_user_id,'post'),
            'newsflashes'=>User::check_user_role($current_user_id,'newsflashes'),
            'create_circle'=>User::check_user_role($current_user_id,'circle_create'),
            'create_topic'=>User::check_user_role($current_user_id,'circle_topic'),
            'binding_login'=>self::check_login_name($current_user_id),
            'user_data'=>apply_filters('b2_current_user_data', $current_user_id),
            'can_img'=>User::check_user_media_role($current_user_id,'image'),
            'can_ask'=>User::check_user_role($current_user_id,'ask'),
            'can_answer'=>User::check_user_role($current_user_id,'answer'),
            'can_video'=>User::check_user_media_role($current_user_id,'video'),
            'can_file'=>User::check_user_media_role($current_user_id,'file'),
            'carts'=>count(get_user_meta($current_user_id,'b2_carts',false)),
            'image_size'=>b2_get_option('normal_write','write_image_size'),
            'video_size'=>b2_get_option('normal_write','write_video_size'),
            'file_size'=>b2_get_option('normal_write','write_file_size'),
            'msg_unread'=>Message::get_user_unread_msg_count($current_user_id),
            'dmsg_unread'=>Directmessage::get_dmsg_unread_count($current_user_id)
        );

        //检查是否在小黑屋
        User::check_dark_room($current_user_id);
        //检查圈子过期
        Circle::circle_pass($current_user_id);

        // $user_data = get_userdata($user_id);
        // $data = array(
        //     'name'=>$user_data->display_name,
        //     'link'=>get_author_posts_url($user_id),
        //     'avatar'=>get_avatar_url($user_id, array('size'=>100)),
        //     'credit'=>get_user_meta($user_id,'zrz_credit_total',true),
        //     'money'=>get_user_meta($user_id,'zrz_rmb',true)
        // );
            
        // $data['display_name'] = $user_data->display_name;

        $role = apply_filters( 'b2_get_user_info', $role, $current_user_id);
        return $role;

    }

    public static function delete_user($arg){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        if(user_can($user_id, 'administrator')) return array('error'=>__('管理员无法注销','b2'));

        if(!isset($arg['password'])) return array('error'=>__('请输入密码','b2'));

        $userdata = get_userdata( $user_id );

        $pass = wp_check_password($arg['password'], $userdata->user_pass, $user_id);

        if($pass){
            require_once( ABSPATH.'wp-admin/includes/user.php' );
            return wp_delete_user($user_id);
        }

        return array('error'=>__('密码错误','b2'));
    }

     /**
     * 聚合社交登录
     */

     public static function juhe_social_login($type){
        $social_types = ['qq','sina','wx','baidu','alipay','huawei','xiaomi','dingtalk','facebook','twitter','google','github','gitee','microsoft'];

        if(!in_array($type,$social_types)) return ['error'=>__('参数错误','b2')];

        $appid = trim(b2_get_option('normal_login','juhe_appid'), " \t\n\r\0\x0B\xC2\xA0");
        $appkey = trim(b2_get_option('normal_login','juhe_appkey'), " \t\n\r\0\x0B\xC2\xA0");
        $url = rtrim(trim(b2_get_option('normal_login','juhe_url'), " \t\n\r\0\x0B\xC2\xA0"),'/');

        $api = $url.'/connect.php?act=login&appid='.$appid.'&appkey='.$appkey.'&type='.$type.'&redirect_uri='.urlencode (B2_HOME_URI.'/open?juhe=1');

        $res = wp_remote_post($api);
        if(is_wp_error($res)){
            return ['error'=>__('登录失败，请稍后再试','b2')];
        }

        $qrcode = '';
        $url = '';

        if($res['response']['code'] == 200){
            $data = json_decode($res['body'],true);

            if(isset($data['code']) && (int)$data['code'] !== 0){
                return ['error'=>__('登录失败，请稍后再试','b2')];
            }
            
            if(isset($data['qrcode']) && $data['qrcode']){
                $qrcode = $data['qrcode'];
            }

            if(isset($data['url']) && $data['url']){
                $url = $data['url'];
            }
        }else{
            return ['error'=>__('登录失败，请稍后再试','b2')];
        }

        if($url){
            $url = str_replace('type=qq','type=juheqq',$url);
            return $url;
        }

        return ['error'=>__('登录失败，请稍后再试','b2')];
    }

    /**
     * 检查是否需要强制绑定用户信息
     *
     * @param [int] $current_user_id 当前用户ID
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function check_login_name($current_user_id){

        //验证方式
        $check_allow = (int)b2_get_option('normal_login','build_phone_email');
        if(!$check_allow) return false;

        if(user_can($current_user_id, 'administrator')) return false;

        //检查验证方式
        $check_type = b2_get_option('normal_login','check_type');

        $user_data = get_userdata($current_user_id);

        $domain = get_option('wp_site_domain');

        //检查是否绑定手机号码
        if($check_type === 'tel'){
            if(!self::is_phone($user_data->data->user_login)) return 'tel';
            return false;
        }

        //检查是否绑定了邮箱
        if($check_type === 'email'){
            if(empty($user_data->data->user_email) || strpos($user_data->data->user_email,$domain) !== false) return 'email';
            return false;
        }

        if($check_type === 'telandemail'){
            if((empty($user_data->data->user_email) || strpos($user_data->data->user_email,$domain) !== false) && !self::is_phone($user_data->data->user_login)) return 'telandemail';
            return false;
        }

        if($check_type === 'text' || $check_type === 'luo'){
            if(strpos($user_data->data->user_login,'user'.$current_user_id) !== false) return 'login';
            return false;
        }
    }

    public static function bind_user_login($data){
        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('非法操作','b2'));

        $type = self::check_login_name($user_id);
        if(!$type) return array('error'=>__('您不需要此操作','b2'));

        if($type !== 'login'){
            $check = self::check_verify_code($data);
            if(isset($check['error'])) return $check;

            $repass = User::edit_pass($data['password'],$data['password'],$user_id);
            if(isset($repass['error'])) return $repass;

            $data['user_id'] = $user_id;
            $res = User::save_username($data);
            if(isset($res['error'])) return $res;

            return $check;
        }else{

            //检查用户名
            $check = self::check_username($data['username']);
            if(isset($check['error'])) return $check;

            $repass = User::edit_pass($data['password'],$data['password'],$user_id);
            if(isset($repass['error'])) return $repass;

            $data['user_id'] = $user_id;

            if(is_email($data['username'])){
                return array('error'=>__('请不要填写邮箱','b2'));
            }else{
    
                global $wpdb;
                $res = $wpdb->update($wpdb->users, array('user_login' =>esc_sql($data['username'])), array('ID' => (int)$data['user_id']));
                wp_cache_delete($data['user_id'], 'users');
            }
    
            if(is_wp_error($res)){
                return array('error'=>$res->get_error_message());
            }

            return $data['username'];
        }

        return true;
    }

    public static function is_phone($mobile) {
        return is_numeric($mobile) ? true : false;
    }

    public static function send_email_code($code,$email){

        $site_name = B2_BLOG_NAME;
        $subject = '['.$site_name.']'.__('：请查收您的验证码','b2');

        $message = '<div style="width:700px;background-color:#fff;margin:0 auto;border: 1px solid #ccc;">
            <div style="height:64px;margin:0;padding:0;width:100%;">
                <a href="'.B2_HOME_URI.'" style="display:block;padding: 12px 30px;text-decoration: none;font-size: 24px;letter-spacing: 3px;border-bottom: 1px solid #ccc;" rel="noopener" target="_blank">
                    '.$site_name.'
                </a>
            </div>
            <div style="padding: 30px;margin:0;">
                <p style="font-size:14px;color:#333;">
                    '.__('您的邮箱为：','b2').'<span style="font-size:14px;color:#333;"><a href="'.$email.'" rel="noopener" target="_blank">'.$email.'</a></span>'.__('，验证码为：','b2').'
                </p>
                <p style="font-size:34px;color: green;">'.$code.'</p>
                <p style="font-size:14px;color:#333;">'.__('验证码的有效期为5分钟，请在有效期内输入！','b2').'</p>
                <p style="font-size:14px;color: #999;">— '.$site_name.'</p>
                <p style="font-size:12px;color:#999;border-top:1px dotted #E3E3E3;margin-top:30px;padding-top:30px;">
                    '.__('本邮件为系统邮件不能回复，请勿回复。','b2').'
                </p>
            </div>
        </div>';

        $send = wp_mail( $email, $subject, $message ,array('Content-Type: text/html; charset=UTF-8'));

        if(!$send){
            return array('error'=>__('验证码发送失败，请联系管理员','b2'));
        }

        //对验证码和邮箱地址进行加密
        $issuedAt = time();
        $expire = $issuedAt + 300;//5分钟时效

        $token = array(
            "iss" => B2_HOME_URI,
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            'exp'=>$expire,
            'data'=>array(
                'code'=>md5(md5(AUTH_KEY.strtolower($code))),
                'username'=>$email
            )
        );

        $jwt = JWT::encode($token, AUTH_KEY);

        return $jwt;
    }

    public static function send_sms_code($code,$phone,$token){
        return Sms::send_code($code,$phone,$token);
    }

    public static function send_code($request){

        //将 token 存入缓存，防止重复提交，
        if(wp_using_ext_object_cache() && $request['token']){
            $isset_token = wp_cache_get(md5($request['token'].'1'));
            if($isset_token) return array('error'=>__('请不要重复提交','b2'));
        }

        if(!self::is_phone($request['username']) && !is_email($request['username'])){
            
            $user = get_user_by('login',$request['username']);
            if($user){
                $request['username'] = $user->user_email;
            }
        }

        //如果用自带验证，不发送短信或邮件
        $check_type = b2_get_option('normal_login','check_type');

        //if(($check_type == 'text' || $check_type == 'luo') && $request['loginType'] != 3) return true;

        if($request['loginType'] != 3){
            //检查用户名
            $username = self::check_username($request['username']);
            if(isset($username['error'])){
                return $username;
            }
        }elseif(!email_exists($request['username']) && !username_exists($request['username'])){
            return array('error'=>__('不存在此邮箱或手机号码，请重新输入'));
        }

        //检查验证码
        $res = self::code_check($request);
        if(isset($res['error'])){
            return $res;
        }

        //缓存token，防止重复注册
        if(wp_using_ext_object_cache() && $request['token']){
            wp_cache_add(md5($request['token'].'1'),'1','',300);
        }

        if(is_email($request['username'])){
            return self::send_email_code(rand(100000,999999),$request['username']);
        }

        if(self::is_phone($request['username'])){
            return self::send_sms_code(rand(100000,999999),$request['username'],$request['smsToken']);
        }

    }

    public static function luosimao($code){
        if(!$code){
            return array('error'=>__('请点击验证','b2'));
        }

        $res = wp_remote_get('https://captcha.luosimao.com/api/site_verify',array(
            'body'=>array(
                'api_key'=>b2_get_option('normal_login','api_key'),
                'response'=>$code
            )
        ));

        $res = json_decode($res['body'],true);

        if($res['error'] === 0){
            return $code;
        }else{
            return array('error'=>__('验证码错误代号：','b2').$res['error']); 
        }

    }

    /**
     * 检查图形验证码或第三方验证
     *
     * @param object $data
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function code_check($data){

        $check_type = b2_get_option('normal_login','check_type');

        if(($check_type === 'luo' || B2_VERIFY_CODE != 'normal') && $data['loginType'] != 3){
            return self::luosimao($data['img_code']);
        }else{
            if($data['img_code'] == '' || $data['token'] == '') return array('error'=>__('请输入验证码','b2'));

            if(wp_using_ext_object_cache() && $data['token']){
                $isset_token = wp_cache_get(md5($data['token'].'2'));
                //return array('error'=>$isset_token);
                if($isset_token && $isset_token > 5) return array('error'=>__('请不要重复提交','b2'));
            }    

            try{
                //检查验证码
                $decoded = JWT::decode($data['token'], AUTH_KEY,array('HS256'));

                if(!isset($decoded->data->value)){
                    return array('error'=>__('验证码错误','b2'));
                }elseif($decoded->data->value !== md5(md5(AUTH_KEY.strtolower($data['img_code'])))){
                    if(wp_using_ext_object_cache() && $data['token']){
                        $isset_token = wp_cache_get(md5($data['token'].'2'));
                        if($isset_token){
                            wp_cache_set(md5($data['token'].'2'),$isset_token+1,'',180);
                        }else{
                            wp_cache_add(md5($data['token'].'2'),'1','',180);
                        }
                    }
                    return array('error'=>__('验证码错误','b2'));
                }

         
                return true;

            }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
                return array('error'=>__('验证码过期失效','b2'));
            }catch(\Exception $e) {  //其他错误
                return array('error'=>__('验证码错误','b2'));
            }

            return array('error'=>__('异常错误','b2'));
        }

    }

    /**
     * 新用户注册验证
     *
     * @param object $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function regeister($request){
        
        //将 token 存入缓存，防止重复提交，
        if(wp_using_ext_object_cache() && $request['token']){
            $isset_token = wp_cache_get(md5($request['token']));
            if($isset_token) return array('error'=>__('请不要重复提交','b2'));
        }
        
        //检查是否允许注册
        if(!b2_get_option('normal_login','allow_register')){
            return array('error'=>__('注册已关闭','b2'));
        }
        
        //获取验证方式
        $check_type = b2_get_option('normal_login','check_type');

        //邀请码为空检查
        $invitation_required = b2_get_option('invitation_main','required');
        if($invitation_required == 2 && $request['invitation_code'] == ''){
            return array('error'=>__('请使用邀请码注册','b2'));
        }

        if($invitation_required == 0 && $request['invitation_code'] != ''){
            return array('error'=>__('不允许使用邀请码','b2'));
        }
        
        $check_invitation = '';
        if($request['invitation_code'] != ''){
            //检查邀请码
            $check_invitation = Invitation::invitationCheck($request['invitation_code']);
            if(isset($check_invitation['error'])){
                return $check_invitation;
            }
        }
        
        //检查昵称
        $nickname = self::check_nickname($request['nickname']);
        if(isset($nickname['error'])){
            return $nickname;
        }
        
        //检查用户名
        $username = self::check_username($request['username']);
        if(isset($username['error'])){
            return $username;
        }
        
        //检查密码
        if(strlen($request['password']) < 6){
            return array('error'=>__('密码必须大于6位','b2'));
        }

        //如果开启了邮箱或者手机验证，检查短信验证码
        if($check_type == 'tel' || $check_type == 'email' || $check_type == 'telandemail'){
            $verify_code = self::check_verify_code($request);
            if(isset($verify_code['error'])){
                return array('error'=>$verify_code['error']);
            }
        }
        
        //检查图形验证码
        if($check_type === 'text' || $check_type === 'luo'){
            $res = self::code_check($request);
            if(isset($res['error'])){
                return $res;
            }
        }

        return self::regeister_action($request,$check_type,$check_invitation);
    }

    /**
     * 添加新用户
     *
     * @param [type] $data
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function regeister_action($data,$check_type,$check_invitation){

        $count = b2_get_option('normal_safe','register_count');

        $ip = b2_get_user_ip();

        $has_register_count = (int)wp_cache_get('b2_register_limit_'.md5($ip),'b2_register_limit');

        if($has_register_count >= $count) return array('error'=>__('非法操作','b2'));

        if(is_email($data['username'])){
            $user_id = wp_create_user(md5($data['username']).rand(1,9999), $data['password']);
        }else{
            $user_id = wp_create_user($data['username'], $data['password']);
        }

        wp_cache_set('b2_register_limit_'.md5($ip),($has_register_count + 1),'b2_register_limit',HOUR_IN_SECONDS*3);

        if (is_wp_error($user_id)) {
            return array('error'=>$user_id->get_error_message());
        }

        //如果是邮箱注册，更换一下用户的登录名
        $rand = rand(100,999);
        $email = '';
        if(is_email($data['username'])){
            $email = $data['username'];
            global $wpdb;
            $wpdb->update($wpdb->users, array('user_login' => 'user'.$user_id.'_'.$rand), array('ID' => (int)$user_id));
            $data['username'] = 'user'.$user_id.'_'.$rand;
        }

        //删除用户默认昵称
        delete_user_meta($user_id,'nickname');

        //更新昵称和邮箱
        $arr = array(
            'display_name'=>$data['nickname'],
            'ID'=>$user_id,
            'user_email'=>is_email($email) ? $email : $data['username'].'@'.get_option('wp_site_domain')
        );
        wp_update_user($arr);

        //获取 token
        $token = '';
        if(class_exists('Jwt_Auth_Public')){
            $request = new \WP_REST_Request( 'POST','/wp-json/jwt-auth/v1/token');
            $request->set_query_params(array(
                'username' => $data['username'],
                'password' => $data['password']
            ));
            
            $JWT = new \Jwt_Auth_Public('jwt-auth', '1.1.0');
            $token = $JWT->generate_token($request);
          
            if(is_wp_error($token)){
                return array('error'=>__('注册成功，登录失败，请重新登录','b2'));
            }
        }

        //缓存token，防止重复注册
        if(wp_using_ext_object_cache() && $data['token']){
            wp_cache_add(md5($data['token']),'1','',300);
        }

        //邀请码使用
        if($check_invitation){
            Invitation::useInv($user_id,$check_invitation['id']);

            //邀请码的积分
            $credit = $check_invitation['invitation_credit'];
            // $total = Credit::credit_change($user_id,$credit);

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$user_id,
                'gold_type'=>0,
                'no'=>$credit,
                'msg'=>sprintf(__('使用邀请码注册奖励，邀请码为：%s','b2'),$check_invitation['invitation_nub']),
                'type'=>'inv',
                'type_text'=>__('邀请注册奖励','b2')
            ]);

            //积分记录
            // Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>46,
            //     'msg_read'=>0,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>$check_invitation['invitation_owner'],
            //     'msg_credit'=>$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>'',
            //     'msg_value'=>''
            // ));
        }

        do_action('b2_user_regeister',$user_id);

        // do_action( 'user_register',$user_id,get_userdata($user_id));

        if($token){
            return apply_filters('b2_regeister', $token,array('user_id'=>$user_id,'invitation_id'=>$check_invitation));
        }else{
            return array('error'=>__('登陆失败','b2'));
        }
    }

    /**
     * 检查短信验证码
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function check_verify_code($request){

        if(!isset($request['smsToken']) || !isset($request['code'])) return array('error'=>__('请输入验证码','b2'));
        if(!$request['smsToken'] || !$request['code']) return array('error'=>__('请输入验证码','b2'));

        //检查smstoken
        try{
            //缓存token，防止重复注册
            if(wp_using_ext_object_cache() && $request['smsToken']){
                $isset_token = wp_cache_get(md5($request['smsToken'].'3'));
                //return array('error'=>$isset_token);
                if($isset_token && $isset_token > 5) return array('error'=>__('请不要重复提交','b2'));
            }    

            //检查验证码
            $decoded = JWT::decode($request['smsToken'], AUTH_KEY,array('HS256'));

            //检查用户名
            if(!isset($decoded->data->username) || $decoded->data->username != $request['username']){
                return array('error'=>__('用户名错误','b2'));
            }

            if(!isset($decoded->data->code) || $decoded->data->code != md5(md5(AUTH_KEY.strtolower($request['code'])))){
                if(wp_using_ext_object_cache() && $request['smsToken']){
                    $isset_token = wp_cache_get(md5($request['smsToken'].'3'));
                    if($isset_token){
                        wp_cache_set(md5($request['smsToken'].'3'),$isset_token+1,'',180);
                    }else{
                        wp_cache_add(md5($request['smsToken'].'3'),'1','',180);
                    }
                }
                return array('error'=>__('验证码错误','b2'));
                
            }
            
            return $decoded->data->username;

        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期

            return array('error'=>__('验证码过期失效','b2'));

        }catch(\Exception $e) {  //其他错误

            return array('error'=>__('验证码错误','b2'));

        }

        return array('error'=>__('异常错误','b2'));
    }

    public static function check_username($username){
        if($username == '') return array('error'=>__('请输入邮箱或手机号码','b2'));

        $check_type = b2_get_option('normal_login','check_type');

        switch ($check_type) {
            case 'email':
                if(!is_email($username)){
                    return array('error'=>__('您输入的不是邮箱','b2'));
                }
                break;
            case 'tel':
                if(!self::is_phone($username)){
                    return array('error'=>__('您输入的不是手机号码','b2'));
                }
                break;
            case 'telandemail':
                if(!is_email($username) && !self::is_phone($username)){
                    return array('error'=>__('您输入的不是邮箱或手机号码','b2'));
                }
                break;
        }
        
        if(is_email($username) && email_exists($username)){
            return array('error'=>__('该邮箱已被注册','b2'));
        }
        
        if(self::is_phone($username) && username_exists($username)){
            return array('error'=>__('该手机号码已被注册','b2'));
        }

        if(username_exists($username)){
            return array('error'=>__('该用户名已被注册','b2'));
        }

        if($check_type == 'text' || $check_type == 'luo'){
            if (!preg_match("/^[a-z\d]*$/i",$username) && !is_email($username)) {
                return array('error'=>__('用户名只能使用字母和（或）数字','b2'));
            }
        }

        return str_replace(array('{{','}}'),'',$username);
    }

    public static function strLength($str, $charset = 'utf-8') {
        if ($charset == 'utf-8')
          $str = iconv ( 'utf-8', 'gb2312', $str );
        $num = strlen ( $str );
        $cnNum = 0;
        for($i = 0; $i < $num; $i ++) {
          if (ord ( substr ( $str, $i + 1, 1 ) ) > 127) {
            $cnNum ++;
            $i ++;
          }
        }
        $enNum = $num - ($cnNum * 2);
        $number = ($enNum / 2) + $cnNum;
        return ceil ( $number );
    }

    public static function match_chinese($chars,$encoding='utf8'){
        $pattern =($encoding=='utf8')?'/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u':'/[\x80-\xFF]/';
        preg_match_all($pattern,$chars,$result);
        return join('',$result[0]);
    }

    public static function create_code($url){
        $url=crc32($url);
        $result=sprintf("%u",$url);
        return self::code62($result);
    }

    public static function code62($x){
        $show='';
        while($x>0){
            $s=$x % 62;
            if ($s>35){
                $s=chr($s+61);
            }elseif($s>9&&$s<=35){
                $s=chr($s+55);
            }
            $show.=$s;
            $x=floor($x/62);
        }
        return $show;
    }

    public static function microtime_float(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function check_nickname($nickname){

        $nickname = sanitize_text_field($nickname);

        $nickname = self::match_chinese($nickname);

        $nickname = str_replace(array('{{','}}'),'',wp_strip_all_tags($nickname));

        if(!$nickname){
            $nickname = self::create_code(self::microtime_float());
        }

        $censor = apply_filters('b2_text_censor', $nickname);
        if(isset($censor['error'])) return $censor;

        if(self::strLength($nickname) > 8) return array('error'=>__('昵称太长了！最多8个字符','b2'));

        //检查昵称是否重复
        global $wpdb;
        $table_name = $wpdb->prefix . 'users';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE display_name = %s", 
            $nickname
        ));

        if($result){
            $nickname = self::create_code(self::microtime_float());
        }
        
        return $nickname;
    }

    /**
     * 重设
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function rest_pass($request){

        if($request['password'] == '') return array('error'=>__('请输入密码','b2'));

        if($request['confirmPassword'] == '') return array('error'=>__('请输入确认密码','b2'));

        if($request['password'] !== $request['confirmPassword']){
            return array('error'=>__('两次密码不同，请重新输入','b2'));
        }

        if(strlen($request['password']) < 6){
            return array('error'=>__('密码必须大于6位','b2'));
        }

        //再次检查验证码
        $verify_code = self::check_verify_code($request);
        if(isset($verify_code['error'])){
            return array('error'=>$verify_code['error']);
        }

        //token检查
        try{
            //检查验证码
            $decoded = JWT::decode($request['smsToken'], AUTH_KEY,array('HS256'));

            if(!isset($decoded->data->username) || $decoded->data->username != $request['username']){
                return array('error'=>__('用户名错误','b2'));
            }
            
            if(!isset($decoded->data->code) || $decoded->data->code != md5(md5(AUTH_KEY.strtolower($request['code'])))){
                return array('error'=>__('验证码错误','b2'));
            }

            $username = $decoded->data->username;

    
            if(is_email($username)){
                $user = get_user_by( 'email', $username);
                $user_id = $user->ID;
            }else{
                $user = get_user_by('login',$username);
                $user_id = $user->ID;
            }

            wp_set_password($request['confirmPassword'], $user_id );
            return true;
        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return array('error'=>__('验证码过期失效','b2'));
        }catch(\Exception $e) {  //其他错误
            return array('error'=>__('验证码错误','b2'));
        }

        return array('error'=>__('异常错误','b2'));
    }

    /**
     * 忘记密码，检查绑定的邮箱或手机号码
     *
     * @param [type] $request
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function forgot_pass($request){

        if(!self::is_phone($request['username']) && !is_email($request['username'])){
            
            $user = get_user_by('login',$request['username']);
            if($user){
                $request['username'] = $user->user_email;
            }
        }

        if(!email_exists($request['username']) && !username_exists($request['username'])){
            return array('error'=>__('不存在此邮箱或手机号码，请重新输入','b2'));
        }

        if(!is_email($request['username']) && !self::is_phone($request['username'])){
            return array('error'=>__('需要您绑定了邮箱或者手机号码方可找回密码','b2'));
        }

        //如果开启了邮箱或者手机验证，检查短信验证码
        $verify_code = self::check_verify_code($request);
        if(isset($verify_code['error'])){
            return array('error'=>$verify_code['error']);
        }

        return true;
        
    }
}