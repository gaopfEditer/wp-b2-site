<?php namespace B2\Modules\Common;

use \Firebase\JWT\JWT;

class OAuth{

    public static function init($type,$code,$juhe = 0){

        if((int)$juhe == 1){
            return self::juhe($type,$code);
        }


        if(strpos($type,'wx_') !== false){
            $type = 'weixin';
        }

        return self::$type($type,$code);
    }

    public static function juhe($type,$code){

        $appid = trim(b2_get_option('normal_login','juhe_appid'), " \t\n\r\0\x0B\xC2\xA0");
        $appkey = trim(b2_get_option('normal_login','juhe_appkey'), " \t\n\r\0\x0B\xC2\xA0");
        $url = rtrim(trim(b2_get_option('normal_login','juhe_url'), " \t\n\r\0\x0B\xC2\xA0"),'/');

        $res = wp_remote_post($url.'/connect.php?act=callback&appid='.$appid.'&appkey='.$appkey.'&type='.$type.'&code='.$code);
        if(is_wp_error($res)){
            return ['error'=>__('登录失败，请联系管理员','b2')];
        }

        if($res['response']['code'] == 200){
            $data = json_decode($res['body'],true);

            if(isset($data['code']) && (int)$data['code'] !== 0){
                return ['error'=>__('登录失败，请联系管理员','b2')];
            }

            $type = $data['type'] == 'qq' ? 'juheqq' : ($data['type'] == 'baidu' ? 'juhebaidu' : $data['type']);
            
            return self::social_check([
                'access_token'=>$data['access_token'],
                'uid'=>$data['social_uid'],
                'type'=>$type,
                'juhe'=>1,
                'user_info'=>[
                    'type'=>$type,
                    'nickname' => $data['nickname'],
                    'avatar' => $data['faceimg'],
                    'sex' => $data['gender'] == '男' ? 1 : 0
                ]
            ]);
            
        }

        return ['error'=>__('登录失败','b2')];
    }

    public static function qq($type,$code){

        $arg = array(
            'url'=>"https://graph.qq.com/oauth2.0/token",
            'client_id'=>b2_get_option('normal_login','qq_id'),
            'client_secret'=>b2_get_option('normal_login','qq_secret')
        );

        return self::get_token($arg,$type,$code);
    }

    public static function weibo($type,$code){

        $arg = array(
            'url'=>"https://api.weibo.com/oauth2/access_token",
            'client_id'=>b2_get_option('normal_login','weibo_id'),
            'client_secret'=>b2_get_option('normal_login','weibo_secret')
        );

        return self::get_token($arg,$type,$code);
    }

    public static function weixin($type,$code){
        
        $arg = array(
            'appid'=>b2_get_option('normal_login','wx_pc_key'),
            'secret'=>b2_get_option('normal_login','wx_pc_secret')
        );
        
        if(b2_is_weixin()){
            $arg = array(
                'appid'=>b2_get_option('normal_login','wx_gz_key'),
                'secret'=>b2_get_option('normal_login','wx_gz_secret')
            );
        }

        $arg['url'] = "https://api.weixin.qq.com/sns/oauth2/access_token";

        return self::get_token($arg,$type,$code);
    }

