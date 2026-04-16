<?php namespace B2\Modules\Common;

use B2\Modules\Common\Login;

class User{

    public $v;
    public static $upload_dir;

    public function init(){
        if(is_admin()){
            add_filter('default_avatar_select', array($this,'default_avatar_select_fn'));
        }
        add_filter('get_avatar_data',array($this,'get_avatar_cus'),1,2);

        add_filter('b2_order_callback_vip',array(__CLASS__,'update_vip_count'),5,2);

        add_filter('b2_order_callback_mission',array(__CLASS__,'reset_always_mission'),5,1);

        self::$upload_dir = apply_filters('b2_upload_path_arg',wp_upload_dir());

        add_action('user_register', [$this,'log_ip']); 
        add_action('b2_user_login', [$this,'insert_last_login']); 
    }

    // 创建一个新字段存储用户注册时的IP地址  
    public function log_ip($user_id){  
        $ip = b2_get_user_ip();  
        update_user_meta($user_id, 'signup_ip', $ip);  
    }  

    // 创建新字段存储用户登录时间和登录IP  
    public function insert_last_login( $user ) {  
        // update_user_meta( $user->data->ID, 'last_login', current_time( 'mysql' ) );  
        $last_login_ip = b2_get_user_ip();  
        $port = isset($_SERVER['REMOTE_PORT']) && $_SERVER['REMOTE_PORT'] ? ':'.$_SERVER['REMOTE_PORT'] : '';
        update_user_meta( $user->data->ID, 'last_login_ip', $last_login_ip.$port);  
    }  

