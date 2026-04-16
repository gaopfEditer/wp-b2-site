<?php namespace B2\Modules\Common;

use B2\Modules\Common\Post;
use B2\Modules\Templates\Single;
/**
 * dplayer播放器
 *
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
class Player {
    static $default_poster = '';
    static $default_logo = '';
    private $_instance = 0;

	public function init() {
        //add_shortcode( 'player', array( $this, 'player_shortcode' ) );
        add_shortcode( 'b2player', array( $this, 'player_shortcode' ) );
        add_shortcode( 'b2player_mp', array( $this, 'player_shortcode_mp' ) );
        add_action('template_redirect',array(__CLASS__,'shortcode_head'));
        self::$default_poster = b2_get_option('normal_main','default_video_poster');
        self::$default_logo = b2_get_option('normal_main','img_logo_white');
    }

    public static function get_doge_video($data){

        $dogeId = $data['id'];

        if($data['token'] !== md5($dogeId.AUTH_KEY)){
            return array('error'=>__('参数错误，无法获取视频播放地址1','b2'));
        }

        if(is_numeric($dogeId)){
            $id = 'vid='.$dogeId;
        }else{
            $id = 'vcode='.$dogeId;
        }

        $video_res = self::dogecloud_api('/video/streams.json?platform=wap&'.$id.'&ip='.b2_get_user_ip());

        if(!$video_res){
            return array('error'=>__('参数错误，无法获取视频播放地址2','b2'));
        }

        if(isset($video_res['code']) && $video_res['code'] != 200){
            return array('error'=>__('参数错误，无法获取视频播放地址3','b2'));
        }

        $data = [];

        $videos = $video_res['data']['stream'];
        if(!empty($videos)){
            foreach ($videos as $k => $v) {
                if($k == 0){
                    return $v['url'];
                    break;
                }
            }
        }

        return array('error'=>__('没有视频','b2'));
    }

    public static function dogecloud_api($apiPath, $data = array(), $jsonMode = false) {

        $accessKey = b2_get_option('template_single','doge_accessKey');
        $secretKey = b2_get_option('template_single','doge_secretKey');

        $body = $jsonMode ? json_encode($data) : http_build_query($data);
        $signStr = $apiPath . "\n" . $body;
        $sign = hash_hmac('sha1', $signStr, $secretKey);
        $Authorization = "TOKEN " . $accessKey . ":" . $sign;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.dogecloud.com" . $apiPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 如果是本地调试，或者根本不在乎中间人攻击，可以把这里的 1 和 2 修改为 0，就可以避免报错
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 建议实际使用环境下 cURL 还是配置好本地证书
        if(isset($data) && $data){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . ($jsonMode ? 'application/json' : 'application/x-www-form-urlencoded'),
            'Authorization: ' . $Authorization
        ));
        $ret = curl_exec($ch);
        curl_close($ch);
        return json_decode($ret, true);
    }
    
    /**
     * 如果文章中存在视频段代码，则加载JS
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function shortcode_head(){
        if(is_singular()){
            global $post;
            if (isset($post->post_content) && (strpos($post->post_content,'[player') !== false || strpos($post->post_content,'[b2player') !== false || Single::get_single_post_settings($post->ID,'single_post_style') === 'post-style-5')) {

                wp_enqueue_script('player-hls',B2_THEME_URI.'/Assets/fontend/library/hls.min.js', array(), "1.1.3", true );
                
                wp_enqueue_script('player-flv',B2_THEME_URI.'/Assets/fontend/library/flv.min.js', array(), "1.1.3", true );
                
                
                wp_enqueue_script('player',B2_THEME_URI.'/Assets/fontend/library/DPlayer.min.js', array(), "1.1.3", true );
                //wp_enqueue_style('player-css',B2_THEME_URI.'/Assets/fontend/library/DPlayer.min.css', array(), "1.1.3", 'all' );
            }
        }
    }

    public function player_shortcode_mp($atts = array(), $content = '' ){

        $mimes = array(
            '.mp4',
            '.mov',
            '.m4v',
            '.3gp',
            '.avi',
            '.m3u8',
            '.webm'
        );

        $url = isset($atts['src']) ? $atts['src'] : '';

        $allow = false;

        foreach ($mimes as $k => $v) {
            if(strpos($url,$v) !== false){
                $allow = true;
                break;
            }
        }

        if($allow){

            //视频封面
            $poster = isset($atts['poster']) && $atts['poster'] ? $atts['poster'] : self::$default_poster;

            if(isset($atts['poster']) && $atts['poster']){
                $poster = $atts['poster'];
            }else{

                if(isset($atts['id'])){
                    $thumb = Post::get_post_thumb($atts['id']);
                }

                $poster = $thumb ? $thumb : b2_get_option('normal_main','default_video_poster');

            }

            return '<video width="100%" class="b2videobox" id="myVideo" poster="'.$poster.'" src="'.$url.'" controls></video>';
        }else{

            if (strpos($_SERVER['HTTP_USER_AGENT'], 'miniprogram') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'wechatdevtools') !== false) {

                if(strpos($url,'v.qq.com') !== false){
                    return apply_filters('the_content', $url);
                }else{
                    return '<p><a href="'.$url.'">复制视频网址</a></p>';
                }
            }

            return apply_filters('the_content', $url);
        }

        return '';
        
    }

	public function player_shortcode( $atts = array(), $content = '' ) {

        $this->_instance++;
        
        //视频地址
        $url = isset($atts['src']) ? $atts['src'] : '';

        //视频封面
        $poster = isset($atts['poster']) && $atts['poster'] ? $atts['poster'] : self::$default_poster;

        if(isset($atts['poster']) && $atts['poster']){
            $poster = $atts['poster'];
        }else{

            global $post;

            $thumb = '';

            if(isset($post->ID)){
                $thumb = Post::get_post_thumb($post->ID);
            }

            $poster = $thumb ? $thumb : b2_get_option('normal_main','default_video_poster');

        }

        

        $vodeo_dom = apply_filters('the_content', $url);

        //外链视频
        if(strpos($vodeo_dom,'class="smartideo"') !== false){
            $data = self::get_video_thumb($url);

            return '<div class="content-video-box" data-video-url="'.$url.'">
                <div class="img-bg"><img src="'.(isset($atts['poster']) && $atts['poster'] ? $atts['poster'] : $data['thumb']).'" /></div>
                <div class="video-title">'.$data['title'].'</div>
            </div>';
        
        //本地视频
        }else{
            if(!$url) return '';

            $data = array(
                'url'=>$url,
                'poster'=>$poster,
                'logo'=>self::$default_logo
            );
            $html = '<div id="player-'.$this->_instance.'" class="b2-player" data-video=\''.json_encode($data).'\'></div>';
    
            
            // $footer_script = '<script type="text/javascript">';
            // $footer_script .= sprintf("
            //     let player%u = new DPlayer({
            //         container: document.getElementById('player-%u'),
            //         screenshot: false,
            //         mutex:true,
            //         hotkey:true,
            //         video: {
            //             url: '%s',
            //             pic: '%s',
            //         },
            //         logo:'%s',
            //         autoplay:false
            //     });
            //     player%u.on('play',()=>{
            //         document.getElementById('player-%u').querySelectorAll('.dplayer-video-current')[0].style=\"object-fit:contain\"
            //     })
            //     ",
            //     $this->_instance,
            //     $this->_instance,
            //     $url,
            //     $poster,
            //     self::$default_logo,
            //     $this->_instance,
            //     $this->_instance
            // );
    
            // $footer_script .= '</script>';
    
            // add_action( 'wp_footer', function () use ( $footer_script ) {
            //     echo '		' . $footer_script . "\n";
            // }, 99999 );
    
            return $html;
        }
    }
    
    public static function strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; 
        }
        return false;
    }

    public static function get_video_thumb($url){
        
        global $post;
        $post_id = $post->ID;

        $video_data = get_post_meta($post_id,'b2_'.md5($url).'_video_thumb',true);
        
        //如果存在封面，直接返回
        if($video_data) return array(
            'title'=>$video_data['title'],
            'thumb'=>$video_data['thumb']
        );

        $data = self::get_video_thumb_url($url);

        if(!$data){
            $thumb = Post::get_post_thumb($post_id);

            return array(
                'title'=>'',
                'thumb'=>$thumb ? $thumb : b2_get_option('normal_main','default_video_poster')
            );
        }else{
            update_post_meta($post_id,'b2_'.md5($url).'_video_thumb',$data);
            return $data;
        }
    }

    public static function get_url_content($gurl){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$gurl);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $result=curl_exec($ch);
        return $result;
    }

    public static function g($uc,$data,$datam){
        preg_match($data,$uc,$m);
        return $m[$datam];
    }

    public static function get_video_thumb_url($_url){

        if(!$_url){
            return false;
        }

        $url = parse_url($_url);
        $bigThumbnail = '';
    
        //优酷
        if($url['host']=="v.youku.com"){
            $youkuid = self::g($url['path'],'|/v_show/id_(.*).html|i',1);
            $youkupic = json_decode(self::get_url_content("https://api.youku.com/videos/show.json?client_id=58263aed0903a6d8&video_id=$youkuid"),true);
            $title = $youkupic['title'];
            $bigThumbnail = $youkupic['bigThumbnail'];

        }
        //土豆
        elseif($url['host']=="new-play.tudou.com" || $url['host']=="video.tudou.com"){
            $tudouid = self::g($url['path'],'|/v/(.*).html|i',1);
            $tudoupic = json_decode(self::get_url_content("https://api.youku.com/videos/show.json?client_id=58263aed0903a6d8&video_id=$tudouid"),true);
            $title = $tudoupic['title'];
            $bigThumbnail = $tudoupic['bigThumbnail'];
        }
        //qq
        elseif($url['host']=="www.iqiyi.com"){
            $qiyiidurl = self::get_url_content($_url);
            preg_match_all("|<meta itemprop=\"image\" content=\"(.*)\"\/>|U", $qiyiidurl, $regs2);//获取网站大图
            preg_match("/<title>(.+)<\/title>/i", $qiyiidurl, $matches);
            $bigThumbnail = $regs2[1][0];
            if(strpos($bigThumbnail,'http') === false){
                $bigThumbnail = 'http:'.$regs2[1][0];
            }
            $title = $matches[1];
        }
        //bili
        // elseif($url['host']=="www.bilibili.com"){
        //     $biliidurl = self::get_url_content($_url);
        //     preg_match_all('|<meta data-vue-meta="true" property="og:image" content=\"(.*)\">|U', $biliidurl, $regs2);//获取网站大图
        //     preg_match("/<title data-vue-meta=\"true\">(.+)<\/title>/i", $biliidurl, $matches);

        //     $bigThumbnail = $regs2[1][0];
        //     $title = $matches[1];

        //     unset($matches);
        //     unset($regs2);
        //     unset($biliidurl);
        // }
        //acfun
        elseif($url['host']=="www.acfun.cn" || $url['host']=="v.hapame.com"){
            $acfunidurl = self::get_url_content($_url);
            preg_match_all("|coverImage\":\"(.*)\",\"|U", $acfunidurl, $regs2);//获取网站大图
            preg_match("/<title>(.+)<\/title>/i", $acfunidurl, $matches);
            $bigThumbnail = $regs2[1][0];
            $title = $matches[1];
        }
        //秒拍
        // elseif($url['host']=="www.miaopai.com"){
        //     $miaopaiidurl = self::get_url_content($_url);
        //     preg_match_all("|\"poster\":\"(.*)\"|U", $miaopaiidurl, $regs2);//获取网站大图
        //     preg_match("/<title>(.+)<\/title>/i", $miaopaiidurl, $matches);
        //     $bigThumbnail = $regs2[1][0];
        //     $title = $matches[1];

        //     unset($matches);
        //     unset($regs2);
        //     unset($miaopaiidurl);
        // }
        //无效地址返回
        else{
            return false;
        }

        if($bigThumbnail){
            return array(
                'thumb'=>strpos($bigThumbnail,'https://') !== false ? $bigThumbnail : str_replace('http://','https://',$bigThumbnail),
                'title'=>$title
            );
        }
        return false;
    }

    /**
     * 检查当前文章的视频播放权限
     *
     * @param [type] $post_id
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function check_video_allow($post_id,$order_id = ''){

        $role = array(
            'allow'=>false,
            'role'=>array(
                'type'=>'',
                'value'=>''
            )
        );

        //获取视频的设置权限
        $video_role = get_post_meta($post_id,'b2_single_post_video_role',true);

        $current_user_id = b2_get_current_user_id();

        $dark_room = (int)get_user_meta($current_user_id,'b2_dark_room',true);

        if($dark_room){
            
            $role = array(
                'allow'=>false,
                'role'=>array(
                    'type'=>'dark_room',
                    'value'=>''
                )
            );
        }else{
            //如果是vip
            $vip = get_user_meta($current_user_id,'zrz_vip',true);
            if($vip){
                $vip_data = b2_get_option('normal_user','user_vip_group');
                $vip = (string)preg_replace('/\D/s','',$vip);

                if(isset($vip_data[$vip]) && $vip_data[$vip]['allow_videos'] === '1'){
                    $role = array(
                        'allow'=>true,
                        'role'=>array(
                            'type'=>'vip',
                            'value'=>''
                        )
                    );
                }
            }

            //如果无限制
            if($video_role === 'none' && $role['allow'] !== true){
                $role = array(
                    'allow'=>true,
                    'role'=>array(
                        'type'=>'free',
                        'value'=>''
                    )
                );
            }

            //如果是登陆才可观看
            if($video_role === 'login' && $role['allow'] !== true){
                if($current_user_id){
                    $role = array(
                        'allow'=>true,
                        'role'=>array(
                            'type'=>'login',
                            'value'=>true
                        )
                    );
                }else{
                    $role = array(
                        'allow'=>false,
                        'role'=>array(
                            'type'=>'login',
                            'value'=>false
                        )
                    );
                }
            }

            //如果是评论可见
            if($video_role === 'comment' && $role['allow'] !== true){
                //如果是游客
                // if(!$current_user_id){
                //     $commenter = wp_get_current_commenter();
                //     if(!empty($commenter['comment_author_email'])){
                //         $allow = true;
                //     }
                //     unset($commenter);

                // //如果不是游客，检查是否在文章中评论过
                // }else{

                    $args = array( 
                        'user_id' => $current_user_id, 
                        'post_id' => $post_id
                    );

                    $comment = get_comments($args);

                    if(!empty($comment)){
                        $role = array(
                            'allow'=>true,
                            'role'=>array(
                                'type'=>'comment',
                                'value'=>true
                            )
                        );
                    }else{
                        $role = array(
                            'allow'=>false,
                            'role'=>array(
                                'type'=>'comment',
                                'value'=>false
                            )
                        );
                    }

                //}
            }

            //如果是积分支付
            if(($video_role === 'credit' || $video_role === 'money') && $role['allow'] !== true){

                $can_guset_pay = get_post_meta($post_id,'b2_video_guest_buy',true);

                $money = get_post_meta($post_id,'b2_single_post_video_money',true);

                $credit = get_post_meta($post_id,'b2_single_post_video_credit',true);

                $video_payed = get_post_meta($post_id,'b2_video_pay',true);

                $video_payed = is_array($video_payed) ? $video_payed : array();

                if(!$current_user_id && $order_id){
                    $role = array(
                        'allow'=>apply_filters('b2_get_video_allow', array('post_id'=>$post_id,'order_id'=>$order_id,'order_key'=>0,'order_type'=>'v')),
                        'role'=>array(
                            'type'=>$video_role,
                            'value'=>$video_role == 'credit' ? $credit : $money
                        ),
                        'can_guest'=>$can_guset_pay
                    );
                }else{
                    if(in_array($current_user_id,$video_payed)){
                        $role = array(
                            'allow'=>true,
                            'role'=>array(
                                'type'=>$video_role,
                                'value'=>$video_role == 'credit' ? $credit : $money
                            ),
                            'can_guest'=>$can_guset_pay
                        );
                    }else{
                        $role = array(
                            'allow'=>false,
                            'role'=>array(
                                'type'=>$video_role,
                                'value'=>$video_role == 'credit' ? $credit : $money
                            ),
                            'can_guest'=>$can_guset_pay
                        );
                    }
                }

                
            }

            //如果是限制用户组查看
            if($video_role === 'roles' && $role['allow'] !== true){
                $lv = get_user_meta($current_user_id,'zrz_lv',true);
                $vip = get_user_meta($current_user_id,'zrz_vip',true);

                $roles = get_post_meta($post_id,'b2_single_post_video_roles',true);
                $roles = is_array($roles) ? $roles : array();

                $role_html = array();
                foreach ($roles as $k => $v) {
                    $role_html[] = User::get_lv_icon($v);
                }

                if(in_array($lv,$roles) || in_array($vip,$roles)){
                    $role = array(
                        'allow'=>true,
                        'role'=>array(
                            'type'=>'role',
                            'value'=>$role_html
                        )
                    );
                }else{
                    $role = array(
                        'allow'=>false,
                        'role'=>array(
                            'type'=>'role',
                            'value'=>$role_html
                        )
                    );
                }
            }

            //如果是作者本人，或者是管理员，直接查看
            $post_author = get_post_field('post_author',$post_id);

            if(($post_author == $current_user_id || user_can($current_user_id, 'administrator' )) && $role['allow'] !== true){
                $role = array(
                    'allow'=>true,
                    'role'=>array(
                        'type'=>'current',
                        'value'=>true
                    )
                );
            }
        }

        //获取视频列表
        $videos = get_post_meta($post_id,'b2_single_post_video_group',true);
        if(!is_array($videos)) return array(
            'user'=>false,
            'videos'=>array(),
            'list'=>array()
        );

        $data = array();
        $list = array();
        $i = 0;
        foreach($videos as $k => $v){

            $h2 = isset($v['title']) ? substr($v['title'],0,strrpos($v['title'],'|')) : '';
            $v['title'] = str_replace($h2.'|','',$v['title']);

            $data[] = array(
                'h2'=>$h2,
                'title'=>isset($v['title']) ? $v['title'] : '',
                'poster'=>isset($v['poster']) ? $v['poster'] : '',
                'url'=>$role['allow'] ? $v['url'] : '',
                'view'=>$v['view_url'],
                'id'=>isset($v['dogecloud_id']) ? $v['dogecloud_id'] : 0,
                'token'=>$role['allow'] === true && isset($v['dogecloud_id']) ? md5($v['dogecloud_id'].AUTH_KEY) : ''
            );

            if($i != 0){
                $list[] = apply_filters('b2_video_url',$role['allow'], $role['allow'] ? $v['url'] : $v['view_url']);
            }
            
            $i++;
        }

        return array(
            'title'=>b2_get_des(0,100,get_the_title($post_id)),
            'user'=>$role,
            'videos'=>$data,
            'list'=>$list,
            'auto'=>(bool)get_post_meta($post_id,'b2_single_post_video_auto_pay',true)
        );
        
    }  
    
}