    public static function get_token($arg,$type,$code){

        $arg['code'] = $code;
        $arg['grant_type'] = 'authorization_code';
        $arg['redirect_uri'] = B2_HOME_URI.'/open?type='.$type;

        if($type == 'weixin'){
            $arg['redirect_uri'] = str_replace(array('http://','https://'),'',B2_HOME_URI);
        }

        $res = wp_remote_post($arg['url'], 
            array(
                'method' => 'POST',
                'body' => $arg,
            )
        );

        if(is_wp_error($res)){
            return array('error'=>$res->get_error_message());
        }

        $data = array();
        switch ($type) {
            case 'qq';
                if(strpos($res['body'], "callback") !== false){
                    $lpos = strpos($res['body'], "(");
                    $rpos = strrpos($res['body'], ")");
                    $res  = substr($res['body'], $lpos + 1, $rpos - $lpos -1);
                    $msg = json_decode($res);
                    if(isset($msg->error)){
                        return array('error'=>sprintf(__('错误代码：%s；错误信息：%s；请在百度中搜索相关错误代码进行修正。','b2'),$msg->error,$msg->error_description));
                    }
                }
                $params = array();
                parse_str($res['body'], $params);

                $res = wp_remote_get("https://graph.qq.com/oauth2.0/me?access_token=" .$params['access_token']);

                if(is_wp_error($res)){
                    return array('error'=>$res->get_error_message());
                }

                $res = $res['body'];

                if (strpos ( $res, "callback" ) !== false) {
                    $lpos = strpos ( $res, "(" );
                    $rpos = strrpos ( $res, ")" );
                    $res = substr ( $res, $lpos + 1, $rpos - $lpos - 1 );
                }

                $res = json_decode ($res,true);
                if (isset ( $res->error )) {
                    return array('error'=>sprintf(__('错误代码：%s；错误信息：%s；请在百度中搜索相关错误代码进行修正。','b2'),$msg->error,$msg->error_description));
                }

                $data = array(
                    'access_token'=>$params['access_token'],
                    'uid'=>$res['openid'],
                    'type'=>'qq'
                );
                break;
            case 'weibo';
                $msg = json_decode($res['body'],true);
                if(isset($msg['error'])){
                    return array('error'=>sprintf(__('错误代码：%s；错误信息：%s；请在百度中搜索相关错误代码进行修正。','b2'),$msg['error'],$msg['error_description']));
                }
                $data = array(
                    'access_token'=>$msg['access_token'],
                    'uid'=>$msg['uid'],
                    'type'=>'weibo'
                );
                break;
            case 'weixin';
                $msg = json_decode($res['body'],true);
                
                if(isset($msg['errcode'])){
                    return array('error'=>sprintf(__('错误代码：%s；错误信息：%s；请在百度中搜索相关错误代码进行修正。','b2'),$msg['errcode'],$msg['errmsg']));
                }
                $data = array(
                    'access_token'=>$msg['access_token'],
                    'uid'=>$msg['openid'],
                    'unionid'=>isset($msg['unionid']) && $msg['unionid'] != '' ? $msg['unionid'] : '',
                    'type'=>'weixin'
                );
                break;
        }
        $data['type'] = $type;

        return self::social_check($data);
    }

    public static function save_mp_openid($data,$user_id){

        if($data['ismp']){
            $mp_weixin_openid = get_user_meta($user_id,'zrz_mpweixin_uid');
            if(!$mp_weixin_openid){
                update_user_meta($user_id,'zrz_mpweixin_uid',$data['uid']);
                update_user_meta($user_id,'zrz_mpweixin_pay_uid',$data['uid']);
            }
        }
    }