    /**
     * 后台头像类型选择不使用字母头像
     *
     * @param array 头像列表
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function default_avatar_select_fn($avatar_list){

        global $avatar_defaults;

        $avatar = explode('<br />', $avatar_list );

        $content = '';

        $i = 0;
        foreach( $avatar_defaults as $default_key=>$default_value ){
            $content .= preg_replace( '/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '<img src="'.get_avatar_url('email@example.com', array('default'=>$default_key)).' class="avatar" width="32" height="32">', $avatar[$i] ) . '<br />';
            $i++;
        }

        return $content;
    }

    /**
     * 生成字母头像
     *
     * @param string $letter 用户名
     *
     * @return string 用户字母头像URL
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_letter_avatar($letter){

        return B2_DEFAULT_AVATAR;

        if(!function_exists('imagecreate')) return '';
    
        //获取文字
        $letter = self::get_last_letter($letter);

        $letter_md5 = md5(trim($letter));
    
        $avatar_dir = B2_DS.'avatar'.B2_DS.$letter_md5.'.png';

        if(is_file(self::$upload_dir['basedir'].$avatar_dir)){
            return apply_filters('b2_get_letter_avatar_url',self::$upload_dir['baseurl'].$avatar_dir);
        }
        
        $font_dir = apply_filters('b2_get_letter_avatar_font',B2_THEME_DIR.B2_DS.'Assets'.B2_DS.'fonts'.B2_DS.'avatar.ttf');

        //检查字体是否存在
        if(!is_file($font_dir)){
            return '';
        }
    
        //创建一个 100*100 的空白图像
		$image = imagecreatetruecolor(300,300);

		//背景颜色
		$bgcolor = imagecolorallocate($image,255,255,255);

		//设置透明
		imagecolortransparent($image,$bgcolor);

		//填充到图像中
		imagefill($image,0,0,$bgcolor);

        $avatar = B2_DEFAULT_AVATAR;
    
        $default = B2_THEME_DIR.B2_DS.'Assets'.B2_DS.'fontend'.B2_DS.'images'.B2_DS.'avatar-bg.png';

		$fontcolor = imagecolorallocate($image,215,215,215);

		//获取字体的真实宽高
		if(function_exists('imagettfbbox')){
			$box = imagettfbbox(180, 0, $font_dir, $letter);
			$txtw = $box[2];
			$txth = $box[1]+$box[7];
		}else{
			$txtw = $txth = 10;
		}

		//坐标
		$x = 148-($txtw/2);
		$y = 150-($txth/2);
		$anger = 0;

		//生成字体
		ImageTTFText($image,180,$anger,$x,$y,$fontcolor,$font_dir,$letter);

		ob_start();
		ImagePng($image);
		$image = ob_get_contents();
        ob_end_clean();
        
        $upload_file = '';
        if(wp_mkdir_p(self::$upload_dir['basedir'].B2_DS.'avatar')){
            $upload_file = file_put_contents(self::$upload_dir['basedir'].$avatar_dir, $image );
        }
        
		if($upload_file) {
			$avatar = self::$upload_dir['baseurl'].$avatar_dir;
		}

		return apply_filters('b2_get_letter_avatar_url',$avatar);

    }

    /**
     * 使用用户名的第一个字符还是最后一个字符
     *
     * @param string $letter 用户名
     *
     * @return string 第一个或者最后一个字符
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_last_letter($letter){

        if(b2_get_option('normal_user','user_avatar_letter') == 1){
            $name = mb_substr( $letter, 0 ,1,"utf-8");
        }else{
            $name = '/(?<!^)(?!$)/u';
            $name = preg_split($name, $letter );
            $name = end($name);
        }
        //如果不存在则返回文字“空”
        if(!$name && $name != 0){
            $name = __('空','b2');
        }

        return strtoupper($name);
    }

    //重写默认头像地址
    public function get_avatar_cus($args, $id_or_email) {

        if($id_or_email === 'email@example.com') return $args;

        if( is_object($id_or_email) ) {

            $user_id = (int)$id_or_email->user_id === 0 ? $id_or_email->comment_author : $id_or_email->user_id;

        }elseif(is_email($id_or_email)){

            $user_id = email_exists($id_or_email);

        }else{
            $user_id = $id_or_email;
        }

        $args = $this->get_avatar_url($user_id,$args);

        return $args;
    }

    //获取头像
    public function get_avatar_url($user_id,$args){
        
        if(is_numeric($user_id)){

            $args['url'] = apply_filters('b2_get_avatar_url', $user_id,$args['size'],false);

        }else{
            $args['url'] = B2_DEFAULT_AVATAR;
        }

        return $args;
    }

    //获取封面
    public static function get_cover_url($user_id){
        $open = get_user_meta($user_id,'zrz_open',true);

        if(isset($open['cover']['id'])){
            return wp_get_attachment_url($open['cover']['id']);
        }

        if(isset($open['cover']['key'])){
            return $open['cover']['key'];
        }

        return b2_get_option('normal_user','default_user_cover');
    }

    public static function get_user_normal_data($user_id){

        if(!$user_id) return array('error'=>__('无法获取用户信息','b2'));

        if(isset($GLOBALS['b2_user_normal_data_'.$user_id])) return $GLOBALS['b2_user_normal_data_'.$user_id];

        $data = get_userdata($user_id);
        $avatar = get_avatar_url($user_id,array('size'=>160));
        if($data){
            $user_normal_data = [
                'id'=>$user_id,
                'name'=>esc_attr($data->display_name),
                'link'=>get_author_posts_url($user_id),
                'avatar'=>$avatar,
                'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar)
            ];
    
            $GLOBALS['b2_user_normal_data_'.$user_id] = $user_normal_data;
    
            return apply_filters('b2_get_user_normal_data',$user_normal_data,$user_id);
        }else{
            return [
                'id'=>0,
                'name'=>__('未名','b2'),
                'link'=>B2_HOME_URI,
                'avatar'=>$avatar,
                'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar)
            ];
        }
        
    }

    //获取用户公开信息
    public static function get_user_public_data($user_id,$private = false){
        
        if(!$user_id) return array('error'=>__('无法获取用户信息','b2'));

        $cache = wp_cache_get( 'b2_user_'.$user_id, 'b2_user_data');
        
        if($cache){
            $cache['lv'] = self::get_user_lv($user_id,$private);
            return $cache;
        } 
        
        $data = get_userdata($user_id);

        $user_title = get_user_meta($user_id,'b2_title',true);

        $sex = get_user_meta($user_id,'zrz_user_custom_data',true);
        $sex = isset($sex['gender']) ? $sex['gender'] : 0;

        $page_width = b2_get_option('template_main','wrapper_width');
        $page_width = preg_replace('/\D/s','',$page_width);

        $cover = b2_get_thumb(array('thumb'=>self::get_cover_url($user_id),'type'=>'fill','width'=>$page_width,'height'=>$page_width*(11/55)));

        $avatar = get_avatar_url($user_id,array('size'=>160));

        $user_public_data = array(
            'id'=>$user_id,
            'sex'=>$sex,
            'name'=>isset($data->display_name) ? esc_attr($data->display_name) : '',
            'link'=>get_author_posts_url($user_id),
            'avatar'=>$avatar,
            'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar),
            'desc'=>isset($data->description) ? esc_attr($data->description) : '',
            'user_title'=>$user_title,
            'verify'=>$user_title,
            'verify_icon'=>B2_VERIFY_ICON,
            'cover'=>$cover,
            'lv' => self::get_user_lv($user_id,$private),
            'cover_webp'=>apply_filters('b2_thumb_webp',$cover)
        );

        $data = apply_filters('b2_get_user_public_data',$user_public_data,$user_id,$private);

        unset($data['lv']);

        wp_cache_set('b2_user_'.$user_id,$data,'b2_user_data',MINUTE_IN_SECONDS*10);

        $data['lv'] = $user_public_data['lv'];

        return $data;
    }

    public static function get_author_info($user_id){

        $user_id = (int)$user_id;
        
        $current_user_id = b2_get_current_user_id();

        $admin = user_can($current_user_id, 'administrator' );

        $self = $user_id == $current_user_id ? true : false;

        if($current_user_id){
            $following = get_user_meta($user_id,'zrz_followed',true);
            $following = is_array($following) ? $following : array();
            $key_following = array_search((int)$current_user_id,$following);
        }else{
            $key_following = false;
        }

        $follow = get_user_meta($user_id,'zrz_follow',true);
        $follow = is_array($follow) ? $follow : array();

        
        $data = array(
            'url'=>get_the_author_meta('user_url', $user_id),
            'self'=>$self ? true : false,
            'admin'=>$admin,
            'id'=>$self || $admin ? $user_id : 0,
            'money'=>$self || $admin ? self::get_money($user_id) : 0,
            'credit'=>$self || $admin ? self::get_credit($user_id) : 0,
            'followed'=>$key_following !== false ? true : false,
            'fans'=>count($following),
            'following'=>count($follow),
        );

        $public_data = self::get_user_public_data($user_id,true);

        unset($public_data['id']);

        $data = array_merge($data,$public_data);

        return apply_filters('b2_author_page_get_user_info', $data);
    }

    //保存封面
    public static function save_cover($url,$id,$user_id){
        if(!$url || !$id || !$user_id) return array('error'=>__('参数不全','b2'));
        
        $current_user_id = b2_get_current_user_id();

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));
        
        $open = get_user_meta($user_id,'zrz_open',true);
        $open = !empty($open) ? $open : array();
        
        $open['cover']['key'] = esc_url($url);
        $open['cover']['id'] = (int)$id;

        update_user_meta($user_id,'zrz_open',$open);

        do_action('b2_user_save_cover', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');

        return true;
    }

    //保存头像
    public static function save_avatar($url,$id,$user_id){
        if(!$url || !$id || !$user_id) return array('error'=>__('参数不全','b2'));
        
        $current_user_id = b2_get_current_user_id();

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));
        
        $open = get_user_meta($user_id,'zrz_open',true);
        $open = !empty($open) ? $open : array();
        
        $open['avatar_set'] = 'default';
        $open['avatar'] = (int)$id;

        update_user_meta($user_id,'zrz_open',$open);

        do_action('b2_user_save_avatar', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');

        $avatar = get_avatar_url($user_id,array('size'=>100));

        return array(
            'avatar'=>$avatar,
            'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar)
        );
    }

    /**
     * 获取所有设置项中的权限等级
     *
     * @return array 权限等级的数组
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_user_roles(){

        $settings = get_option('b2_normal_user');

        $settings_lv = isset($settings['user_lv_group']) ? (array)$settings['user_lv_group'] : array();
        $settings_vip = isset($settings['user_vip_group']) ? (array)$settings['user_vip_group'] : array();

        $_settings_lv = array();
        if(!empty($settings_lv)){
            foreach($settings_lv as $k => $v){
                if(!isset($v['name'])){
                    $v = [];
                    $v['name'] = __('未设置','b2');
                }
                $_settings_lv['lv'.$k] = $v;
            }
        }
        

        $_settings_vip = array();
        if(!empty($settings_vip)){
            foreach($settings_vip as $k => $v){
                if(!isset($v['name'])){
                    $v = [];
                    $v['name'] = __('未设置','b2');
                }
                $_settings_vip['vip'.$k] = $v;
            }
        }
        

        $settings = array_merge($_settings_vip,$_settings_lv);

        return $settings;
    }

    /**
     * 获取某个用户的等级
     *
     * @param int $user_id
     *
     * @return array [lv]:用户的普通等级；['vip']用户的付费等级
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_user_lv($user_id,$private = false){

        if(!$user_id){
            return array(
                'lv'=>array(
                    'name'=>__('游客','b2'),
                    'lv'=>'guest',
                    'credit'=>0,
                    'icon'=>apply_filters('b2_get_guest_icon', '<span class="lv-icon user-guest b2-guest"><i></i><b>'.__('游客','b2').'</b></span>',0),
                    'lv_next'=>1,
                    'lv_next_name'=>'',
                    'lv_next_credit'=>0
                ),
                'vip'=>array(
                    'name'=>'',
                    'lv'=>'',
                    'icon'=>'',
                    'color'=>''
                )
            );
        }

        $roles = self::get_user_roles();

        $lv = array(
            'vip'=>array(
                'name'=>'',
                'lv'=>'',
                'credit'=>0,
                'icon'=>'',
                'lv_next'=>'',
                'lv_next_name'=>'',
                'lv_next_credit'=>'',
                'color'=>''
            ),
            'lv'=>array(
                'name'=>'',
                'lv'=>'',
                'icon'=>'',
            )
        );

        //获取普通会员等级
        $user_lv = self::rebuild_user_lv($user_id);
        if(!$user_lv) $lv['lv'] = array(
            'name'=>'',
            'lv'=>'',
            'icon'=>''
        );
        
        //普通会员等级检查，如果不存在此等级，重新计算会员的等级
        if(isset($roles[$user_lv])){     
            $lv_next =  preg_replace('/\D/s','',$user_lv);
            $_lv_next = 'lv'.($lv_next+1);
            $credit = get_user_meta($user_id,'zrz_credit_total',true);
            $credit = $credit ? (int)$credit : 0;

            $next_credit = isset($roles[$_lv_next]['credit']) && (int)$roles[$_lv_next]['credit'] != 0 ? (int)$roles[$_lv_next]['credit'] : (int)$roles[$user_lv]['credit'];
          	
			if($credit >= $next_credit && !isset($roles[$lv_next + 2])){
              $credit_ratio = 100;
            }elseif($next_credit != 0){
                $credit_ratio = bcmul(round($credit/$next_credit,2),100,2);
            }else{
                $credit_ratio = 100;
            }

            $lv['lv'] = array(
                'name'=>$roles[$user_lv]['name'],
                'credit'=>b2_number_format($credit),
                'lv'=>$user_lv,
                'icon'=>self::get_lv_icon($user_lv),
                'lv_next'=>$_lv_next,
                'lv_next_name'=>isset($roles[$_lv_next]) ? $roles[$_lv_next]['name'] : $roles[$user_lv]['name'],
                'lv_next_credit'=>b2_number_format($next_credit),
                'lv_ratio'=>round($credit_ratio,2)
            );
        }

        //获取VIP用户等级
        $user_vip = get_user_meta($user_id,'zrz_vip',true);

        //VIP用户检查，如果是vip等级，并且不存在此等级，返回空
        if(!isset($roles[$user_vip])){
            $lv['vip'] = array(
                'name'=>'',
                'lv'=>'',
                'icon'=>'',
                'color'=>'',
                'time'=>0
            );
        }else{
            //如果用户会员已经过期，取消会员
            if(self::check_user_vip_time($user_id)){
                $lv['vip'] = array(
                    'name'=>'',
                    'lv'=>'',
                    'icon'=>'',
                    'color'=>'',
                    'time'=>0
                );
            }else{
                
                $time = get_user_meta($user_id,'zrz_vip_time',true);
                if(isset($time['end'])){
                    $time = $time['end'];
                    $time = $time == 0 ? 'long' : wp_date('Y-m-d H:i:s',$time);
                }

                $lv['vip'] = array(
                    'name'=>$roles[$user_vip]['name'],
                    'lv'=>$user_vip,
                    'icon'=>self::get_lv_icon($user_vip),
                    'color'=>isset($roles[$user_vip]['color']) ? $roles[$user_vip]['color'] : '#000000',
                    'time'=>$time
                );
            }
        }

        $dark_room = (int)get_user_meta($user_id,'b2_dark_room',true);
        if($dark_room){
            $lv['vip'] = array(
                'name'=>'',
                'lv'=>'',
                'icon'=>'',
                'color'=>'',
                'time'=>0
            );

            $lv['lv']['name'] = __('小黑屋','b2');
            $lv['lv']['lv'] = 'dark_room';
            $lv['lv']['icon'] = self::get_lv_icon('dark_room');
        }

        if($private){
            $lv['lv']['credit'] = 0;
            $lv['lv']['lv_next_credit'] = 0;
        }
        
        return $lv;
    }

    //判断用户是否允许查看所有的隐藏内容
    public static function check_user_can_read_all($user_id){

        //获取用户当前的等级
        $user_role = self::get_user_lv($user_id);
        $roles = self::get_user_roles();

        if(isset($roles[$user_role['vip']['lv']])){
            $vip_settings = $roles[$user_role['vip']['lv']];
            if(isset($vip_settings['allow_read']) && $vip_settings['allow_read'] == 1){
                return true;
            }
        }

        return false;
    }

    /**
     * 重建普通用户的等级
     *
     * @param int $user_id 用户ID
     *
     * @return string 重设的等级
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function rebuild_user_lv($user_id){
        
        //用户当前的积分
        $credit = get_user_meta($user_id,'zrz_credit_total',true);
        $credit = $credit ? $credit : 0;

        //获取等级设置数据
        $lvs = b2_get_option('normal_user','user_lv_group');
        if(!$lvs) return false;
        $_lv = array();

        foreach($lvs as $k => $v){
            if(!isset($v['credit'])) continue;
            if($v['credit'] < $credit || $v['credit'] == $credit){
                $_lv[$k] = $v['credit'];
            }
        }

        $lv = get_user_meta($user_id,'zrz_lv',true);

        if(empty($_lv) && $lv){
            return $lv;
        }

        if(empty($_lv)) return false;

        $max = max($_lv);

        foreach($_lv as $_k => $_v){
            if($_v == $max){
                $_lv = $_k;
                break;
            }
        }

        if($lv){
            preg_match_all('!\d+!', $lv, $matches);
            $lv = isset($matches[0][0]) ? $matches[0][0] : 0;
        }else{
            //重建等级
            update_user_meta($user_id,'zrz_lv','lv0');

            do_action('b2_user_rebuild_lv', $user_id);
            wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            return 'lv'.$_lv;
        }

        if((int)$lv < (int)$_lv){
            //重建等级
            update_user_meta($user_id,'zrz_lv','lv'.$_lv);

            do_action('b2_user_rebuild_lv', $user_id);
            wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            $lv = $_lv;
        }
        
        return 'lv'.$lv;
    }

    public static function get_current_role($post_id = 0){
        $user_id = b2_get_current_user_id();
        $post_author = get_post_field( 'post_author', $post_id );

        if($post_author){
            return user_can($user_id, 'administrator' ) || $user_id == $post_author ? true : false;
        }else{
            return user_can($user_id, 'administrator' ) ? true : false;
        }
    }

    public static function put_dark_room($user_id,$days = 0,$why = ''){
        update_user_meta($user_id,'b2_dark_room_start_date',current_time('mysql'));
        update_user_meta($user_id,'b2_dark_room_days',$days);

        $w = sprintf(__('，原因：%s','b2'),$why);

        $msg = __('您被关进了小黑屋%s天','b2');

        if($days == 0){
            $msg = __('您被永久关进了小黑屋','b2');
        }

        if($why){
            $msg .= $msg.$w;
        }

        Message::update_data([
            'date'=>current_time('mysql'),
            'from'=>0,
            'to'=>$user_id,
            'post_id'=>0,
            'msg'=>$msg,
            'type'=>'user_dark_room',
            'type_text'=>__('被关进小黑屋','b2')
        ]);

        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        delete_user_meta($user_id,'b2_verify_upload_count');

        return true;
    }

    public static function check_dark_room($user_id){
        $dark_room = (int)get_user_meta($user_id,'b2_dark_room',true);
        $dark_days = get_user_meta($user_id,'b2_dark_room_days',true);

        if($dark_days == 0) return false;

        if($dark_room === 1){
            $update_date = get_user_meta($user_id,'b2_dark_room_start_date',true);
            if(!$update_date){
                $update_date = get_user_meta($user_id,'b2_user_update_date',true);
            }

            if((( wp_strtotime(current_time( 'mysql' ))-wp_strtotime($update_date))/86400) > $dark_days){
                delete_user_meta($user_id,'b2_dark_room');
                delete_user_meta($user_id,'b2_dark_room_days');
                delete_user_meta($user_id,'b2_dark_room_start_date');
                delete_user_meta($user_id,'b2_dark_room_why');
                wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
                return false;
            }
        }

        return true;
    }

    /**
     * 检查VIP用户是否过期
     *
     * @param [type] $user_id 用户ID
     *
     * @return bool 如果为false 未过期，如果为true 过期
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function check_user_vip_time($user_id){

        $user_lv_time = get_user_meta($user_id,'zrz_vip_time',true);

        //var_dump(  $user_lv_time );

        //获取结束时间
        $vip_data = b2_get_option('normal_user','user_vip_group');

        $vip = get_user_meta($user_id,'zrz_vip',true);
        $vip = (string)preg_replace('/\D/s','',$vip);

        if(!isset($vip_data[$vip])) return true;

        $day = $vip_data[$vip];

        if(!isset($day['time'])) return true;
        $day = (int)$day['time'];

        //如果是永久会员
        if(isset($user_lv_time['end']) && (string)$user_lv_time['end'] === '0'){
            return false;
        }

        //如果不是永久会员
        if(isset($user_lv_time['end']) && (string)$user_lv_time['end'] !== '0' && (int)$day !== 0){
            //检查是否过期
            if($user_lv_time['end'] <  wp_strtotime(current_time( 'mysql' ))){
                delete_user_meta($user_id,'zrz_vip');
                delete_user_meta($user_id,'zrz_vip_time');
                wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
                return true;
            }

            return false;
        }

        $current =  wp_strtotime(current_time( 'mysql' ));
        $end = (int)$day ? wp_strtotime('+'.$day.' day') : 0;

        //如果是永久会员，并且管理员重新设置了非永久会员
        if(isset($user_lv_time['end']) && (string)$user_lv_time['end'] === '0' && (int)$day !== 0){
            // update_user_meta($user_id,'zrz_vip_time',array(
            //     'start'=>$current,
            //     'end'=>$end
            // ));

            // do_action('b2_user_rebuild_vip', $user_id);
            // wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            return false;
        }

        //如果不是永久会员，管理员设置了永久会员
        if(isset($user_lv_time['end']) && (string)$user_lv_time['end'] !== '0' && (int)$day === 0){
            // update_user_meta($user_id,'zrz_vip_time',array(
            //     'start'=>$current,
            //     'end'=>'0'
            // ));

            // do_action('b2_user_rebuild_vip', $user_id);
            // wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            return false;
        }

        //如果没有设置会员，重新计算时间
        if(!isset($user_lv_time['end'])){
            update_user_meta($user_id,'zrz_vip_time',array(
                'start'=>$current,
                'end'=>$end
            ));

            do_action('b2_user_rebuild_vip', $user_id);
            wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            return false;
        }

        return true;
    }

    //获取粉丝列表
    public static function get_followers($user_id,$paged,$number){
        $follow = get_user_meta($user_id,'zrz_followed',true);

        if(empty($follow)){
            return array(
                'pages'=>0,
                'data'=>array()
            );
        }

        $paged = (int)$paged;
        $number = (int)$number;

        $offest = ($paged - 1)*$number;

        $arr = array();

        for ($i=$offest; $i < $offest + $number; $i++) {
            if(isset($follow[$i])){
                $following = get_user_meta($user_id,'zrz_follow',true);
                $following = is_array($following) ? $following : array();

                $key_following = array_search((int)$follow[$i],$following);

                $followers = get_user_meta($follow[$i],'zrz_followed',true);
                $followers = is_array($followers) ? $followers : array();

                $arr[] = array(
                    'id'=>$follow[$i],
                    'display_name'=>get_the_author_meta('display_name',$follow[$i]),
                    'link'=>get_author_posts_url($follow[$i]),
                    'avatar'=>get_avatar_url($follow[$i],array('size'=>100)),
                    'post_count'=>count_user_posts($follow[$i],'post'),
                    'following'=>count($following),
                    'followers'=>count($followers),
                    'desc'=>get_the_author_meta('description',$follow[$i]),
                    'followed'=>$key_following === false ? false : true
                );
            }
        }

        return array(
            'pages'=>ceil(count($follow)/$number),
            'data'=>$arr
        );
    }

    public static function get_lv_icon($lv,$return = false){

        if($lv === 'dark_room'){
            if($return){
                return array(
                    'lv'=>'dark_room',
                    'color'=>'',
                    'name'=>__('小黑屋','b2')
                );
            }

            return apply_filters('b2_get_dark_room_icon', '<span class="lv-icon dark-room"><b>'.__('小黑屋','b2').'</b></span>',$lv);
        }

        $roles = self::get_user_roles();

        $icon = '';

        if(isset($roles[$lv])){
            $role = $roles[$lv];
            $name = $role['name'];
            if($return){
                $role['lv'] = $lv;
                return $role;
            }
            if(strpos($lv,'vip') !== false){
                $color = isset($role['color']) ? $role['color'] : '#008000';

                //$rgb = FileUpload::hex2rgb($color);
                //style="background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.18)"
                $icon = apply_filters('b2_get_lv_icon', '<span class="lv-icon user-'.preg_replace("[\d]", '', $lv).' b2-'.$lv.'"><i style="border-color:'.$color.'"></i><b style="color:'.$color.'">'.$name.'</b></span>',$lv,$name,$color);
            }else{
                $icon = apply_filters('b2_get_vip_icon', '<span class="lv-icon user-'.preg_replace("[\d]", '', $lv).' b2-'.$lv.'"><b>'.$name.'</b><i>'.$lv.'</i></span>',$lv,$name);
            }
        }

        if($lv === 'verify'){
            if($return){
                return array(
                    'lv'=>'verify',
                    'color'=>'',
                    'name'=>__('认证用户','b2')
                );
            }

            $icon = apply_filters('b2_get_verify_icon', '<span class="lv-icon user-verify"><b>'.__('认证用户','b2').'</b></span>',$lv,__('认证用户','b2'),'');
        }

        return $icon;
        
    }

    //获取关注列表
    public static function get_following($user_id,$paged,$number){

        $paged = (int)$paged;
        $number = (int)$number;

        if((int)$number > 80) return array('error'=>__('请求数量过多','b2'));
        if((int)$paged < 0) return array('error'=>__('请求格式错误','b2'));
        
        $follow = get_user_meta($user_id,'zrz_follow',true);

        if(empty($follow)){
            return array(
                'pages'=>0,
                'data'=>array()
            );
        }

        $offest = ($paged - 1)*$number;

        $arr = array();

        for ($i=$offest; $i < $offest + $number; $i++) {
            if(isset($follow[$i])){
                $following = get_user_meta($follow[$i],'zrz_follow',true);
                $following = is_array($following) ? count($following) : 0;

                $followers = get_user_meta($follow[$i],'zrz_followed',true);
                $followers = is_array($followers) ? count($followers) : 0;

                $arr[] = array(
                    'id'=>$follow[$i],
                    'display_name'=>get_the_author_meta('display_name',$follow[$i]),
                    'link'=>get_author_posts_url($follow[$i]),
                    'avatar'=>get_avatar_url($follow[$i],array('size'=>100)),
                    'post_count'=>count_user_posts($follow[$i],'post'),
                    'following'=>$following,
                    'followers'=>$followers,
                    'desc'=>get_the_author_meta('description',$follow[$i])
                );
            }
        }

        return array(
            'pages'=>ceil(count($follow)/$number),
            'data'=>$arr
        );
    }

    //关注取消关注
    public static function user_follow_action($user_id){
        $user_id = (int)$user_id;
        $current_user_id = (int)b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('非法操作','b2'));

        if($current_user_id == $user_id) return array('error'=>__('不能关注自己','b2'));

        //关注数据
        $following = get_user_meta($current_user_id,'zrz_follow',true);
        $following = !empty($following) ? $following : array();

        $key_following = array_search($user_id,$following);
        
        $action = false;

        $credit = (int)b2_get_option('normal_gold','credit_follow');

        if($key_following === false){
            $following[] = $user_id;
            $action = true;

            $task = Task::update_task($current_user_id,'task_follow');
            if($task){
                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$current_user_id,
                    'gold_type'=>0,
                    'post_id'=>0,
                    'no'=>$credit,
                    'msg'=>__('您关注了${count}人','b2'),
                    'type'=>'user_followed',
                    'type_text'=>__('关注某人','b2'),
                    'old_row'=>1
                ]);
            }
        }else{
            unset($following[$key_following]);

            $task = Task::update_task($current_user_id,'task_follow');
            if($task){
                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$current_user_id,
                    'gold_type'=>0,
                    'post_id'=>0,
                    'no'=>-$credit,
                    'msg'=>__('您取消关注了${count}人','b2'),
                    'type'=>'user_cancel_followed',
                    'type_text'=>__('关注某人','b2'),
                    'old_row'=>1
                ]);
            }

            //积分记录
            // Message::add_message(array(
            //     'user_id'=>$current_user_id,
            //     'msg_type'=>43,
            //     'msg_read'=>1,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>$user_id,
            //     'msg_credit'=>-$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>'',
            //     'msg_value'=>''
            // ));
        }

        //粉丝
        $follower = get_user_meta($user_id,'zrz_followed',true);
        $follower = !empty($follower) ? $follower : array();

        $key_follower = array_search((int)$current_user_id,$follower);

        if($key_follower === false){
            $follower[] = $current_user_id;
            $action = true;

            $task = Task::update_task($user_id,'task_fans');

            if($task){
                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$user_id,
                    'gold_type'=>0,
                    'post_id'=>0,
                    'no'=>$credit,
                    'msg'=>__('有${count}人关注了您','b2'),
                    'type'=>'author_followed',
                    'type_text'=>__('被关注','b2'),
                    'old_row'=>1
                ]);
            }

            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$current_user_id,
                'to'=>$user_id,
                'post_id'=>0,
                'msg'=>__('${from}关注了您','b2'),
                'type'=>'author_followed',
                'type_text'=>__('被关注','b2'),
                'old_row'=>1
            ]);

            //积分记录
            // Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>11,
            //     'msg_read'=>0,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>$current_user_id,
            //     'msg_credit'=>$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>'',
            //     'msg_value'=>''
            // ));

        }else{
            unset($follower[$key_follower]);

            // $total = Credit::credit_change($user_id,-$credit);

            $task = Task::update_task($user_id,'task_fans');
            if($task){
                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$user_id,
                    'gold_type'=>0,
                    'post_id'=>0,
                    'no'=>-$credit,
                    'msg'=>__('有${count}人取消关注了您','b2'),
                    'type'=>'author_cancel_followed',
                    'type_text'=>__('被取消关注','b2'),
                    'old_row'=>1
                ]);
            }

            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$current_user_id,
                'to'=>$user_id,
                'post_id'=>0,
                'msg'=>__('${from}取消了对您的关注','b2'),
                'type'=>'author_cancle_followed',
                'type_text'=>__('被取消关注','b2'),
                'old_row'=>1
            ]);

            //积分记录
            // Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>15,
            //     'msg_read'=>0,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>$current_user_id,
            //     'msg_credit'=>-$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>'',
            //     'msg_value'=>''
            // ));
        }

        array_values($follower);
        array_values($following);

        update_user_meta($user_id,'zrz_followed',$follower);
        update_user_meta($current_user_id,'zrz_follow',$following);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
        wp_cache_delete('b2_user_'.$current_user_id,'b2_user_custom_data');
        return $action;
    }

    //获取用户收藏列表
    public static function get_user_favorites_list($user_id,$paged,$number,$sub){
        
        $current_user_id = b2_get_current_user_id();

        $offest = ($paged - 1)*$number;

        $arr = self::get_user_favorites_arg($user_id,$sub);

        if(empty($arr)) return array(
            'pages'=>0,
            'data'=>array()
        );

        $_i = 0;
        foreach ($arr as $k => $v) {
            if($v['type'] === $sub){
                $_i++;
            }
        }

        $data = array();

        for ($i=$offest; $i < $offest + $number; $i++) {
            if(isset($arr[$i]['id'])){
                $post_type = get_post_type_object($arr[$i]['type']);
          
                    $data[] = array(
                        'type'=>isset($post_type->labels->name) ? $post_type->labels->name : '',
                        'id'=>$arr[$i]['id'],
                        'thumb'=>get_the_post_thumbnail_url($arr[$i]['id'],'full'),
                        'title'=>get_the_title($arr[$i]['id']),
                        'link'=>get_permalink($arr[$i]['id']),
                        'self'=>(int)$current_user_id === (int)$user_id ? true : false
                    );
                
            }
        }
        
        return array(
            'pages'=>ceil($_i/$number),
            'data'=>$data
        );
    }

    //检查是否已经关注
    public static function check_following($user_id,$post_id,$post_data = true){
        $current_user_id = (int)b2_get_current_user_id();

        $arr = array(
            'following'=>0,
            'self'=>0
        );

        $post_meta = Post::get_post_data($post_id,$post_data);

        $arr = array_merge($arr,$post_meta);

        if(!$current_user_id) return $arr;

        $following = get_user_meta($current_user_id,'zrz_follow',true);
        $following = is_array($following) ? $following : array();

        $key_following = array_search($user_id,$following);

        if($key_following !== false){
            $arr['following'] = 1;
        }

        if((int)$user_id === (int)$current_user_id){
            $arr['self'] = 1;
        }

        $arr = array_merge($arr,$post_meta);

        return $arr;
    }

    //用户收藏
    public static function user_favorites($post_id){

        if(get_post_status($post_id) !== 'publish') return ['error'=>__('该文章无法收藏','b2')];

        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('请登录','b2'));
        if(!$post_id) return array('error'=>__('参数不全','b2'));

        $post_type = get_post_type($post_id);

        //获取用户的收藏数据
        $favorites = get_user_meta($current_user_id,'zrz_user_favorites',true);
        $favorites = is_array($favorites) ? $favorites : array();

        //获取文章的收藏数据
        $post_favorites = get_post_meta($post_id, 'zrz_favorites', true );
        $post_favorites = is_array($post_favorites) ? $post_favorites : array();

        
        $key_f = isset($favorites[$post_type]) ? array_search($post_id,$favorites[$post_type]) : false;
        $key_u = array_search($current_user_id,$post_favorites);

        $add = false;

        //如果不存在此收藏，加入
        if($key_f === false && $key_u === false){
            $favorites[$post_type][] = $post_id;
            $post_favorites[] = $current_user_id;
            $add = true;
        }else{
            unset($favorites[$post_type][$key_f]);
            unset($post_favorites[$key_u]);
        }

        update_user_meta($current_user_id,'zrz_user_favorites',$favorites);
        update_post_meta($post_id,'zrz_favorites',$post_favorites);

        do_action('b2_post_favorite',$post_id,$current_user_id);

        if($post_type == 'ask'){
            do_action('b2_ask_hotness',$post_id);
        }

        return $add;
    }

    public static function get_user_favorites_arg($user_id,$sub){
        $favorites = get_user_meta($user_id,'zrz_user_favorites',true);

        if(!isset($favorites[$sub])){
            return '';
        }

        $arr = array();

        foreach ($favorites[$sub] as $k => $v) {
            if(get_post_type($v) === $sub && get_post_status($v) === 'publish'){
                $arr[] = array(
                    'type'=>$sub,
                    'id'=>$v
                );
            }
        }

        return $arr;
    }

    //获取余额
    public static function get_money($user_id){
        if(!$user_id) return;

        $money = get_user_meta($user_id,'zrz_rmb',true);
        $money = $money ? $money : 0;

        return apply_filters('b2_get_money', '<span class="user-money">'.B2_MONEY_SYMBOL.$money.'</span>');
    }

    public static function money_change($user_id,$money){

        global $user_current_money;
        $_money = get_user_meta($user_id,'zrz_rmb',true);
        $_money = $_money ? $_money : 0;

        $user_current_money = $_money;

        //金额
        $money = bcadd((float)$money,(float)$_money,2);

        if($money < 0) return false;

        update_user_meta($user_id,'zrz_rmb',$money);
        // do_action('b2_user_rebuild_money', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
        return $money;
    }

    //获取积分
    public static function get_credit($user_id){
        if(!$user_id) return;
        $credit = get_user_meta( $user_id, 'zrz_credit_total', true );
        $credit = $credit ? $credit : 0;

        return apply_filters('b2_get_credit', '<span class="user-credit">'.b2_get_icon('b2-coin-line').$credit.'</span>');
    }

    //获取用户社交登录信息
    public static function get_user_open_info($user_id){
        $arg = b2_oauth_types(true);

        $arr = array();

        $open = get_user_meta($user_id,'zrz_open',true);

        foreach ($arg as $k => $v) {
            $uid = get_user_meta($user_id,'zrz_'.$k.'_uid',true);

            if($k == 'weixin' && !$uid){
                $uid = get_user_meta($user_id,'zrz_mpweixin_uid',true);
                if(!$uid){
                    $uid = get_user_meta($user_id,'zrz_weixin_unionid',true);
                }
            }

            $arr[$k] = array(
                'isset'=>$uid ? true : false,
                'avatar'=>apply_filters('b2_get_avatar_url', $user_id,100,$k),
                'name'=>$v['name'],
                'url'=>$v['url'],
                'open'=>$v['open'],
                'mp'=>!b2_is_weixin() && b2_get_option('normal_login','wx_mp_login'),
                'pc_open'=>!b2_is_weixin() && b2_get_option('normal_login','wx_pc_open')
            );
        }

        $avatar = isset($open['avatar']) ? $open['avatar'] : '';

        if(is_numeric($avatar)){
            $avatar = wp_get_attachment_url($avatar);
        }elseif(empty($avatar)){
            $avatar = self::get_letter_avatar(get_author_name($user_id));
        }

        $avatar = b2_get_thumb(array(
            'thumb'=>$avatar,
            'type'=>'fill',
            'width'=>100,
            'height'=>100,
        ));

        $arr['default'] = array(
            'avatar_set'=>isset($open['avatar_set']) ? $open['avatar_set'] : 'default',
            'avatar'=>$avatar,
            'open'=>true
        );

        return $arr;
    }

    public static function get_default_address($user_id,$key = ''){
        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        $default_address = get_user_meta($user_id,'zrz_default_address',true);

        if(!isset($userData['address'])) return array('error'=>__('收货地址不存在','b2'));

        $address = $userData['address'];

        //如果指定了地址
        if($key && isset($address[$key])){
            $ads = $address[$key];

            $_ads = isset($ads['address']) ? $ads['address'] : '';
            $phone = isset($ads['phone']) ? $ads['phone'] : '';
            $name = isset($ads['name']) ? $ads['name'] : '';

            $province = isset($ads['province']) ? $ads['province'] : '';
            $city = isset($ads['city']) ? $ads['city'] : '';
            $county = isset($ads['county']) ? $ads['county'] : '';
        }
        //如果没有指定地址获取默认地址，如果没有默认地址，获取第一个地址
        elseif($default_address && isset($address[$default_address])){
            $ads = $address[$default_address];

            $_ads = isset($ads['address']) ? $ads['address'] : '';
            $phone = isset($ads['phone']) ? $ads['phone'] : '';
            $name = isset($ads['name']) ? $ads['name'] : '';

            $province = isset($ads['province']) ? $ads['province'] : '';
            $city = isset($ads['city']) ? $ads['city'] : '';
            $county = isset($ads['county']) ? $ads['county'] : '';

        }else{
            reset($address);
            $first = current($address);

            $_ads = isset($first['address']) ? $first['address'] : '';
            $phone = isset($first['phone']) ? $first['phone'] : '';
            $name = isset($first['name']) ? $first['name'] : '';

            $province = isset($first['province']) ? $first['province'] : '';
            $city = isset($first['city']) ? $first['city'] : '';
            $county = isset($first['county']) ? $first['county'] : '';

        }

        return $province ? $province.' '.$city.' '.$county.' '.$_ads.' '.$name.' '.$phone  : $_ads.' '.$name.' '.$phone;
    }

    public static function get_addresses(){
        $user_id = b2_get_current_user_id();
        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        $address = isset($userData['address']) && !empty($userData['address']) ? $userData['address'] : array();

        $default_address = get_user_meta($user_id,'zrz_default_address',true);

        if(!isset($address[$default_address])){
            $default_address = isset(array_keys($address)[0]) ? array_keys($address)[0] : '';
        }

        return array(
            'addresses'=>$address,
            'default'=>$default_address
        );
    }

    //获取编辑页面用户数据
    public static function get_author_settings($user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if(!$user_id){
            $user_id = $current_user_id;
        }

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        $data = get_userdata($user_id);

        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        $default_address = get_user_meta($user_id,'zrz_default_address',true);

        $open = get_user_meta($user_id,'zz_open',true);

        $qrcode = get_user_meta($user_id,'zrz_qcode',true);

        $email = $data->user_email;
        $_email = explode('@', $email)[1];

        $data = array(
            'display_name'=>$data->display_name,
            'sex'=>isset($userData['gender']) ? $userData['gender'] : 0,
            'phone'=>Login::is_phone($data->user_login) ? $data->user_login : '',
            'address'=>isset($userData['address']) ? $userData['address'] : array(),
            'email'=>strpos(B2_HOME_URI,$_email) === false ? $email : '',
            'login'=>$data->user_login,
            'url'=>$data->user_url,
            'desc'=>get_user_meta($user_id,'description',true),
            'default_address'=>$default_address,
            'open'=>self::get_user_open_info($user_id),
            'qrcode_weixin'=>isset($qrcode['weixin']) ? b2_get_thumb(array(
                'thumb'=>$qrcode['weixin'],
                'type'=>'fill',
                'width'=>120,
                'height'=>'100%'
            )) : '',
            'qrcode_alipay'=>isset($qrcode['alipay']) ? b2_get_thumb(array(
                'thumb'=>$qrcode['alipay'],
                'type'=>'fill',
                'width'=>120,
                'height'=>'100%'
            )) : '',
            'check_type'=>b2_get_option('normal_login','check_type'),
            'verify'=>get_user_meta($user_id,'b2_title',true)
        );

        return apply_filters('b2_author_settings_info', $data,$user_id);
    }

    //修改默认头像
    public static function change_avatar($type,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        $open = get_user_meta($user_id,'zrz_open',true);
        $open = is_array($open) ? $open : array();

        $open['avatar_set'] = esc_attr($type);

        update_user_meta($user_id,'zrz_open',$open);

        do_action('b2_user_rebuild_avatar', $user_id);

        $avatar = get_avatar_url($user_id,array('size'=>100));
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        return array(
            'avatar'=>$avatar,
            'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar)
        );
    }

    //保存收款二维码
    public static function save_qrcode($type,$id,$url,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        if($type !== 'alipay' && $type !== 'weixin') return array('error'=>__('参数错误','b2'));

        $qrcode = get_user_meta($user_id,'zrz_qcode',true);
        $qrcode = is_array($qrcode) ? $qrcode : array();

        $qrcode[$type] = esc_url($url);

        update_user_meta($user_id,'zrz_qcode',$qrcode);

        return b2_get_thumb(array(
            'thumb'=>$url,
            'type'=>'fill',
            'width'=>120,
            'height'=>'100%'
        ));
    }

    //解除社交账户的绑定
    public static function un_build($type,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));
        
        do_action('b2_user_social_un_build',$user_id,$type);

        if($type == 'weixin'){
            $uid = get_user_meta($user_id,'zrz_weixin_uid',true);
            if(!$uid){
                $uid = get_user_meta($user_id,'zrz_mpweixin_uid',true);
                if(!$uid){
                    $uid = get_user_meta($user_id,'zrz_weixin_unionid',true);
                }
            }
            
            if(!$uid) return array('error'=>__('您未绑定此账户，无需解绑','b2'));
        }
        
        $user_info = get_userdata( $current_user_id );

        if(!is_numeric($user_info->data->user_login) && strpos($user_info->data->user_email,(string)$current_user_id) !== false && !user_can($current_user_id, 'administrator' )){
            $begin_date = wp_strtotime($user_info->data->user_registered);

            $end_date =  wp_strtotime(current_time( 'mysql' ));
            $days = round(($end_date - $begin_date) / 3600 / 24);
    
            if($days <= 2) return array('error'=>__('系统原因，暂时无法解绑','b2'));
        }

        delete_user_meta($user_id,'zrz_'.$type.'_uid');
        delete_user_meta($user_id,'zrz_'.$type.'_openid');
        delete_user_meta($user_id,'zrz_'.$type.'_open_id');

        if($type == 'weixin'){
            delete_user_meta($user_id,'zrz_weixin_unionid');
            delete_user_meta($user_id,'zrz_mpweixin_uid');
        }

        $open = get_user_meta($user_id,'zrz_open',true);
        $open = is_array($open) ? $open : array();

        $open['avatar_set'] = 'default';

        unset($open[$type.'_avatar_new']);

        update_user_meta($user_id,'zrz_open',$open);

        do_action('b2_user_rebuild_open', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');

        return self::get_author_settings($user_id);
        
    }

    //保存用户的昵称
    public static function save_nick_name($name,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        $res = Login::check_nickname($name);
        if(isset($res['error'])){
            return $res;
        }

        //更新昵称和邮箱
        $arr = array(
            'display_name'=>$name,
            'ID'=>$user_id
        );
        $res = wp_update_user($arr);

        if(is_wp_error($res)){
            return array('error'=>$res->get_error_message());
        }
        do_action('b2_user_rebuild_name', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        return true;
    }

    //保存性别
    public static function save_sex($sex,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        if((int)$sex !== 1 && (int)$sex !== 0) return array('error'=>__('非法操作','b2'));

        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        $userData = is_array($userData) ? $userData : array();
        $userData['gender'] = (int)$sex;

        update_user_meta($user_id,'zrz_user_custom_data',$userData);

        do_action('b2_user_rebuild_sex', $user_id);

        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');

        return true;
    }

    //保存网址
    public static function save_url($url,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        if(empty($url)) return array('error'=>__('网址不可为空','b2'));

        $url = str_replace(array('{{','}}'),'',sanitize_text_field($url));

        $url = sanitize_text_field($url);

        //更新昵称和邮箱
        $arr = array(
            'user_url'=>esc_url($url),
            'ID'=>$user_id
        );
        $res = wp_update_user($arr);

        if(is_wp_error($res)){
            return array('error'=>$res->get_error_message());
        }

        do_action('b2_user_rebuild_url', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');

        return true;
    }

    //保存描述
    public static function save_desc($desc,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        $censor = apply_filters('b2_text_censor', $desc);
        if(isset($censor['error'])) return $censor;

        if(empty($desc)) return array('error'=>__('描述不可为空','b2'));

        //更新描述
        update_user_meta($user_id,'description',b2_remove_kh($desc,true));

        do_action('b2_user_rebuild_desc', $user_id);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        return true;
    }

    //保存收货地址
    public static function save_address($data){

        $address = isset($data['address']) ? $data['address'] : '';
        $name = isset($data['name']) ? $data['name'] : '';
        $phone = isset($data['phone']) ? $data['phone'] : '';
        $province = isset($data['province']) ? $data['province'] : '';
        $city = isset($data['city']) ? $data['city'] : '';
        $county = isset($data['county']) ? $data['county'] : '';
        $regionCode = isset($data['regionCode']) ? $data['regionCode'] : '';
        $key = isset($data['key']) ? $data['key'] : '';
        $user_id = isset($data['user_id']) ? $data['user_id'] : '';

        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if(!$user_id){
            $user_id = $current_user_id;
        }

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        if(!$address || !$name || !$phone || !$key) return array('error'=>__('请填写完整的地址信息','b2'));

        if(!Login::is_phone($phone)) return array('error'=>__('您填写的不是手机号码','b2'));

        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        $userData = isset($userData['address']) && is_array($userData['address']) ? $userData : array();

        $userData['address'][esc_attr($key)] = array(
            'address'=>str_replace(array('{{','}}'),'',sanitize_text_field($address)),
            'name'=>str_replace(array('{{','}}'),'',sanitize_text_field($name)),
            'phone'=>str_replace(array('{{','}}'),'',sanitize_text_field($phone)),
            'province'=>str_replace(array('{{','}}'),'',sanitize_text_field($province)),
            'city'=>str_replace(array('{{','}}'),'',sanitize_text_field($city)),
            'county'=>str_replace(array('{{','}}'),'',sanitize_text_field($county)),
            'regionCode'=>str_replace(array('{{','}}'),'',sanitize_text_field($regionCode)),
        );

        if(count($userData['address']) == 1){
            update_user_meta($user_id,'zrz_default_address',$key);
        }

        update_user_meta($user_id,'zrz_user_custom_data',$userData);

        return array(
            'address'=>$userData['address'],
            'key'=>$key
        );
    }

    //设为默认地址
    public static function save_default_address($key,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$user_id){
            $user_id = $current_user_id;
        }

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));

        $address = get_user_meta($user_id,'zrz_default_address',true);

        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        $userData = isset($userData['address']) ? $userData : array();

        foreach ($userData['address'] as $k => $v) {
            if(!isset($userData['address'][$key])){
                return array('error'=>__('不存在此地址','b2'));
            }
        }

        update_user_meta($user_id,'zrz_default_address',$key);

        return $key;

    }

    //删除地址
    public static function delete_address($key,$user_id){
        $current_user_id = (int)b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if(!$user_id){
            $user_id = $current_user_id;
        }

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2'));
        

        $address = get_user_meta($user_id,'zrz_default_address',true);

        $userData = get_user_meta($user_id,'zrz_user_custom_data',true);
        
        if(!isset($userData['address'])) return array('error'=>__('数据错误','b2'));

        $arr = array();

        $userData['address'] = (array)$userData['address'];

        foreach ($userData['address'] as $k => $v) {
            if(!isset($userData['address'][$key])){
                return array('error'=>__('不存在此地址','b2'));
            }
            if($k != $key){
                $arr[] = $k;
            }
        }

        unset($userData['address'][$key]);

        if($key == $address && count($arr) > 0){
            update_user_meta($user_id,'zrz_default_address',$arr[0]);
            $address = $arr[0];
        }

        if(count($arr) == 0){
            delete_user_meta($user_id,'zrz_default_address');
            $address = '';
        }

        update_user_meta($user_id,'zrz_user_custom_data',$userData);

        return array(
            'address'=>$userData['address'],
            'default'=>$address
        );
    }

    //修改用户名
    public static function save_username($data){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$data['user_id'] !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2')); 
        
        //检查验证码
        $check = Login::code_check($data);
        if(isset($check['error'])){
            return $check;
        }

        $verify_code = Login::check_verify_code($data);
        if(isset($verify_code['error'])){
            return $verify_code;
        }

        $check = Login::check_username($data['username']);
        if(isset($check['error'])) return $check;
        
        if(is_email($data['username'])){
            $arr = array(
                'user_email' => sanitize_email($data['username']),
                'ID'=>(int)$data['user_id']
            );
            $res = wp_update_user($arr);
        }else{

            global $wpdb;
            $res = $wpdb->update($wpdb->users, array('user_login' =>esc_sql($data['username'])), array('ID' => (int)$data['user_id']));
            wp_cache_delete($data['user_id'], 'users');
            clean_user_cache($data['user_id']);
        }

        if(is_wp_error($res)){
            return array('error'=>$res->get_error_message());
        }

        wp_cache_delete('b2_user_'.$data['user_id'],'b2_user_data');

        $allow_cookie = apply_filters('b2_login_cookie', b2_get_option('normal_login','allow_cookie'));

        if((string)$allow_cookie === '1'){
            wp_set_auth_cookie($current_user_id,true);
        }

        return true;
    }

    //修改密码
    public static function edit_pass($password,$repassword,$user_id){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2')); 

        if($password !== $repassword) return array('error'=>__('请重复输入两次相同的密码！','b2'));

        if(strlen($password) < 6){
            return array('error'=>__('密码必须大于6位','b2'));
        }

        wp_set_password($password, $user_id );

        return true;
    }

    //用户警告
    public static function add_danger_user($user_id,$type){
        $danger = get_user_meta($user_id,'b2_danger',true);
        $danger = is_array($danger) ? $danger : array();

        $danger[] = esc_attr($type);

        update_user_meta($user_id,'b2_danger',$danger);
    }

    //用户搜索
    public static function search_users($key){
        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        if(!$key) return array('error'=>__('用户名不可为空','b2'));

        $users = new \WP_User_Query( array(
            'search'         => '*'.$key.'*',
            'search_columns' => array(
                'display_name',
            ),
            'number' => 20,
            'paged' => 1
        ) );

        $users_found = $users->get_results();

        $users = array();

        foreach ($users_found as $user) {
            $users[] = self::get_user_public_data($user->ID,true);
        }
    
        if(!empty($users)) return $users;

        return array('error'=>__('未找到相关用户','b2'));
    }

    //获取用户的财富信息
    public static function get_user_gold_data($_user_id){

        $user_id = b2_get_current_user_id();

        if(!$user_id){
            return array('error'=>__('请先登录','b2'));
        }

        if($_user_id && user_can($user_id, 'administrator' )){
            $user_id = $_user_id;
        }

        $credit = get_user_meta($user_id, 'zrz_credit_total', true );
        $credit = $credit ? $credit : 0;

        $money = get_user_meta($user_id,'zrz_rmb',true);
        $money = $money ? $money : 0;

        return array(
            'credit'=>$credit,
            'money'=>$money
        );
    }

    //获取财富页面积分、余额列表信息
    public static function get_gold_list($_user_id,$type,$paged){
        $user_id = b2_get_current_user_id();

        if(!$user_id){
            return array('error'=>__('请先登录','b2'));
        }

        if($_user_id && user_can($user_id, 'administrator' )){
            $user_id = $_user_id;
        }

        $msg = Message::get_user_message($user_id,$type,$paged);

        return $msg;

    }

    //申请提现
    public static function cash_out($money){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        if((int)b2_get_option('normal_gold','gold_tx') === 0) return array('error'=>__('禁止提现','b2'));

        //检查余额
        if(!is_numeric($money)){
            return array('error'=>__('参数错误','b2'));
        }

        $money = (float)$money;

        if($money < (float)b2_get_option('normal_gold','gold_money')){
            return array('error'=>__('提现金额太少','b2'));
        }

        if($money > 99999 || $money <= 0 ) return array('error'=>__('参数错误','b2'));

        //检查是否有未完成的款项
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $mark = '-1+'.$user_id;

        //检查是否有未回复的工单
        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `mark`=%s AND `status`=%d",$mark,0),
            ARRAY_A
        );

        if(!empty($res)){
            return array('error'=>__('您有未完成的提现请求，请完成之后再次发起','b2'));
        }

        //检查余额是否充足
        $my_money = get_user_meta($user_id,'zrz_rmb',true);

        if($my_money < $money) return array('error'=>sprintf(__('%s不足','b2'),B2_MONEY_NAME));

        //计算手续费
        $_money = bcmul(b2_get_option('normal_gold','gold_tc'),$money,2);
        
        //真实提现金额
        $c_money = bcsub($money,$_money,2);
       
        $res = $wpdb->insert($table_name, array(
            'mark'=>$mark,
            'from'=> (int)$user_id,
            'to'=> -1,
            'date'=> current_time('mysql'),
            'status'=> 0,
            'content'=> $money,
            'key'=>$c_money,
            'value'=>$_money
        ));
        
        // if($res){

        //     $y = self::money_change($user_id,-$money);

        //     if($y > -1){
                Message::add_message(array(
                    'user_id'=>$user_id,
                    'msg_type'=>41,
                    'msg_read'=>1,
                    'msg_date'=>current_time('mysql'),
                    'msg_users'=>'',
                    'msg_credit'=>-$money,
                    'msg_credit_total'=>$_money,
                    'msg_key'=>$c_money,
                    'msg_value'=>$_money
                ));

                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$user_id,
                    'gold_type'=>1,
                    'post_id'=>0,
                    'no'=>-$money,
                    'msg'=>sprintf(__('您申请了提现，其中手续费%s，实际到账应为%s。一个工作日内到账。','b2'),B2_MONEY_SYMBOL.$_money,B2_MONEY_SYMBOL.$c_money),
                    'type'=>'user_tx',
                    'type_text'=>__('申请提现','b2')
                ]);  
                
                $admin_id = b2_get_option('normal_weixin_message','admin_id');

                Message::update_data([
                    'date'=>current_time('mysql'),
                    'from'=>$user_id,
                    'to'=>$admin_id,
                    'post_id'=>0,
                    'msg'=>__('${from}申请了提现','b2'),
                    'type'=>'user_tx',
                    'type_text'=>__('申请提现','b2')
                ]);


                return true;
            // }
        // }

        return array('error'=>__('提现申请失败','b2'));
    }

    //获取签到数据
    public static function get_user_mission_data($user_id){
        $date = get_user_meta($user_id,'b2_mission_today',true);

        $credit = get_user_meta($user_id,'b2_mission_credit',true);

        if(current_time('Y-m-d') > substr($date,0,10)){
            delete_user_meta($user_id, 'b2_mission_today');
            update_user_meta($user_id, 'b2_mission_old_today',$date);
            delete_user_meta($user_id,'b2_mission_credit');
            wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
            $credit = 0;
            $date = '';
        }

        $bs = b2_get_option('normal_gold','tk_bs');

        $always = get_user_meta($user_id,'b2_mission_always_count',true);
        $always = $always ? $always : 0;

        $_credit = b2_get_option('normal_gold','credit_qd');
        if(strpos($_credit,'-') !== false){
            $_credit = explode('-', $_credit);
        }

        $days = 0;
        $mission_always = get_user_meta($user_id,'b2_mission_always_date',true);
        if(!$mission_always){
            $_credit = 0;
        }else{
            $days = (int)((wp_strtotime(current_time('Y-m-d')) - wp_strtotime(substr($mission_always,0,10)))/86400) - 2;
            $days = $days < 0 ? 0 : $days;
            $_credit = $days*$_credit[1]*$bs;
        }

        $my_credit = 0;
        if((int)$user_id){
            $my_credit = get_user_meta($user_id,'zrz_credit_total',true);
            $my_credit = $my_credit ? $my_credit : 0;
        }

        return array(
            'date'=>$date,
            'credit'=>$credit,
            'always'=>$always,
            'tk'=>array(
                'days'=>$days,
                'credit'=>$_credit,
                'bs'=>$bs
            ),
            'my_credit'=>$my_credit,
            'current_user'=>(int)$user_id
        );
    }

    //重置连续签到（填坑）
    public static function reset_always_mission($data){
        $user_id = (int)$data['user_id'];
        if($user_id <= 0) return;

        $mission_always = get_user_meta($user_id,'b2_mission_always_date',true);
        $days = (int)((wp_strtotime(current_time('Y-m-d')) - wp_strtotime(substr($mission_always,0,10)))/86400) - 2;
        $days = $days < 0 ? 0 : $days;

        $count = (int)get_user_meta($user_id,'b2_mission_always_count',true);
        $count = $count ? $count : 0;

        update_user_meta($user_id,'b2_mission_always_count',$count+$days);

        self::mission_task($user_id);
        update_user_meta($user_id,'b2_mission_always_date',current_time('mysql'));
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
    }

    public static function get_user_mission($count,$paged){
        $user_id = (int)b2_get_current_user_id();
        $paged = (int)$paged;

        $arr = array(
            'mission'=>self::get_user_mission_data($user_id),
        );
        
        $_arr = array();
        if($count){
            $_arr = array(
                'mission_today_list'=>self::get_mission_user_list('today',$count,$paged),
                'mission_always_list'=>self::get_mission_user_list('always',$count,$paged),
            );
        }
        
        $new = array_merge($arr,$_arr);

        return $new;
    }

    public static function mission_task($user_id){
        $task_count = get_user_meta($user_id,'b2_task_mission_count',true);

        $mission_count = (int)get_user_meta($user_id,'b2_mission_always_count',true);

        //如果之前没有加过积分，忽略，重新计算。
        if($task_count === ''){
            update_user_meta($user_id,'b2_task_mission_count',$mission_count);
            return;
        }

        $mission_task = b2_get_option('normal_task','task_mission_task');
        if((int)$mission_task == 0) return;

        $mission_task = explode('|',$mission_task);

        if(!isset($mission_task[1]) || (int)$mission_task[0] == 0 || (int)$mission_task[1] == 0) return;

        $count = intval(($mission_count - $task_count)/$mission_task[0]);

        if($count <= 0) return;

        $total = $count*(int)$mission_task[1];
        

        Message::update_data([
            'date'=>current_time('mysql'),
            'from'=>0,
            'to'=>$user_id,
            'post_id'=>0,
            'msg'=>sprintf(__('您连续签到了%s天，每%s天奖励%s积分，此次一共奖励%s积分：${gold_page}','b2'),$mission_count,$mission_task[0],$mission_task[1],$total),
            'type'=>'post_up',
            'type_text'=>__('连续签到奖励','b2')
        ]);

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$user_id,
            'gold_type'=>0,
            'no'=>$total,
            'post_id'=>0,
            'msg'=>sprintf(__('您连续签到了%s天，每%s天奖励%s积分，此次一共奖励%s积分','b2'),$mission_count,$mission_task[0],$mission_task[1],$total),
            'type'=>'post_up',
            'type_text'=>__('连续签到奖励','b2')
        ]);

        update_user_meta($user_id,'b2_task_mission_count',$mission_count);
    }

    //用户签到
    public static function user_mission(){

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $credit = get_user_meta($user_id,'b2_mission_credit',true);
        if(!$credit){
            $credit = b2_get_option('normal_gold','credit_qd');
            if(strpos($credit,'-') !== false){
                $credit = explode('-', $credit);
                $credit = (int)rand($credit[0], $credit[1]);
            }

            // if(get_user_meta($user_id,'zrz_vip',true)){
            //     $credit = (int)rand(50,100);
            // }

            //连续签到
            $mission_always = get_user_meta($user_id,'b2_mission_always_date',true);
            if(!$mission_always){
                update_user_meta($user_id,'b2_mission_always_date',current_time('mysql'));
                update_user_meta($user_id,'b2_mission_always_count',1);
            }else{
                if((int)((wp_strtotime(current_time('mysql')) - wp_strtotime(substr($mission_always,0,10)))/86400) === 1){
                    $count = (int)get_user_meta($user_id,'b2_mission_always_count',true);
                    update_user_meta($user_id,'b2_mission_always_count',$count+1);
                    update_user_meta($user_id,'b2_mission_always_date',current_time('mysql'));
                }
            }

            $arr = array(
                'date'=>current_time('mysql'),
                'credit'=>$credit
            );

            update_user_meta($user_id,'b2_mission_today',$arr['date']);
            update_user_meta($user_id,'b2_mission_credit',$arr['credit']);

            // $total = Credit::credit_change($user_id,$credit);

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$user_id,
                'gold_type'=>0,
                'post_id'=>0,
                'no'=>$credit,
                'msg'=>__('签到成功','b2'),
                'type'=>'user_mission',
                'type_text'=>__('签到','b2')
            ]);

            self::mission_task($user_id);

            //积分记录
            // Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>16,
            //     'msg_read'=>1,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>0,
            //     'msg_credit'=>$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>current_time('mysql'),
            //     'msg_value'=>''
            // ));

            $arr['mission'] = self::get_user_mission_data($user_id);
            wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');

            return $arr;
        }

        return $credit;
    }

    public static function get_mission_list($type,$count,$paged){
        $paged = (int)$paged;
        $count = (int)$count;

        return self::get_mission_user_list($type,$count,$paged);
    }

    //获取签到用户列表
    public static function get_mission_user_list($type,$count,$paged){

        if((int)$paged < 0) return array('error'=>__('数据格式错误','b2'));

        $offset = ($paged -1)*$count;

        if((int)$count >50) return array('error'=>__('请求数据数量过多','b2'));

        $count = $count == 0 ? 1 : $count;

        $args = array(
            'number' => $count,
            'offset'=>$offset
        );

        if($type === 'today'){
            $args['meta_key'] = 'b2_mission_today';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'DESC';
            $args['meta_query'] = array(
                array(
                    'key' => 'b2_mission_today',
                    'meta_value' => current_time("Y-m-d"),
                    'compare' => '>='
                )                   
            );
        }else{
            $args['meta_key'] = 'b2_mission_always_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
        }

        $user_query = new \WP_User_Query($args);
        
        $authors = $user_query->get_results();
        $total = $user_query->get_total();

        $pages = ceil($total/$count);

        $data = array();
        foreach ($authors as $k => $v) {
            $user = self::get_user_public_data($v->ID,true);
            $count = get_user_meta($v->ID,'b2_mission_always_count',true);

            if($type === 'always'){
                $date = get_user_meta($v->ID,'b2_mission_always_date',true);
                $data[] = array(
                    'user'=>$user,
                    'count'=>$count,
                    'date'=>b2_timeago(strlen($date) == 10 ? $date.' 21:01:01' : $date),
                    '_date'=>$date
                );
            }else{
                $date = get_user_meta($v->ID,'b2_mission_today',true);
                $date = $date ? $date : get_user_meta($v->ID,'b2_mission_old_today',true);
                $data[] = array(
                    'user'=>$user,
                    'credit'=>get_user_meta($v->ID,'b2_mission_credit',true),
                    'date'=>b2_timeago($date),
                    'count'=>$count,
                    '_date'=>$date
                );
            }
        }

        return array(
            'pages'=>$pages,
            'data'=>$data
        );
    }

    public static function get_user_custom_data($user_id ){

        

        $custom_data = array();

        if($user_id){
            if(isset($GLOBALS['b2_user_custom_data_'.$user_id])) return $GLOBALS['b2_user_custom_data_'.$user_id];

            // $cache = wp_cache_get( 'b2_user_'.$user_id, 'b2_user_custom_data');
            // if($cache){
            //     $custom_data = $cache;
            // }else{
                $following = get_user_meta($user_id,'zrz_follow',true);
                $following = is_array($following) ? count($following) : 0;
                
                $followers = get_user_meta($user_id,'zrz_followed',true);
                $followers = is_array($followers) ? count($followers) : 0;
        
                $credit = get_user_meta($user_id,'zrz_credit_total',true);
                $credit = $credit ? $credit : 0;
        
                $money = get_user_meta($user_id,'zrz_rmb',true);
                $money = $money ? $money : 0;
        
                $task = Task::user_task_finish($user_id);
        
                $custom_data = array(
                    'following'=>$following,
                    'followers'=>$followers,
                    'post_count'=>count_user_posts($user_id,'post'),
                    'comment_count'=>Comment::get_user_comment_count($user_id),
                    'credit'=>$credit,
                    'money'=>number_format($money,2,".",""),
                    'task'=>bcmul($task['finish']/$task['total'],100,0),
                    'task_'=>$task 
                );

                $custom_data = apply_filters('b2_get_user_custom_data',$custom_data,$user_id);

                $GLOBALS['b2_user_custom_data_'.$user_id] = $custom_data;

              // wp_cache_set('b2_user_'.$user_id,$custom_data,'b2_user_custom_data', HOUR_IN_SECONDS);
            //}
        }

        return $custom_data;

    }

    //获取小工具中用户相关的数据
    public static function get_user_widget(){
        $user_id = b2_get_current_user_id();
        if($user_id){

            return array();
        }

        return array(
            'oauth'=>b2_oauth_types(true),
            'mp'=>!b2_is_weixin() && b2_get_option('normal_login','wx_mp_login')
        );
    }

    public static function get_vip_info(){
        $user_id = b2_get_current_user_id();

        $vip_data = b2_get_option('normal_user','user_vip_group');
        $count = self::get_vip_count();
        $vip = get_user_meta($user_id,'zrz_vip',true);

        $_vip = (string)preg_replace('/\D/s','',$vip);
        foreach ($vip_data as $k => $v) {
            if(!$v['price']) {
                unset($vip_data[$k]);
                unset($count[$k]);
                continue;
            }

            if((string)$k === $_vip){
                $vip_data[$k]['allow_buy'] = false;
            }else{
                $vip_data[$k]['allow_buy'] = true;
            }
            $role = array();

            foreach (b2_roles_arg() as $_k => $_v) {
                $role[$_k] = array(
                    'allow'=>in_array($_k,(array)$vip_data[$k]['user_role']),
                    'name'=>$_v
                );
            }

            $vip_data[$k]['more'] = array();

            if(isset($v['more']) && $v['more']){
                $more = explode(PHP_EOL,$v['more']);
                
                $_more = array();

                if(!empty($more)){
                    foreach ($more as $km => $vm) {
                        $s_more = trim($vm, " \t\n\r\0\x0B\xC2\xA0");
                        $s_more = explode('|',$s_more);
                        if(!$s_more[0]) continue;
                        $_more[] = array(
                            'text'=>$s_more[0],
                            'role'=>$s_more[1]
                        );
                    }
                }

                $vip_data[$k]['more'] = $_more;
            }

            $vip_data[$k]['user_role'] = $role;
        }

        $user_data = $user_id ? self::get_user_lv($user_id) : array();
        if(!empty($user_data)){
            $time = get_user_meta($user_id,'zrz_vip_time',true);
            if(isset($time['end'])){
                $time = $time['end'];
                $time = $time == 0 ? 'long' : wp_date('Y-m-d H:i:s',$time);
                $user_data['time'] = $time;
            }
        }else{
            $user_data = 'guest';
        }

        $vip_data = apply_filters('b2_vip_roles', $vip_data);

        $data = array(
            'data'=>$vip_data,
            'count'=>$count,
            'user'=>$user_data
        );

        return $data;
    }

    public static function get_vip_count(){

        $roles = self::get_user_roles();
        $count = get_option('b2_vip_count',true);
        $lv = array();
        if(is_array($count)){
            foreach ($count as $k => $v) {
                $init_count = isset($roles[$k]['count']) ? (int)$roles[$k]['count'] : 300;

                if(isset($roles[$k]['name'])){
                    $lv[] = array(
                        'name'=>$roles[$k]['name'],
                        'lv'=>$k,
                        'icon'=>self::get_lv_icon($k),
                        'count'=>$v + $init_count
                    );
                }
                
            }
        }

        return $lv;
    }

    //随机获取认证用户
    public static function get_verify_users(){

        global $wpdb;
        $users = $wpdb->get_results("SELECT `ID` FROM $wpdb->users u LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id WHERE um.meta_key = 'b2_title' ORDER BY RAND() LIMIT 8");

        $data = array();

        if($users){
            foreach ($users as $k => $v) {
                $data[] = self::get_user_public_data($v->ID,true);
            }
        }

        return $data;
    }

    //更新VIP数量
    public static function update_vip_count($order_id,$data){
        $count = get_option('b2_vip_count');
        $count = is_array($count) ? $count : array();

        if(isset($count[$data['order_key']])){
            $count[$data['order_key']]++;
        }else{
            $count[$data['order_key']] = 1;
        }

        update_option('b2_vip_count',$count);
    }

    public static function get_oauth_link(){
        return b2_oauth_types(true);
    }

    public static function get_current_user_attachments($type,$paged){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));
        
        $offset = ($paged -1)*12;

        if(!$type) return array('error'=>__('文件类型错误','b2'));

        $supported_mimes = '';

        if($type == 'image'){
            $supported_mimes  = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon','image/svg' );
        }
        
        if($type == 'video'){
            $supported_mimes  = array( 'video/mp4', 'video/x-ms-asf', 'video/x-ms-wmv', 'video/x-ms-wmx', 'video/x-ms-wm', 'video/avi','video/divx','video/x-flv','video/quicktime','video/mpeg','video/ogg','video/webm','video/x-matroska','video/3gpp','video/3gpp2');
        }

        if(!$supported_mimes) return array('error'=>__('参数错误','b2'));

        $attachment_query   = array(
            'post_type'         => 'attachment',
            'post_status'       => 'inherit',
            'post_mime_type'    => $supported_mimes,
            'posts_per_page'    => 12,
            'post__not_in' => array(1),
            'author'=>$user_id,
            'offset'=>$offset
        );

        $the_query = new \WP_Query($attachment_query);

        $arr = array();
        $_pages = 0;
        
        if ( $the_query->have_posts() ){
            $_pages = $the_query->max_num_pages;
            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                $att_url = wp_get_attachment_url($the_query->post->ID);
                
                $arr[] = array(
                    'id'=>$the_query->post->ID,
                    'att_url'=>$att_url,
                    'thumb'=>$type == 'image' ? b2_get_thumb(array('thumb'=>$att_url,'width'=>100,'height'=>100)) : ''
                );
            } 
            wp_reset_postdata();
        }
        
        return array(
            'pages'=>$_pages,
            'data'=>$arr
        );
    }

    public static function check_user_role($user_id,$type){

        //检查是否在小黑屋
        $dark_room = (int)get_user_meta($user_id,'b2_dark_room',true);
        if($dark_room) return false;

        //获取用户当前等级
        $lv = get_user_meta($user_id,'zrz_lv',true);
        $vip = get_user_meta($user_id,'zrz_vip',true);

        if(user_can($user_id, 'administrator' ) || user_can( $user_id, 'editor' )) return true;

        //获取权限
        $roles = self::get_user_roles();

        // return array('error'=>$roles);

        if($lv && isset($roles[$lv])){
            $role = $roles[$lv];
            $role = $role['user_role'];
            
            if(is_array($role) && in_array($type,$role)) return true;
        }

        if($vip && isset($roles[$vip])){
            $role = $roles[$vip];
            $role = $role['user_role'];

            if(is_array($role) && in_array($type,$role)) return true;
        }

        if(get_user_meta($user_id,'b2_title',true)){
            $role = b2_get_option('verify_main','verify_user_role');
            if(is_array($role) && in_array($type,$role)) return true;
        }
        
        return false;
    }

    //检查用户上传媒体文件权限
    public static function check_user_media_role($user_id,$type){

        // if(isset($GLOBALS['b2_check_user_media_role_'.$user_id.'_'.$type])) return $GLOBALS['b2_check_user_media_role_'.$user_id.'_'.$type];

        //检查是否在小黑屋
        $dark_room = (int)get_user_meta($user_id,'b2_dark_room',true);
        if($dark_room) return false;

        //获取用户当前等级
        $lv = get_user_meta($user_id,'zrz_lv',true);
        $vip = get_user_meta($user_id,'zrz_vip',true);

        if(user_can($user_id, 'administrator' ) || user_can( $user_id, 'editor' )) return true;

        $roles = self::get_user_roles();

        if($type == 'infomation'){
            $role = b2_get_option('infomation_submit','po_role');
            $role = is_array($role) ? $role : array();
        }elseif($type == 'ask'){
            $role = b2_get_option('ask_submit','po_role');
            $role = is_array($role) ? $role : array();
        }elseif($type == 'answer'){
            $role = b2_get_option('ask_submit','po_answer_role');
            $role = is_array($role) ? $role : array();
        }else{
            $role = b2_get_option('normal_write','write_'.$type.'_role');
            $role = is_array($role) ? $role : array();
        }

        if($lv && isset($roles[$lv])){
            if(in_array($lv,$role)){
                $GLOBALS['b2_check_user_media_role_'.$user_id.'_'.$type] = true;
                return true;
            }
        }

        if($vip && isset($roles[$vip])){
            if(in_array($vip,$role)){
                $GLOBALS['b2_check_user_media_role_'.$user_id.'_'.$type] = true;
                return true;
            }
        }
        
        if(get_user_meta($user_id,'b2_title',true)){
            if(in_array('verify',$role)){
                $GLOBALS['b2_check_user_media_role_'.$user_id.'_'.$type] = true;
                return true;
            }
        }

        // $GLOBALS['b2_check_user_media_role_'.$user_id.'_'.$type] = false;
        
        return false;
    }

    //检查用户
    public static function check_user_write_role(){
        $user_id = b2_get_current_user_id();

        return array(
            'video'=>self::check_user_media_role($user_id,'video'),
            'file'=>self::check_user_media_role($user_id,'file'),
            'image'=>self::check_user_media_role($user_id,'image'),
        );
    }

    //批量检查是否关注
    public static function check_follow_by_ids($ids){

        $ids = (array)$ids;

        $user_id = b2_get_current_user_id();
        $data = array();

        if(!$user_id){
            foreach($ids as $id){
                $data[$id] = false;
            }
        }else{
            foreach($ids as $id){
                $follow = get_user_meta($user_id,'zrz_follow',true);
                $follow = is_array($follow) ? $follow : array();

                $data[$id] = in_array($id,$follow);
            }
        }

        return $data;
    }

    public static function get_top_data($data){

        $args = array(
            'number' => 20,
            'order' => 'DESC',
            'meta_key' => 'zrz_credit_total',
            'orderby'   => 'meta_value_num',
        );

        if(!isset($data['number']) || (int)$data['number'] > 30 || (int)$data['number'] <= 0) $data['number'] = 20;

        if(isset($data['number']) && $data['number'] < 50){
            $args['number'] = $data['number'];
        }

        if(isset($data['exclude']) && count($data['exclude']) < 50){
            $args['exclude'] = $data['exclude'];
        }

        $user_query = new \WP_User_Query( $args );
        $i=0;
        $data = array();
        
        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) { 
                $i++; 
                $credit = get_user_meta($user->ID,'zrz_credit_total',true);
                $credit = $credit ? $credit : 0;

                $_data = self::get_user_public_data($user->ID,true);

                $_data['credit'] = b2_number_format($credit);

                if($_data['desc'] === ''){
                    $_data['desc'] = __('这个人很懒，什么都没有留下！','b2');
                }
                $data[] = $_data;
            }
        }

        return $data;
    }

    public static function get_user_collection_count($user_id,$type){
        $collection = get_user_meta($user_id,'zrz_user_favorites',true);
        if(isset($collection[$type])){
            return count($collection[$type]);
        }
        return 0;
    }

    public static function user_count() { 

        if(!function_exists('get_user_count')) {
            return (int) get_network_option( null, 'user_count', -1 );
        }

        return get_user_count();
    }

    public static function get_dark_room_users($paged,$type){
        $current_user_id = b2_get_current_user_id();

        $is_admin = user_can($current_user_id, 'administrator' );

        $paged = (int)$paged;
        $number = 20;

        $offset = ($paged - 1)*$number;

        $args = array(
            'number' => $number,
            'paged'=>$paged,
            'offset'=>$offset,
            'meta_key'=>'b2_dark_room_start_date',
            'order'=>'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'b2_dark_room',
                    'value' => 1,
                    'compare' => '='
                )
            )
        );

        if($type){
            if($type == 'ls'){
                $args['meta_query'][] = array(
                    'key' => 'b2_dark_room_days',
                    'value' => 0,
                    'compare' => '>'
                );
            }elseif($type == 'yy'){
                $args['meta_query'][] = array(
                    'key' => 'b2_dark_room_days',
                    'value' => 0,
                    'compare' => '='
                );
            }
        }

        $user_query = new \WP_User_Query( $args );
        $i=0;
        $data = array(
            'data'=>array(),
            'count'=>0,
            'pages'=>0
        );
        
        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) { 
                $days = get_user_meta($user->ID,'b2_dark_room_days',true);
                $start_date = get_user_meta($user->ID,'b2_dark_room_start_date',true);
                
                $end_date = wp_date("Y-m-d H:i:s", wp_strtotime($start_date.' +'.$days.' day'));

                if($end_date < current_time('mysql')){
                    $end_date = __('下次来访时','b2');
                }

                $data['data'][] = array(
                    'user'=>self::get_user_public_data($user->ID,true),
                    'days'=>$days,
                    'end_date'=>$end_date,
                    'start_date'=>b2_timeago($start_date),
                    'why'=>get_user_meta($user->ID,'b2_dark_room_why',true)
                );
            }

            $data['count'] = $user_query->get_total();
            $data['pages'] = ceil($data['count'] / $number);

        }

        return $data;
    }

    public static function is_date($date){
        $is_date=wp_strtotime($date)?wp_strtotime($date):false;
     
        if($is_date===false&&$date!=""){
           return false;
     
        }

        return true;
     
    }

    public static function shield_author($request){

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        if(!isset($request['id']) || !isset($request['type'])){
            return array('error'=>__('参数错误','b2'));
        }

        $id = (int)$request['id'];

        if($request['type'] !== 'comment' && $request['type'] !== 'post') return array('error'=>__('参数错误','b2'));

        if($request['type'] == 'comment'){
            $comment = get_comment($id);

            $author_id = isset($comment->user_id) && $comment->user_id ? $comment->user_id : 0;
        }else{
            $author_id = get_post_field( 'post_author',$id );
        }

        $author_id = (int)$author_id;

        if($user_id == $author_id) return array('error'=>__('不能屏蔽自己','b2'));

        if(!$author_id){
            return array('error'=>__('游客，无法屏蔽','b2'));
        }

        $user_shields = get_user_meta($user_id,'b2_shields',true);

        if($user_shields){
            if(in_array($author_id,$user_shields)) return array('error'=>__('您已经屏蔽了此用户','b2'));
            array_push($user_shields,$author_id);
        }else{
            $user_shields = [$author_id];
        }

        update_user_meta($user_id,'b2_shields',$user_shields);

        return $author_id;
    }

    public static function check_post($user_id){

        if(!$user_id) return array('error'=>__('登录后操作','b2'));

        if(user_can( $user_id, 'manage_options' )) return true;

        $count = b2_get_option('normal_safe','post_count');

        $has_post_count = (int)wp_cache_get('b2_post_limit_'.$user_id,'b2_post_limit');

        if($has_post_count >= $count) return array('error'=>__('非法操作','b2'));

        // wp_cache_set('b2_post_limit_'.$user_id,($has_post_count + 1),'b2_post_limit',HOUR_IN_SECONDS*3);

        return true;
    }

    public static function save_check_post_count($user_id){
        $has_post_count = (int)wp_cache_get('b2_post_limit_'.$user_id,'b2_post_limit');
        if ($has_post_count === 0) {
            wp_cache_set('b2_post_limit_'.$user_id,1,'b2_post_limit',HOUR_IN_SECONDS*3);
        } else {
            wp_cache_incr('b2_post_limit_'.$user_id,1,'b2_post_limit');
        }
    }
}