    public static function social_check($data){

        
        $user_id = b2_get_current_user_id();

        $name = '';

        switch ($data['type']) {
            case 'qq':
            case 'juheqq':
                $name = __('QQ','b2');
                break;
            case 'wx':
            case 'weixin':
                $name = __('微信','b2');
                break;
            case 'sina':
            case 'weibo':
                $name = __('微博','b2');
                break;
            case 'baidu':
            case 'juhebaidu':
                $name = __('百度','b2');
                break;
            case 'alipay':
                $name = __('支付宝','b2');
                break;
            case 'huawei':
                $name = __('华为','b2');
                break;
            case 'xiaomi':
                $name = __('小米','b2');
                break;
            case 'dingtalk':
                $name = __('钉钉','b2');
                break;
            case 'facebook':
                $name = __('Facebook','b2');
                break;
            case 'twitter':
                $name = __('Twitter','b2');
                break;
            case 'google':
                $name = __('Google','b2');
                break;
            case 'github':
                $name = __('Github','b2');
                break;
            case 'gitee':
                $name = __('Gitee','b2');
                break;
            case 'microsoft':
                $name = __('Microsoft','b2');
                break;
        }

        $data['ismp'] = isset($data['mpweixin']) && $data['mpweixin'];
        //已登录状态，直接绑定
        if($user_id){
            $user = self::check_binding($data);

            if($user){
                $name = get_the_author_meta('display_name',$user->ID);

                return array('error'=>array(
                    'msg'=>sprintf(__('此%s已经绑定到名为%s的账户中','b2'),$name,'['.$name.']'),
                    'oauth'=>b2_oauth_types(false,true),
                    'name'=>$name
                ));
            }else{
                //用户已登录，未绑定账户，执行绑定操作
                $res = self::get_info($data);
                
                if(isset($res['error'])){
                    return $res;
                }

                if(b2_is_weixin() && !$data['ismp']){
                    if(isset($data['unionid']) && $data['unionid']){
                        update_user_meta($user_id ,'zrz_weixin_uid',$data['uid']);
                    }
                    
                }

                if($data['ismp']){
                    self::save_mp_openid($data,$user->ID);
                }

                return self::binding($res,$user_id);
            }
        }else{
            $user = self::check_binding($data);

            //如果存在用户，直接登录
            if($user){

                if(b2_is_weixin() && !$data['ismp']){
                    if(isset($data['unionid']) && $data['unionid']){
                        update_user_meta($user->ID ,'zrz_weixin_uid',$data['uid']);
                    }
                }
                self::save_mp_openid($data,$user->ID);
                // return ['error'=>self::save_avatar($data,$user->ID)];
                return self::user_login($user->ID);
            }else{
                //是否允许注册
                if(!b2_get_option('normal_login','allow_register')){
                    return array('error'=>__('本站已关闭注册','b2'));
                }

                //如果用户不存在，执行注册操作
                $invitation = b2_get_option('invitation_main','required');
                if($invitation == 1 || $invitation == 2){
                    if(class_exists('Jwt_Auth_Public')){
                        $issuedAt = time();
                        $expire = $issuedAt + 300;//5分钟时效
                    
                        $token = array(
                            "iss" => B2_HOME_URI,
                            "iat" => $issuedAt,
                            "nbf" => $issuedAt,
                            'exp'=>$expire,
                            'data'=>$data
                        );

                        $jwt = JWT::encode($token, AUTH_KEY);

                        return array(
                            'type'=>'invitation',
                            'token'=>$jwt
                        );
                    }else{
                        return array('error'=>__('请安装 JWT Authentication for WP-API 插件','b2'));
                    }
                }else{
                    $data = self::get_info($data);

                    if(isset($data['error'])){
                        return $data;
                    }

                    return self::create_user($data);
                }
            }
        }
    }

    public static function object_to_array($obj){
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)self::object_to_array($v);
            }
        }
    
        return $obj;
    }

    public static function invitation_action($data){

        try{
            //检查验证码
            $decoded = JWT::decode($data['token'],AUTH_KEY,array('HS256'));

            if(!isset($decoded->data->uid) || !isset($decoded->data->type)){
                return array('error'=>__('参数错误','b2'));
            }
           
            $data = array(
                'access_token'=>isset($decoded->data->access_token) ? $decoded->data->access_token : '',
                'uid'=>$decoded->data->uid,
                'unionid'=>isset($decoded->data->unionid) ? $decoded->data->unionid : '',
                'type'=>$decoded->data->type,
                'invitation'=>$data['invitation'],
                'subType'=>isset($data['subType']) ? $data['subType'] : '',
                'user_info'=>isset($decoded->data->user_info) ? self::object_to_array($decoded->data->user_info) : [],
                'ismp'=>isset($decoded->data->ismp) ? $decoded->data->ismp : false,
                'juhe'=>isset($decoded->data->juhe) ? $decoded->data->juhe : false,
                'data'=>isset($decoded->data->data) ? self::object_to_array($decoded->data->data) : []
            );

            $data['data']['uid'] = $data['uid'];

        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return array('error'=>__('注册时间过期，请返回重新注册','b2'));
        }catch(\Exception $e) {  //其他错误
            return array('error'=>__('解码失败','b2'));
        }

        //检查是否绑定过，防止重复注册
        $user = self::check_binding($data);
        if($user){
            return self::user_login($user->ID);
        }

        $inv = b2_get_inv_settings();

        $check_invitation = false;

        //跳过
        if($inv['type'] == 1){
            if($data['subType'] == 'pass'){
               
                $data = self::get_info($data);

                if(isset($data['error'])){
                    return $data;
                }

                return self::create_user($data);
            }else{
                $check_invitation = Invitation::invitationCheck($data['invitation']);
                if(isset($check_invitation['error'])){
                    return $check_invitation;
                }
            }
        }

        if($inv['type'] == 2){
            if(empty($data['invitation'])){
                return array('error'=>__('请输入邀请码','b2'));
            }else{
                $check_invitation = Invitation::invitationCheck($data['invitation']);
                if(isset($check_invitation['error'])){
                    return $check_invitation;
                }
            }
        }

        if($inv['type'] == 0 && !empty($data['invitation'])){
            return array('error'=>__('不允许使用邀请码','b2'));
        }

        
        $data = self::get_info($data);

        if(isset($data['error'])){
            return $data;
        }

        return self::create_user($data,$check_invitation);

    }

    public static function create_user($data,$inv = '',$verify = false){

        if(isset($data['openid']) && $data['openid']){
            $data['uid'] = $data['openid'];
        }

        $user_id = wp_create_user((isset($data['unionid']) && !empty($data['unionid']) ? $data['unionid'] : $data['uid']), wp_generate_password());

        if(is_wp_error($user_id)) {
            return array('error'=>$user_id->get_error_message());
        }

        if(!isset($data['phoneNumber'])){
            //更换一下用户名
            global $wpdb;
            $wpdb->update($wpdb->users, array('user_login' => 'user'.$user_id.'_'.rand(100,999)), array('ID' => $user_id));

            //删除用户默认昵称
            delete_user_meta($user_id,'nickname');
        }else{
            $data['nickname'] = 'user'.$user_id.'_'.rand(100,999);
        }

        //昵称过滤掉特殊字符
        $nickname = self::replace_special_char($data['nickname']);

        //检查昵称是否重复
        global $wpdb;
        $table_name = $wpdb->prefix . 'users';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE display_name = %s", 
            $nickname
        ));

        $email = 'user'.$user_id.'_'.rand(100,999).'@'.get_option('wp_site_domain');

        if($nickname){
            if($result){
                $arr = array(
                    'display_name'=>$nickname.$user_id,
                    'ID'=>$user_id,
                    'user_email'=>$email
                );
            }else{
                $arr = array(
                    'display_name'=>$nickname,
                    'ID'=>$user_id,
                    'user_email'=>$email
                );
            }
        }else{
            $arr = array(
                'display_name'=>'user'.$user_id,
                'ID'=>$user_id,
                'user_email'=>$email
            );
        }
        wp_update_user($arr);

        //如果使用邀请码
        if($inv){
            //使用邀请码
            Invitation::useInv($user_id,$inv['id']);

            //邀请码的积分
            $credit = $inv['invitation_credit'];

            // $total = Credit::credit_change($user_id,$credit);

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$user_id,
                'gold_type'=>0,
                'no'=>$credit,
                'msg'=>sprintf(__('使用邀请码注册奖励，邀请码为：%s','b2'),$inv['invitation_nub']),
                'type'=>'inv',
                'type_text'=>__('邀请注册奖励','b2')
            ]);

            //积分记录
            // Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>46,
            //     'msg_read'=>0,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>$inv['invitation_owner'],
            //     'msg_credit'=>$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>'',
            //     'msg_value'=>''
            // ));
        }

        if($verify){
            Wecatmp::add_verify($user_id);
        }

        //绑定用户数据
        self::binding($data,$user_id);
        
        if(b2_is_weixin() && !$data['ismp']){
            if(isset($data['unionid']) && $data['unionid']){
                update_user_meta($user_id ,'zrz_weixin_uid',$data['uid']);
            }
        }

        do_action('b2_user_regeister',$user_id);

        //返回用户数据
        return self::user_login($user_id);
    }

    public static function replace_special_char($strParam){
        $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex,'',$strParam);
    }

    public static function user_login($user_id){
        
        if(class_exists('Jwt_Auth_Public')){
            $user_data = get_user_by('id',$user_id);
            
            $request = new \WP_REST_Request( 'POST','/wp-json/jwt-auth/v1/token');
            $request->set_query_params(array(
                'username' => $user_data->data->user_login,
                'password' => $user_data->data->user_pass
            ));

            $JWT = new \Jwt_Auth_Public('jwt-auth', '1.1.0');
            $token = $JWT->generate_token($request);

            if(is_wp_error($token)){
                return array('error'=>$token->get_error_message());
            }

            do_action('b2_user_social_login', $user_id);

            return $token;
        }else{
            return array('error'=>__('请安装 JWT Authentication for WP-API 插件','b2'));
        }
    }

    public static function save_avatar($data,$user_id){
        $user_data = get_user_meta($user_id,'zrz_open',true);
        $user_data = is_array($user_data) ? $user_data : array();

        $upload_url = apply_filters('b2_upload_path_arg',wp_upload_dir());

        $avatar = isset($user_data[$data['type'].'_avatar_new']) ? $user_data[$data['type'].'_avatar_new'] : '';
 
        if(isset($upload_url['baseurl'])){
            if(strpos($avatar,$upload_url['baseurl']) === false || !$avatar){
                if(isset($data['data']['avatar'])){
                    $user_data[$data['type'].'_avatar_new'] = self::download_remote_image($data['data']['avatar'],$user_id);
                }else{
                    $user_data[$data['type'].'_avatar_new'] = self::download_remote_image($data['avatar'],$user_id);
                }


                update_user_meta($user_id,'zrz_open',$user_data);
            }
        }

        return $data;
    }

    //执行绑定操作
    public static function binding($data,$user_id){

        if(isset($data['avatarUrl'])){
            $data['avatar'] = $data['avatarUrl'];
        }

        if(isset($data['nickName'])){
            $data['nickname'] = $data['nickName'];
        }

        $user_data = get_user_meta($user_id,'zrz_open',true);
        $user_data = is_array($user_data) ? $user_data : array();
        $user_data['avatar_set'] = $data['type'];
        
        // $upload_url = apply_filters('b2_upload_path_arg',wp_upload_dir());
        // if(isset($upload_url['baseurl'])){
        //     if(strpos($data['avatar'],$upload_url['baseurl']) === false){
        //         $data['avatar'] = self::download_remote_image($data['avatar'],$user_id);
        //     }
        // }

        $user_data[$data['type'].'_avatar_new'] = $data['avatar'];

        //存入头像
        update_user_meta($user_id,'zrz_open',$user_data);

        //存入id
        if(isset($data['unionid']) && $data['unionid'] != ''){
            update_user_meta($user_id,'zrz_'.$data['type'].'_unionid',$data['unionid']);
        }

        self::save_mp_openid($data,$user_id);

        //如果不是小程序，存uid
        if(!$data['ismp']){
            update_user_meta($user_id,'zrz_'.$data['type'].'_uid',$data['uid']);
        }
        
        do_action('b2_social_binding',$user_id,$data);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        
        return true;

    }

    //检查是否绑定
    public static function check_binding($data){

        if(isset($data['unionid']) && $data['unionid'] != ''){
            $user = get_users(array('meta_key'=>'zrz_'.$data['type'].'_unionid','meta_value'=>$data['unionid']));
        }else{
            if(isset($data['mpweixin']) && $data['mpweixin']){
                $user = get_users(array('meta_key'=>'zrz_mpweixin_uid','meta_value'=>$data['uid']));
            }elseif($data['type'] === 'weixin'){
                $user = get_users(array('meta_key'=>'zrz_weixin_open_id','meta_value'=>$data['uid']));
                if(empty($user)){
                    $user = get_users(array('meta_key'=>'zrz_weixin_uid','meta_value'=>$data['uid']));
                }
            }else{
                $user = get_users(array('meta_key'=>'zrz_'.$data['type'].'_uid','meta_value'=>$data['uid']));
            }
        }

        if(!empty($user)){
            return $user[0]->data;
        }else{
            return false;
        }
    }

    //下载远程头像图片到本地
    public static function download_remote_image($image_url, $user_id = 0) {

        wp_set_current_user($user_id);

        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        // 确保远程图片 URL 是有效的
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            return false;
        }
    
        // 下载图片并将其添加到媒体库
        $result = media_sideload_image($image_url,0, null, 'src');
    
        // 检查是否有错误
        if (is_wp_error($result)) {
            return false;
        }
    
        return $result;
    }

    public static function get_info($data){
        
    // return ['error'=>$data];
        if(isset($data['juhe']) && (int)$data['juhe'] == 1){
                
            $data['nickname'] = $data['user_info']['nickname'];
            $data['avatar'] = str_replace('http://','https://',$data['user_info']['avatar']);

            $data['sex'] = isset($data['sex']) ? $data['sex'] : 0;
            unset($data['user_info']);
            
            return $data;
        }

        if(!in_array($data['type'],array('qq','weibo','weixin','apple','baidu','toutiao'))) return array('error'=>__('参数错误','b2'));

        if(isset($data['user_info']['nickName']) && isset($data['user_info']['avatarUrl'])){
            
            $data['nickname'] = $data['user_info']['nickName'];
            $data['avatar'] = str_replace('http://','https://',$data['user_info']['avatarUrl']);

            $data['sex'] = 0;
            unset($data['user_info']);
        }else{
            switch ($data['type']) {
                case 'qq';
                    $url = 'https://graph.qq.com/user/get_user_info?access_token='.$data['access_token'].'&oauth_consumer_key=' .b2_get_option('normal_login','qq_id'). '&openid='.$data['uid'].'&format=json';
                    $data['nickname'] = 'nickname';
                    $data['avatar'] = 'figureurl_qq_2';
                    $data['avatar1'] = 'figureurl_qq_1';
                    $data['sex'] = 'gender';
                    break;
                case 'weibo':
                    $url = 'https://api.weibo.com/2/users/show.json?uid='.$data['uid'].'&access_token='.$data['access_token'];
                    $data['nickname'] = 'name';
                    $data['avatar'] = 'avatar_large';
                    $data['sex'] = 'gender';
                    break;
                case 'weixin':
                    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $data['access_token'] . '&openid=' . $data['uid'];
                    $data['nickname'] = 'nickname';
                    $data['avatar'] = 'headimgurl';
                    $data['sex'] = 'sex';
                    break;
            }

            $user = wp_remote_get($url);
            
            if(is_wp_error($user)){
                return array('error'=>$user->get_error_message());
            }
    
            $user = json_decode($user['body'],true);

            if(isset($user['ret']) && $user['ret'] != 0){
                return array('error'=>sprintf(__('错误代码：%s；错误信息：%s；请在百度中搜索相关错误代码进行修正。','b2'),$user['ret'],$user['msg']));
            }
    
            $avatar = $user[$data['avatar']];
    
            $data['nickname'] = $user[$data['nickname']];
            if($data['type'] === 'qq' && !$avatar){
                $avatar = $user[$data['avatar1']];
            }

            if($data['type'] === 'weibo' && $data['type'] === 'sina'){
                $avatar = '';
            }

            $data['avatar'] = str_replace('http://','https://',$avatar);

            $data['sex'] = isset($user[$data['sex']]) ? $user[$data['sex']] : 0;
        }
        

        return $data;
    }
}