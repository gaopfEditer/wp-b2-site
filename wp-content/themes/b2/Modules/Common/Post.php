<?php namespace B2\Modules\Common;

use B2\Modules\Common\User;
use \Firebase\JWT\JWT;
use B2\Modules\Common\PostRelationships;
use B2\Modules\Common\Circle;
use B2\Modules\Common\Wecatmp;
use B2\Modules\Common\Ask;

class Post{

    public static $upload_dir;

    public function init(){
        //add_action('b2_single',array(__CLASS__,'single_content'),10);

        //隐藏主题自带的自定义字段

        //add_filter( 'post_gallery', array($this,'post_gallery'), 10, 2 );
        //add_action( 'save_post',array(__CLASS__,'save_post_qrcode'),10,3);

        self::$upload_dir = apply_filters('b2_upload_path_arg',wp_upload_dir());

        //add_filter('post_link', array(__CLASS__,'post_link'),9,3);

        add_action(  'transition_post_status', array($this,'publish_post'), 999, 3 );
        add_filter( 'wp_insert_post_data',array($this,'insert_post_data'),10, 2);

        //懒加载
        if(b2_get_option('normal_write','write_image_lazyload') && !B2_AUTH){
            add_filter ('the_content', array($this,'b2_lazyload'));
        }
        //add_filter( 'render_block', array($this,'b2_lazyload'),0);

        // if((int)b2_get_option('template_main','prettify_load')){
        //     add_action( 'wp_enqueue_scripts', array($this,'b2_highlight_styles'),99 );
        // }

    }

    public function b2_highlight_styles() {
        wp_enqueue_script( 'prettify', B2_THEME_URI.'/Assets/fontend/library/prettify.min.js', array(), B2_VERSION , true );
    }

    function innerHTML($node) {
        return implode(array_map([$node->ownerDocument,"saveHTML"], 
                                 iterator_to_array($node->childNodes)));
    }

    public static function img_replace($img){
        $r = b2_get_option('normal_write','write_image_replace');

        if(!$r) return $img;

        $r = trim($r, " \t\n\r\0\x0B\xC2\xA0");
        if($r){
            $row = explode(PHP_EOL,$r);
            if(!empty($row)){
                $arr = [];
                foreach ($row as $k => $v) {
                    $v = trim($v, " \t\n\r\0\x0B\xC2\xA0");
                    
                    $url = explode('|',$v);
     
                    if(isset($url[1])){
                        if(strpos($img,$url[0]) !== false) return str_replace($url[0],$url[1],$img);
                    }
                }
            }
        }

        return $img;
    }

    public static function b2_lazyload_action($content) {
        
        // return mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        $doc = new \DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD);
        $img = $doc->getElementsByTagName('img');

        $post_id = 0;
        $title = '';

        global $post;
        if(isset($post->ID)){
            $post_id = $post->ID;
            $title = $post->post_title;
        }

        if(count($img) > 0){
           foreach ($img as $k => $v) {

                $url = self::img_replace($v->getAttribute('src'));

                $v->setAttribute(
                    'data-src',
                    $url
                );

                $v->setAttribute(
                    'src',
                    B2_DEFAULT_IMG
                );

                if($title){
                    $v->setAttribute(
                        'alt',
                        $title
                    );
                }
                
                $v->setAttribute(
                    'class',
                    $v->getAttribute('class').' lazy'
                );
                
           }
        }

        // $doc->encoding = 'UTF-8';

        $content = $doc->saveHTML($doc->documentElement);
        $saved_dom = trim($content);
        $start_dom = stripos($saved_dom,'<body>')+6;
        $html = substr($saved_dom,$start_dom,strripos($saved_dom,'</body>') - $start_dom );
        return $html;
    }

    public function b2_lazyload($content) {

        if(!is_singular()) return $content;
        if($content == '') return $content;
        return self::b2_lazyload_action($content);
    }

    public function insert_post_data($data,$postarr){
        if(!isset($postarr['ID'])) return $data;
        if(get_post_meta($postarr['ID'],'b2_auto_title',true) && $data['post_title'] != get_the_title($postarr['ID'])){
            delete_post_meta($postarr['ID'],'b2_auto_title');
        }

        return $data;
    }

    //文章发布触发消息
    public function publish_post($new_status, $old_status, $post){
        //文章作者
        $user_id = $post->post_author;
        $post_type = $post->post_type;
        $credit = 0;
        delete_post_meta($post->ID,'b2_post_exp');
        delete_post_meta($post->ID,'b2_post_thumb');

        $msg_title = '';

        $has_add_credit = get_post_meta($post->ID,'zrz_credit_add',true);

        switch ($post_type) {
            case 'shop' :
                \B2\Modules\Common\Cache::clean_index_module_cache();
                break;
            case 'post':
                \B2\Modules\Common\Cache::clean_index_module_cache();

                if($new_status !== 'publish') return;

                $task = Task::update_task($user_id,'task_post');
                if($task && !$has_add_credit){
                    $credit = b2_get_option('normal_gold','credit_post');
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$user_id,
                        'gold_type'=>0,
                        'no'=>$credit,
                        'post_id'=>$post->ID,
                        'msg'=>__('您发布了一篇文章：${post_id}','b2'),
                        'type'=>'po_post',
                        'type_text'=>__('投稿奖励','b2')
                    ]);

                    update_post_meta($post->ID,'zrz_credit_add',1);
                }

                $msg_title = __('文章','b2');

                break;
            case 'newsflashes':
                if($new_status !== 'publish') return;

                $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

                $task = Task::update_task($user_id,'task_newsflashes');
                if($task && !$has_add_credit){
                    $credit = b2_get_option('normal_gold','credit_newsflashes');
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$post->post_author,
                        'gold_type'=>0,
                        'no'=>$credit,
                        'post_id'=>$post->ID,
                        'msg'=>sprintf(__('您发布了一个%s：${post_id}','b2'),$newsflashes_name),
                        'type'=>'po_newsflashes',
                        'type_text'=>__('发帖奖励','b2'),
                    ]);
                    update_post_meta($post->ID,'zrz_credit_add',1);
                }

                $msg_title = $newsflashes_name;

                break;
            case 'circle':
                if($new_status !== 'publish') return;

                $circle_id = Circle::get_circle_id_by_topic_id($post->ID);
                $role = get_term_meta($circle_id,'b2_circle_read',true);

                if($role){
                    if($role === 'public'){
                        delete_post_meta($post->ID,'b2_currentCircle');
                    }else{
                        update_post_meta($post->ID,'b2_currentCircle',true);
                    }
                }

                $circle_name = b2_get_option('normal_custom','custom_circle_name');
                
                $task = Task::update_task($user_id,'task_circle');

                if($task && !$has_add_credit){
                    $credit = b2_get_option('normal_gold','credit_topic');
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$post->post_author,
                        'gold_type'=>0,
                        'no'=>$credit,
                        'post_id'=>$post->ID,
                        'msg'=>sprintf(__('您发布了一个%s帖子：${post_id}','b2'),$circle_name),
                        'type'=>'po_circle',
                        'type_text'=>__('发帖奖励','b2')
                    ]);
                    update_post_meta($post->ID,'zrz_credit_add',1);
                }   

                $msg_title = sprintf(__('%s帖子','b2'),$circle_name);

                $data = get_post_meta($post->ID,'b2_topic_ask_pending_data',true);
                if($data){
                    $data['status'] = 'publish';
                    apply_filters( 'b2_insert_ask_action', $data);
                }
                delete_post_meta($post->ID,'b2_topic_ask_pending_data');

                do_action('b2_rebuild_hotness',$post->ID);

                break;

            case 'infomation':

                if($new_status !== 'publish') return;

                $author_id = get_post_field( 'post_author', $post->ID );
                if($author_id){
                    $term = get_the_terms($post->ID, 'infomation_cat');
                    if(isset( $term[0] )){
                        delete_term_meta((int)$term[0]->term_id,'b2_infomation_count');
                    }
                    
                    delete_user_meta($author_id,'b2_infomation_counts');
                }

                $infomation_name = b2_get_option('normal_custom','custom_infomation_name');
                $task = Task::update_task($user_id,'task_infomation');

                if($task || !$has_add_credit){
                    
                    $credit = b2_get_option('normal_gold','credit_infomation');
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$post->post_author,
                        'gold_type'=>0,
                        'no'=>$credit,
                        'post_id'=>$post->ID,
                        'msg'=>sprintf(__('您发布了一个%s帖子：${post_id}','b2'),$infomation_name),
                        'type'=>'po_infomation',
                        'type_text'=>__('发帖奖励','b2')
                    ]);

                    update_post_meta($post->ID,'zrz_credit_add',1);
                }

                $msg_title = sprintf(__('%s帖子','b2'),$infomation_name);

                break;
            case 'announcement':
                delete_option('b2_latest_announcement');
                break;
            case 'ask':

                if($new_status == 'trash'){
                    $reward = get_post_meta($post->ID,'b2_ask_reward',true);
                    if(isset($reward['reward']) && $reward['reward']){

                        if(!get_post_meta($post->ID,'b2_ask_has_reward',true)){
                            Ask::back_money($post->ID,__('您的帖子被删除，返还赏金','b2'));

                            delete_post_meta($post->ID,'b2_ask_passtime');
                            delete_post_meta($post->ID,'b2_ask_reward');
                        }
                        
                    }
                    
                }

                if($new_status !== 'publish') return;
                Ask::set_inv($post->ID);

                do_action('b2_ask_hotness',$post->ID);
                break;
            case 'answer':
                $parent = get_post_field( 'post_parent', $post->ID);
                if($parent){
                    delete_post_meta($parent,'b2_ask_answer_count');
                    delete_post_meta($parent,'b2_ask_answer_last');

                    if($new_status == 'publish'){
                        $parent_author = get_post_field( 'post_author',$parent);

                        Message::update_data([
                            'date'=>current_time('mysql'),
                            'from'=>$user_id,
                            'to'=>$parent_author,
                            'post_id'=>$parent,
                            'msg'=>__('${from}回答了您的问题：${post_id}','b2'),
                            'type'=>'post_up',
                            'type_text'=>__('回答','b2'),
                            'old_row'=>1
                        ]);
                    }
                }

                if($new_status == 'publish'){
                    update_post_meta($parent,'b2_has_answer',1);
                    do_action('b2_ask_hotness',$parent);
                    do_action('b2_rebuild_hotness',$post->ID);
                }
                break;
            default:
                break;
        }

        if($msg_title && $old_status == 'pending' && $post->post_author != b2_get_option('normal_weixin_message','admin_id') && !user_can($post->post_author, 'manage_options' )){
            Wecatmp::message_success([
                'type'=>'check_success',
                "touser"=>$post->post_author,
                "template_id"=>'',
                "url"=>get_permalink($post->ID),
                "data"=>[
                    "first"=> [
                        "value"=>sprintf(__('恭喜您！%s审核通过。','b2'),$msg_title),
                        "color"=>"#173177"
                    ],
                    "keyword1"=>[
                        "value"=>get_the_title($post->ID),
                        "color"=>"#173177"
                    ],
                    "keyword2"=> [
                        "value"=>'审核通过',
                        "color"=>"#173177"
                    ],
                    "keyword3"=> [
                        "value"=>current_time('mysql'),
                        "color"=>"#173177"
                    ],
                    "remark"=>[
                        "value"=>'欢迎再次投稿！',
                        "color"=>"#173177"
                    ]
                ]
            ]);
        }
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
    }

    public static function post_link($permalink, $post, $leavename){
        if(!is_admin()){
            if($post->post_status === 'pending' || $post->post_status === 'draft'){
                return b2_get_custom_page_url('write').'?id='.$post->ID;
            }
        }

        return $permalink;
    }

    /**
     * 文章保存的时候生成二维码缓存
     *
     * @param [type] $post_id
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function save_post_qrcode($post_id,$return = false){

        $post_type = get_post_type($post_id);

        if($post_type !== 'post' && $post_type !== 'shop') return;

        if(!is_file(self::$upload_dir['basedir'].B2_DS.'qrcode'.B2_DS.$post_id.'.jpg')){
            if(wp_mkdir_p(self::$upload_dir['basedir'].B2_DS.'qrcode')){

                require_once B2_THEME_DIR . '/Library/Qrcode/phpqrcode.php';
                \QRcode::png(get_permalink($post_id),self::$upload_dir['basedir'].B2_DS.'qrcode'.B2_DS.$post_id.'.jpg',QR_ECLEVEL_L,6,1,false,array(255, 255, 255, 0),array(0, 0, 0, 0));

            }else{
                return array('error'=>__('无法创建 wp-content/uploads/qrcode 目录','b2'));
            }
        }

        //保存的时候删除封面图片
        if(is_file(self::$upload_dir['basedir'].B2_DS.'posters'.B2_DS.$post_id.'.jpg')){
            unlink(self::$upload_dir['basedir'].B2_DS.'posters'.B2_DS.$post_id.'.jpg');
        }
        if($return){
            return self::$upload_dir['baseurl'].'/qrcode/'.$post_id.'.jpg';
        }
    }

    public static function time_ago($ptime,$return = false){

        if(!is_string($ptime)) return;

        $_ptime = wp_strtotime($ptime);
        $etime =  wp_strtotime(current_time( 'mysql' )) - $_ptime;

        if ($etime < 1){
            $text = __('刚刚','b2');
        }else{
            $interval = array (
                60 * 60                 =>  __('小时前','b2'),
                60                      =>  __('分钟前','b2'),
                1                       =>  __('秒前','b2')
            );

            if($etime <= 84600){
                foreach ($interval as $secs => $str) {
                    $d = $etime / $secs;
                    if ($d >= 1) {
                        $r = round($d);
                        $text = $r . $str;
                        break;
                    }
                };
            }else{

                $date = date_create($ptime);

                $y = date_format($date,"y");
                if($y == wp_date('y')){
                    $text = sprintf(__('%s月%s日','b2'),date_format($date,"n"),date_format($date,"j"));
                }else{
                    $text = sprintf(__('%s年%s月%s日','b2'),$y,date_format($date,"n"),date_format($date,"j"));
                }
            }
        }

        if($return) return $text;

        return '<time class="b2timeago" datetime="'.$ptime.'" itemprop="datePublished">'.$text.'</time>';
    }

    /**
     * 重写相册短代码图片排列部分
     *
     * @param string $output
     * @param array $attr
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function post_gallery($output, $attr){
        if('file' == $attr['link']) return $output;
        $output = "<div id=\"b2 container\" class=\"gallery grid\" data-packery='{ \"itemSelector\": \".gallery-item\", \"gutter\": 10 }'>";
        $posts = get_posts(array('include' => $attr['ids'],'post_type' => 'attachment'));

        foreach($posts as $imagePost){
            $img_big = wp_get_attachment_image_src($imagePost->ID, 'large');
            $img_m = wp_get_attachment_image_src($imagePost->ID, 'medium')[0];

            $output .= '<div class="gallery-item"> <img src="'.$img_m.'" '.(isset($src[1]) ? 'data-zooming-width="'.$img_big[1].'"' : '').' '.(isset($img_big[2]) ? 'data-zooming-height="'.$img_big[2].'"' : '').'/></div>';

        }

        $output .= "</div>";

        return $output;
    }

    public static function get_post_vote_up($post_id){
        return array(
            'up'=>PostRelationships::get_count(array('type'=>'post_up','post_id'=>$post_id)),
            'down'=>PostRelationships::get_count(array('type'=>'post_down','post_id'=>$post_id))
        );
    }

    public static function get_post_vote($post_id){
        $user_id = b2_get_current_user_id();
        return array(
            'up'=>PostRelationships::get_count(array('type'=>'post_up','post_id'=>$post_id)),
            'down'=>PostRelationships::get_count(array('type'=>'post_down','post_id'=>$post_id)),
            'isset_up' => $user_id ? PostRelationships::isset(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id)) : false,
            'isset_down' => $user_id ? PostRelationships::isset(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id)) : false
        );
    }

    /**
     * 获取文章meta内容
     *
     * @param int $post_id
     *
     * @return array 文章meta内容
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function post_meta($post_id = 0){

        if(!$post_id){
            global $post;
            if(!isset($post->ID)) return;
            $post_id = $post->ID;
        }

        $user_id = get_post_field('post_author', $post_id);

        $user_data = get_userdata($user_id);

        //获取分类信息
        $post_cats = get_the_category($post_id);
        $cats_data = array();

        foreach($post_cats as $cat){

            if(isset($cat->term_id)){
                $color = get_term_meta($cat->term_id,'b2_tax_color',true);
                $color = $color ? $color : '#607d8b';
                $link = get_category_link( $cat->term_id );

                $cats_data[] = array(
                    'name'=>$cat->name,
                    'color'=>$color,
                    'link'=>$link
                );
            }
        }

        $view = (int)get_post_meta($post_id,'views',true);

        $user_title = get_user_meta($user_id,'b2_title',true);

        $avatar = get_avatar_url($user_id, array('size'=>100));

        $date = get_the_date('Y-m-d H:i:s',$post_id);

        return apply_filters( 'b2_get_post_metas', array(
            'date'=>self::time_ago($date),
            'date_normal'=>get_the_date('Y-m-d',$post_id),
            'ctime'=>get_the_date('c',$post_id),
            '_date'=>$date,
            'verify_icon'=>$user_title ? B2_VERIFY_ICON : '',
            'user_title'=>$user_title,
            'user_id'=>$user_id,
            'user_desc'=>isset($user_data->description) ? $user_data->description : '',
            'user_name'=>isset($user_data->display_name) ? $user_data->display_name : '',
            'user_avatar'=>$avatar,
            'user_avatar_webp'=>apply_filters('b2_thumb_webp',$avatar),
            'user_link'=>get_author_posts_url($user_id),
            'cats'=>$cats_data,
            'like'=>b2_number_format(self::get_post_vote_up($post_id)['up']),
            'comment'=>b2_number_format(get_comments_number($post_id)),
            'views'=>b2_number_format($view)
        ),$post_id);

    }

    /**
     * 获取文章缩略图
     *
     * @param int $post_id
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_post_thumb($post_id = 0,$no_default = false){
        if(!$post_id){
            global $post;
            if(!isset($post->ID)) return '';
            $post_id = $post->ID;
        }

        $post_thumbnail_url = get_the_post_thumbnail_url($post_id,'full');

        if($post_thumbnail_url){
            return esc_url($post_thumbnail_url);
        }else{
            $thumb = get_post_meta($post_id,'b2_post_thumb',true);
            if($thumb) return $thumb;

            $post_content = get_post_field('post_content', $post_id);
            $thumb = b2_get_first_img($post_content);

            if(!$thumb && !$no_default){
                return b2_get_default_img();
            }else{
                update_post_meta($post_id,'b2_post_thumb',$thumb);
            }
        }

        return apply_filters('b2_get_post_thumb',$thumb,$post_id,$no_default);
    }

    public static function update_post_views($post_id){

        if(wp_using_ext_object_cache()){
            $cache = (int)wp_cache_get( $post_id, 'b2_post_views');

            if($cache){
                if($cache >= 100){
                    $views = (int)get_post_meta($post_id,'views',true);
                    update_post_meta($post_id,'views',$views + $cache + 1);
                    wp_cache_set($post_id,1,'b2_post_views');
                }else{
                    wp_cache_set($post_id,$cache+1,'b2_post_views');
                }
            }else{
                wp_cache_set($post_id,1,'b2_post_views');
            }
        }else{
            $views = (int)get_post_meta($post_id,'views',true);
            update_post_meta($post_id,'views',$views + 1);
        }
    }

    public static function get_post_views($post_id){
        if(wp_using_ext_object_cache()){
            $cache = (int)wp_cache_get( $post_id, 'b2_post_views');

            return (int)get_post_meta($post_id,'views',true) + $cache;
        }

        return (int)get_post_meta($post_id,'views',true);
    }

    //获取文章数据
    public static function get_post_data($post_id,$show_author = true){
        if(!$post_id) return array('error'=>__('参数不全','b2'));

        $current_user_id = b2_get_current_user_id();

        $favorites = self::get_post_favorites($current_user_id,$post_id);

       // self::update_post_views($post_id);

        $views = (int)get_post_meta($post_id,'views',true);

        update_post_meta($post_id,'views',$views+1);

        do_action('b2_post_views',[
            'view'=>$views+1,
            'post_id'=>$post_id,
            'user_id'=>$current_user_id
        ]);

        $isset_up = PostRelationships::isset(array('type'=>'post_up','user_id'=>$current_user_id,'post_id'=>$post_id));
        $isset_down = PostRelationships::isset(array('type'=>'post_down','user_id'=>$current_user_id,'post_id'=>$post_id));

        $count_vote = self::get_post_vote_up($post_id);

        //文章作者信息
        $post_author = get_post_field('post_author', $post_id);

        //当前用户的信息
        $current_user = array();
        if($current_user_id && $show_author){
            $current_user = User::get_user_public_data($current_user_id);
            $current_user['credit'] = get_user_meta($current_user_id,'zrz_credit_total',true);
            $current_user['money'] = get_user_meta($current_user_id,'zrz_rmb',true);
        }

        $author = [];
        if($show_author){
            $author = User::get_user_public_data($post_author,true);
        }

        $data = apply_filters( 'b2_get_post_data', array(
            'favorites_isset'=>$favorites['favorites_isset'],
            'favorites'=>$favorites['favorites'],
            'views'=>b2_number_format($views),
            'up'=>$count_vote['up'],
            'down'=>$count_vote['down'],
            'up_isset'=>$isset_up,
            'down_isset'=>$isset_down,
            'author'=>$author,
            'current_user'=>$current_user,
            'ds_data'=>self::get_post_ds_data($post_id)
        ), $post_id);

        return $data;
    }

    public static function get_post_favorites($user_id,$post_id){
        if(!$user_id){
            $user_id = b2_get_current_user_id();
        }
        $favorites = get_post_meta($post_id, 'zrz_favorites', true );
        $favorites = !empty($favorites) ? $favorites : array();

        return [
            'favorites_isset'=>array_search($user_id,$favorites) === false ? false : true,
            'favorites'=>b2_number_format(!empty($favorites) ? count($favorites) : 0)
        ];
    }

    //获取文章打赏数据
    public static function get_post_ds_data($post_id){
        $ds = get_option('b2_template_single');

        if(!isset($ds['single_ds_group'][0])){
            $ds = array(
                'single_post_ds_open'=>1,
                'single_post_ds_text'=>b2_get_option('template_single','single_post_ds_text'),
                'single_post_ds_none_text'=>b2_get_option('template_single','single_post_ds_none_text'),
                'single_post_ds_money'=>b2_get_option('template_single','single_post_ds_money'),
            );
        }else{
            $ds = $ds['single_ds_group'][0];
        }

        if(!$ds['single_post_ds_open']) return;

        $ds['moneys'] = array_slice(explode('|',$ds['single_post_ds_money']),0,5,true);

        //获取赞赏的数据
        $arg = get_post_meta($post_id,'zrz_shang',true);
        $arg = is_array($arg) ? $arg : array();

        $arg = array_reverse($arg);

        $count = count($arg);

        $ds['count'] = $count;

        $users = array();

        foreach ($arg as $k => $v) {
            if($k >= 20) break;
            $link = $v['user'] ? get_author_posts_url($v['user']) : '';
            $name = $v['user'] ? get_the_author_meta('display_name',$v['user']) : __('游客','b2');
            $users[] = array(
                'link'=>$link,
                'name'=>$name,
                'money'=>B2_MONEY_SYMBOL.number_format($v['rmb'],2),
                'avatar'=>get_avatar_url($v['user'], array('size'=>40))
            );
        }

        $ds['users'] = $users;

        return $ds;
    }

    //文章点赞与点踩
    public static function post_vote($type,$post_id){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return false;

        $success = array(
            'down'=>0,
            'up'=>0
        );

        $up = PostRelationships::isset(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id));
        $down = PostRelationships::isset(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id));

        $post_author = (int)get_post_field('post_author',$post_id);
        if(!$post_author) return array('error'=>__('参数错误','b2'));

        if($user_id === $post_author) return array('error'=>__('不能给自己投票','b2'));

        $credit = (int)b2_get_option('normal_gold','credit_post_up');

        $post_type = get_post_type($post_id);

        $post_type_text = b2_get_post_type_name($post_type);

        if($type === 'up'){
            if($up){
                $success['up'] = -1;
                PostRelationships::delete_data(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id));

                $task = Task::update_task($post_author,'task_post_vote');
                if($task){
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$post_author,
                        'gold_type'=>0,
                        'no'=>-$credit,
                        'post_id'=>$post_id,
                        'msg'=>sprintf(__('有${count}人不太认同您的%s：${post_id}','b2'),$post_type_text),
                        'type'=>'post_up_cancle',
                        'type_text'=>__('取消点赞','b2'),
                        'old_row'=>1
                    ]);
                }

                Message::update_data([
                    'date'=>current_time('mysql'),
                    'from'=>$user_id,
                    'to'=>$post_author,
                    'post_id'=>$post_id,
                    'msg'=>sprintf(__('${from}不太喜欢你的%s：${post_id}','b2'),$post_type_text),
                    'type'=>'post_up_cancle',
                    'type_text'=>__('取消点赞','b2'),
                    'old_row'=>1
                ]);
                
            }else{
                $success['up'] = 1;
                PostRelationships::update_data(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id));

                $task = Task::update_task($post_author,'task_post_vote');
                if($task){
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$post_author,
                        'gold_type'=>0,
                        'no'=>$credit,
                        'post_id'=>$post_id,
                        'msg'=>sprintf(__('有${count}人给您的%s点了赞：${post_id}','b2'), $post_type_text),
                        'type'=>'post_up',
                        'type_text'=>__('点赞','b2'),
                        'old_row'=>1
                    ]);

                }

                Message::update_data([
                    'date'=>current_time('mysql'),
                    'from'=>$user_id,
                    'to'=>$post_author,
                    'post_id'=>$post_id,
                    'msg'=>sprintf(__('${from}给你的%s点了赞：${post_id}','b2'),$post_type_text),
                    'type'=>'post_up',
                    'type_text'=>sprintf(__('%s被点赞','b2'),$post_type_text),
                    'old_row'=>1
                ]);
            }

            if($down){
                $success['down'] = -1;
                PostRelationships::delete_data(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id));
            }
        }

        if($type === 'down'){
            if($up){
                $success['up'] = -1;
                PostRelationships::delete_data(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id));
                
                $task = Task::update_task($post_author,'task_post_vote');
                if($task){
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$post_author,
                        'gold_type'=>0,
                        'no'=>-$credit,
                        'post_id'=>$post_id,
                        'msg'=>sprintf(__('有${count}人不太认同您的%s：${post_id}','b2'),$post_type_text),
                        'type'=>'post_up_cancle',
                        'type_text'=>__('取消点赞','b2'),
                        'old_row'=>1
                    ]);
                }

                Message::update_data([
                    'date'=>current_time('mysql'),
                    'from'=>$user_id,
                    'to'=>$post_author,
                    'post_id'=>$post_id,
                    'msg'=>sprintf(__('${from}不太喜欢你的%s：${post_id}','b2'),$post_type_text),
                    'type'=>'post_up_cancle',
                    'type_text'=>__('取消点赞','b2'),
                    'old_row'=>1
                ]);

            }

            if($down){
                $success['down'] = -1;
                PostRelationships::delete_data(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id));
            }else{
                $success['down'] = 1;
                PostRelationships::update_data(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id));
            }
        }

        if($post_type == 'infomation'){
            $user_vote_arg = get_user_meta($user_id,'b2_infomation_like',true);
            $user_vote_arg = is_array($user_vote_arg) ? $user_vote_arg : [];

            if($success['up'] == 1){
                if(!in_array($post_id,$user_vote_arg)){
                    $user_vote_arg[] = $post_id;
                }
            }else{
                if(in_array($post_id,$user_vote_arg)){
                    $key = array_search($post_id, $user_vote_arg);
                    if($key !== false){
                        unset($user_vote_arg[$key]);
                    }
                    
                }
            }

            update_user_meta($user_id,'b2_infomation_like',$user_vote_arg);
        }

        update_post_meta($post_id,'b2_vote_down_count',PostRelationships::get_count(array('type'=>'post_down','post_id'=>$post_id)));
        update_post_meta($post_id,'b2_vote_up_count',PostRelationships::get_count(array('type'=>'post_up','post_id'=>$post_id)));

        do_action('b2_post_vote',$type,$post_id,$user_id,$success);
        do_action('b2_rebuild_hotness',$post_id);

        return $success;
    }

    //获取文章资源下载数据
    public static function get_post_download_data($post_id,$guest = ''){

        $post_id = (int)$post_id;

        $user_id = b2_get_current_user_id();

        //检查是否开启了下载
        $can_download = get_post_meta($post_id,'b2_open_download',true);
        if(!$can_download) return array('error'=>__('下载未开启','b2'));

        //获取下载设置
        $download_settings = get_post_meta($post_id,'b2_single_post_download_group',true);
        if(!$download_settings || !is_array($download_settings)) return array('error'=>__('没有下载资源','b2'));

        $data = array();
        $i = 0;
        foreach($download_settings as $k => $v){

            $attr = array();
            if(!empty($v['attr'])){
                $attr = self::get_download_attrs($v['attr']);
            }

            $thumb = isset($v['thumb']) && !empty($v['thumb']) ? $v['thumb'] : self::get_post_thumb($post_id);

            if(!isset($v['rights']) || $v['rights'] == ''){
                $v['rights'] = 'all|free';
            }

            $rights = apply_filters('b2_get_download_rights', $v['rights'],$post_id);

            $lv = User::get_user_lv($user_id);

            $can = self::check_current_user_can_download($post_id,$i,$user_id,$rights,isset($guest[$k]['order_id']) ? $guest[$k]['order_id'] : '');

            $can = apply_filters('b2_check_current_user_can_download',$can,$post_id,$rights);

            $rights = apply_filters('b2_get_download_rights_last', $rights,$can,$lv);

            $_guest = false;

            $can_guset_pay = get_post_meta($post_id,'b2_down_guest_buy',true);

            foreach ($rights as $_k => $_v) {
                if($_v['lv'] === 'guest' || ($_v['lv'] === 'all' && $can_guset_pay)){
                    $_guest = true;
                }
            }

            $data[] = array(
                'thumb'=>b2_get_thumb(array(
                    'thumb'=>$thumb,
                    'width'=>200,
                    'height'=>200
                )),
                'name'=>isset($v['name']) && $v['name'] ? $v['name'] : get_the_title($post_id),
                'button'=>isset($v['url']) ? self::get_download_button($v['url'],$post_id,$i) : '',
                'attrs'=>$attr,
                'rights'=>$rights,
                'view'=>isset($v['view']) && $v['view'] ? esc_url($v['view']) : '',
                'current_user'=>array(
                    'can'=>$can,
                    'lv'=>$lv,
                    'guest'=>$_guest,
                    'current_guest'=>$user_id
                ),
                'show_role'=>false,
            );

            $i++;
        }

        return $data;
    }

    //获取单独的下载资源
    public static function get_download_page_data($post_id,$index,$i,$guest = ''){

        $user_id = b2_get_current_user_id();

        $download_settings = get_post_meta($post_id,'b2_single_post_download_group',true);
        if(!$download_settings || !isset($download_settings[$index])) return array('error'=>__('没有找到您要下载的资源','b2'));

        $download_settings = $download_settings[$index];

        $rights = apply_filters('b2_get_download_rights', $download_settings['rights'],$post_id);
        $can = self::check_current_user_can_download($post_id,$index,$user_id,$rights,isset($guest[$index]['order_id']) ? $guest[$index]['order_id'] : '');
        $can = apply_filters('b2_check_current_user_can_download',$can,$post_id,$rights);

        $button = self::get_download_button($download_settings['url'],$post_id,$index,$can['allow'],$i,$user_id);
        if(isset($button['error'])){
            return $button;
        }

        $_guest = false;

        $can_guset_pay = get_post_meta($post_id,'b2_down_guest_buy',true);

        foreach ($rights as $k => $v) {
            if($v['lv'] === 'guest' || ($v['lv'] === 'all' && $can_guset_pay)){
                $_guest = true;
                break;
            }
        }

        return array(
            'button'=>$button,
            'current_user'=>array(
                'can'=>$can,
                'lv'=>User::get_user_lv($user_id),
                'guest'=>$_guest,
                'current_guest'=>$user_id
            )
        );

    }

    public static function get_download_button($str,$post_id,$index,$url = false,$_i = 0,$user_id = 0){
        if(!$str) return array();

        $str = trim($str, " \t\n\r");
        $str = explode(PHP_EOL, $str );

        $arg = array();

        $i = 0;
        foreach ($str as $k => $v) {
            $v = trim($v, " \t\n\r");
            $_v = explode('|', $v);
            if(!isset($_v[0]) && !isset($_v[1])) continue;

            $attr = array(
                'tq'=>'',
                'jy'=>''
            );

            if(isset($_v[2]) && $url){
                $attr = self::get_download_tq_and_jy($_v[2]);
            }

            //加密下载地址
            $arg[] = array(
                'name'=>$_v[0],
                'link'=>b2_get_custom_page_url('download').'?post_id='.$post_id.'&index='.$index.'&i='.$i,
                'url'=>$url ? $_v[1] : '',
                'attr'=>$attr
            );

            $i++;
        }

        if($url){
            $arg = $arg[$_i];

            if(class_exists('Jwt_Auth_Public')){
                $issuedAt = time();
                $expire = $issuedAt + 300;//5分钟时效

                $sign = md5(AUTH_KEY.$user_id.$post_id.$index.$_i);

                $token = array(
                    "iss" => B2_HOME_URI,
                    "iat" => $issuedAt,
                    "nbf" => $issuedAt,
                    'exp'=>$expire,
                    'data'=>array(
                        // 'url'=>$arg['url'],
                        'sign'=>$sign,
                        'user_id'=>$user_id,
                        'post_id'=>$post_id,
                        'index'=>$index,
                        'i'=>$_i
                    )
                );

                $token = JWT::encode($token, AUTH_KEY);

                $arg['url'] = $token;
            }else{
                return array('error'=>__('请安装 JWT Authentication for WP-API 插件','b2'));
            }
        }

        return $arg;
    }
    
    public static function get_download_url($post_id,$index,$i){
        
        $download_settings = get_post_meta($post_id,'b2_single_post_download_group',true);
        if(!$download_settings || !isset($download_settings[$index]['url'])) return array('error'=>__('没有找到您要下载的资源','b2'));

        $download_settings = $download_settings[$index]['url'];
        
        $str = trim($download_settings, " \t\n\r");
        $str = explode(PHP_EOL, $str );

        $arg = array();

     
        foreach ($str as $k => $v) {
            $v = trim($v, " \t\n\r");
            $_v = explode('|', $v);
            if(!isset($_v[0]) && !isset($_v[1])) continue;

            $attr = array(
                'tq'=>'',
                'jy'=>''
            );

            if(isset($_v[2])){
                $attr = self::get_download_tq_and_jy($_v[2]);
            }
            
            if($k == $i){
                return array(
                    'name'=>$_v[0],
                    'link'=>b2_get_custom_page_url('download').'?post_id='.$post_id.'&index='.$index.'&i='.$i,
                    'url'=>$_v[1],
                    'attr'=>$attr
                );
            }
        }
        
        return ['error'=>__('没有这个资源','b2')];
        
    }

    //获取文件的真实地址
    public static function download_file($token){

        try{
            //检查验证码
            $decoded = JWT::decode($token, AUTH_KEY,array('HS256'));
            //return array('error'=>$decoded);
            

            if(!isset($decoded->data->sign) || !isset($decoded->data->user_id)){
                return array('error'=>__('参数错误','b2'));
            }

            $public_count = apply_filters('b2_check_repo_before', $decoded->data->user_id);
            if(isset($public_count['error'])) return $public_count;

            $sign = md5(AUTH_KEY.$decoded->data->user_id.$decoded->data->post_id.$decoded->data->index.$decoded->data->i);

            if($sign !== $decoded->data->sign) return array('error'=>__('参数错误','b2'));

            $down_count = self::check_user_can_download_all($decoded->data->user_id);

            if($down_count['count'] < 0 && $decoded->data->user_id) return ['error'=>__('下载次数使用完毕','b2')];
            
            $button = self::get_download_url($decoded->data->post_id,$decoded->data->index,$decoded->data->i);
            
            if(isset($button['error'])) return $button;

            $decoded->data->url = $button['url'];

            if(!in_array($decoded->data->post_id,$down_count['posts']) && (int)$down_count['total_count'] < 9999){
                $down_count['posts'][] = $decoded->data->post_id;
                $down_count['count'] = $down_count['count'] - 1;

                update_user_meta($decoded->data->user_id,'b2_download_count',$down_count);
            }

            $data = apply_filters('b2_download_file', $decoded->data);

            apply_filters('b2_check_repo_after', $decoded->data->user_id,$public_count);

            return $decoded->data->url;

        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return array('error'=>__('网页时效过期，请重新发起','b2'));
        }catch(\Exception $e) {  //其他错误
            return array('error'=>__('解码失败','b2'));
        }

    }

    //获取提取码和解压码
    public static function get_download_tq_and_jy($str){

        $attr = array(
            'tq'=>'',
            'jy'=>''
        );

        //检查字符串是否
        $a = explode(',',$str);

        foreach ($a as $k => $v) {
            $b = explode('=', $v);
            if(!isset($b[0]) && !isset($b[1])) continue;

            $attr[$b[0]] = trim($b[1], " \t\n\r");
        }

        return $attr;

    }

    //将下载数据中的属性字符串转换成数组
    public static function get_download_attrs($str){
        if(!$str) return array();

        $str = trim($str, " \t\n\r");
        $str = explode(PHP_EOL, $str );

        $arg = array();

        foreach ($str as $k => $v) {
            $v = trim($v, " \t\n\r");
            $_v = explode('|', $v);
            if(!isset($_v[0]) && !isset($_v[1])) continue;

            $arg[] = array(
                'name'=>$_v[0],
                'value'=>$_v[1]
            );
        }

        return $arg;
    }

    //判断用户是否允许下载所有资源
    public static function check_user_can_download_all($user_id){

        //获取用户当前的等级
        $user_role = User::get_user_lv($user_id);
        $roles = User::get_user_roles();

        $vip_settings = isset($roles[$user_role['vip']['lv']]) ? $roles[$user_role['vip']['lv']] : false;
        $lv_settings = isset($roles[$user_role['lv']['lv']]) ? $roles[$user_role['lv']['lv']] : false;

        $count_vip = isset($vip_settings['allow_download_count']) ? (int)$vip_settings['allow_download_count'] : 20;

        $count_lv = isset($lv_settings['allow_download_count']) ? (int)$lv_settings['allow_download_count'] : 20;

        $is_vip = isset($user_role['vip']['lv']) && isset($roles[$user_role['vip']['lv']]);

        $taday = current_time('Y-m-d');

        $count_total = $count_vip && $vip_settings ? $count_vip : $count_lv;

        if($user_id){

            
            //检查下载次数
            $user_count = get_user_meta($user_id,'b2_download_count',true);
            if((!isset($user_count['date']) || (isset($user_count['date']) && $taday > $user_count['date']))){

                $user_count = array(
                    'date'=>$taday,
                    'count'=>$count_total,
                    'posts'=>array()
                );

                update_user_meta($user_id,'b2_download_count',$user_count);

                return array(
                    'allow'=>isset($vip_settings['allow_download']) && $vip_settings['allow_download'] == 1 && $is_vip ? true : false,
                    'count'=>$count_total,
                    'total_count'=>$count_total,
                    'date'=>$user_count['date'],
                    'posts'=>array()
                );
            }

            if((int)$user_count['count'] <= 0){

                return array(
                    'allow'=>false,
                    'count'=>0,
                    'total_count'=>$count_total,
                    'date'=>$user_count['date'],
                    'posts'=>array()
                );
            }else{
                return array(
                    'allow'=>isset($vip_settings['allow_download']) && $vip_settings['allow_download'] == 1 && $is_vip ? true : false,
                    'count'=>$user_count['count'],
                    'total_count'=>$count_total,
                    'date'=>$user_count['date'],
                    'posts'=>isset($user_count['posts']) ? $user_count['posts'] : array()
                );
            }
        }

        return array(
            'allow'=>false,
            'count'=>0,
            'total_count'=>0,
            'date'=>'',
            'posts'=>array()
        );
    }

    //检查当前用户是否有权限下载
    public static function check_current_user_can_download($post_id,$index,$user_id,$rights,$order_id = ''){

        //获取用户的当前权限
        $user_role = User::get_user_lv($user_id);

        $allow = self::check_user_can_download_all($user_id);

        $has_lv = false;
        $has_vip = false;

        foreach ($rights as $k => $v) {
            if($v['lv'] === 'lv'){
                $has_lv = true;
            }

            if($v['lv'] === 'vip'){
                $has_vip = true;
            }
        }unset($v);

        if(!$user_id){
            foreach ($rights as $k => $v) {
                if($v['lv'] == 'guest'){
                    return self::check_download_allow($user_id,$post_id,$index,$v,$order_id);
                }
            }unset($v);
        }

        //如果是会员，检查会员是否有权限下载
        foreach ($rights as $k => $v) {

            if($v['lv'] === 'lv' || $v['lv'] === 'vip' || $v['lv'] === 'all'){
                continue;
            }

            if((isset($user_role['vip']['lv']) && $user_role['vip']['lv'] === $v['lv']) || (isset($user_role['lv']['lv']) && $user_role['lv']['lv'] === $v['lv'])){
                return self::check_download_allow($user_id,$post_id,$index,$v,$order_id);
            }

        }unset($v);

        foreach ($rights as $k => $v) {
            if($v['lv'] === 'lv'){
                $allow = self::check_download_allow($user_id,$post_id,$index,$v,$order_id);
                if($allow['allow']) return $allow;
            }
        }unset($v);

        foreach ($rights as $k => $v) {
            if($v['lv'] === 'vip' && (isset($user_role['vip']['lv']) && strpos($user_role['vip']['lv'],'vip') !== false)){
                $allow = self::check_download_allow($user_id,$post_id,$index,$v,$order_id);
                if($allow['allow']) return $allow;
            }
        }unset($v);

        foreach ($rights as $k => $v) {
            if($v['lv'] === 'all' && !$has_lv && !$has_vip){
                $allow = self::check_download_allow($user_id,$post_id,$index,$v,$order_id);
                if($allow['allow']) return $allow;
            }
        }

        return $allow;
    }

    public static function check_download_allow($user_id,$post_id,$index,$v,$order_id){
        $res = apply_filters('b2_download_allow',array(
            'user_id'=>$user_id,
            'post_id'=>$post_id,
            'order_key'=>$index,
            'order_type'=>'x',
            'type'=>$v,
            'order_id'=>$order_id
        ));

        if(!$res){

            //检查付费会员是否还有权限继续下载
            $allow_download = self::check_user_can_download_all($user_id);

            $allow = array(
                'allow'=>false,
                'type'=>$allow_download['count'] <= 0 && $user_id ? 'full' : $v['type'],
                'value'=>$v['value'],
                'count'=>$allow_download['count'],
                'total_count'=>$allow_download['total_count'],
                'free_down'=>(int)$allow_download['total_count'] >= 9999 ? true : false
            );

            if($allow_download['allow'] === true){
                $allow = array(
                    'allow'=>$allow_download['count'] <= 0 && $user_id ? false : true,
                    'type'=>$allow_download['count'] <= 0 && $user_id ? 'full' : $v['type'],
                    'count'=>$allow_download['count'],
                    'total_count'=>$allow_download['total_count'],
                    'free_down'=>(int)$allow_download['total_count'] >= 9999 ? true : false
                );
            }

        }else{

            $allow_download = self::check_user_can_download_all($user_id);

            $allow = array(
                'allow'=>$allow_download['count'] <= 0 && $user_id ? false : true,
                'type'=>$allow_download['count'] <= 0 && $user_id ? 'full' : $v['type'],
                'count'=>$allow_download['count'],
                'total_count'=>$allow_download['total_count'],
                'value'=>$v['value'],
                'free_down'=>(int)$allow_download['total_count'] >= 9999 ? true : false
            );
        }

        return $allow;
    }

    //预览文章
    public static function preview_post($content){
        return apply_filters( 'the_content', wptexturize( $content ));
    }

    //发布文章
    public static function insert_post($data){

        $user_id = b2_get_current_user_id();

        //检查积分
        // $credit = get_user_meta($user_id,'zrz_credit_total',true);
        // if($credit <= 0) return array('error'=>__('积分不足','b2'));

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        wp_set_current_user($user_id);

        //检查3小时内发布总数
        $post_count_3 = User::check_post($user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        if(!b2_get_option('normal_write','write_allow')) return array('error'=>__('禁止投稿','b2'));

        //检查是否有权限
        $role = User::check_user_role($user_id,'post');

        if(!$role && !user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )) return array('error'=>__('您没有权限发布文章','b2'));

        // 新增钩子
        // edited by fuzqing
        do_action('b2_user_write_post',$user_id);

        $post_count = b2_get_option('normal_write','write_can_post');

        $data['title'] = isset($data['title']) ? b2_remove_kh($data['title']) : '';
        $data['content'] = isset($data['content']) ? str_replace(array('{{','}}'),'',$data['content']) : '';
        $data['excerpt'] = isset($data['excerpt']) ? b2_remove_kh($data['excerpt']) : '';

        if(!$data['title']){
            return array('error'=>__('标题不可为空','b2'));
        }

        //检查文章内容
        if(!$data['content']){
            return array('error'=>__('内容不可为空','b2'));
        }

        $censor = apply_filters('b2_text_censor', $data['title'].$data['content'].$data['excerpt']);
        if(isset($censor['error'])) return $censor;

        if(!user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )){
            //检查是否有草稿
            $args=array(
                'post_type' => 'post',
                'post_status' => 'pending',
                'posts_per_page' => $post_count ? $post_count+1 : 3,
                'author' => $user_id
            );

            $posts = get_posts($args);
            if(count($posts) >= $post_count){
                return array('error'=>__('您还有未审核的文章，请审核完后再提交','b2'));
            }
        }

        $data['post_id'] = isset($data['post_id']) ? (int)$data['post_id'] : null;

        //检查文章作者
        if($data['post_id']){
            if((get_post_field( 'post_author', $data['post_id'] ) != $user_id || get_post_type($data['post_id']) != 'post') && !user_can($user_id, 'administrator' ) && !user_can( $user_id, 'editor' )){
                return array('error'=>__('非法操作','b2'));
            }
        }

        $post_id = false;

        if($data['type'] !== 'draft'){
            if((user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'editor' ))){
                $data['type'] = 'publish';

            }else{
                $data['type'] = 'pending';
            }

            $can_publish = User::check_user_media_role($user_id,'post');
            if($can_publish){
                $data['type'] = 'publish';
            }
        }else{
            $data['type'] = 'draft';
        }

        if($data['post_id']){
            $post_author = get_post_field( 'post_author', $data['post_id'] );
        }

        //提交
        $arg = array(
            'ID'=> $data['post_id'] ? $data['post_id'] : null,
            'post_title' => $data['title'],
            'post_content' => wp_slash($data['content']),
            'post_status' => $data['type'],
            'post_author' => $post_author,
            'post_category' => $data['cats'],
            'post_excerpt'=>$data['excerpt'],
        );

        if($data['post_id']){
            $post_id = wp_update_post($arg);
        }else{
            $post_id = wp_insert_post( $arg );
        }

        if($post_id){
            apply_filters('b2_check_repo_after', $user_id,$public_count);
            User::save_check_post_count($user_id);

            //设置专题
            if(!empty($data['collections'])){
                wp_set_object_terms($post_id, array_map('intval',$data['collections']), 'collection');
            }

            if(!empty($data['tags'])){
                //设置标签
                $tags = array();
                foreach ($data['tags'] as $key => $value) {
                    $tags[] = b2_remove_kh($value,true);
                }
                wp_set_post_tags($post_id, $tags, false);
            }

            //设置特色图
            $thumb_id = self::get_attached_id_by_url($data['thumb']);
            if($thumb_id){
                set_post_thumbnail($post_id,$thumb_id);
            }

            //文章来源
            if(isset($data['from']['url']) && $data['from']['url']){

                update_post_meta($post_id,'b2_post_from_url',esc_url(b2_remove_kh($data['from']['url']),true));

            }

            if(isset($data['from']['name']) && $data['from']['name']){
                update_post_meta($post_id,'b2_post_from_name',b2_remove_kh($data['from']['name'],true));
            }

            //权限设置
            if(isset($data['role'])){
                update_post_meta($post_id,'b2_post_reading_role',b2_remove_kh($data['role']['key'],true));
                if(isset($data['role']['money']) && $data['role']['money']){
                    $data['role']['money'] = (float)$data['role']['money'];
                    if($data['role']['money'] <= 0) return array('error'=>__('金额错误','b2'));

                    update_post_meta($post_id,'b2_post_money',b2_remove_kh($data['role']['money'],true));
                }
                if(isset($data['role']['credit']) && $data['role']['credit']){
                    $data['role']['credit'] = (int)$data['role']['credit'];
                    if($data['role']['credit'] <= 0) return array('error'=>__('金额错误','b2'));
                    update_post_meta($post_id,'b2_post_credit',$data['role']['credit']);
                }
                if(isset($data['role']['roles']) && !empty($data['role']['roles'])){
                    $i = 0;
                    foreach($data['role']['roles'] as $k=>$v){
                        $data['role']['roles'][$i] = b2_remove_kh($v,true);
                        $i++;
                    }
                    update_post_meta($post_id,'b2_post_roles',$data['role']['roles']);
                }
            }

            //设置自定义字段
            if(!empty($data['custom'])){
                $custom_arr = array();
                foreach($data['custom'] as $k => $v){
                    $v = (string)$v;
                    $k = b2_remove_kh($k,true);

                        if(is_array($v)){
                            $i = 0;
                            foreach ($v as $_k => $_v) {
                                $v[$i] = b2_remove_kh($v,true);
                                $i++;
                            }
                        }else{
                            $v = b2_remove_kh($v,true);
                        }
                        $custom_arr[] = (string)$k;
                        update_post_meta($post_id,$k,$v);

                }

                update_post_meta($post_id,'b2_custom_key',$custom_arr);
            }

            //图片挂载到当前文章
            $regex = '/src="([^"]*)"/';
            preg_match_all( $regex, $data['content'], $matches );
            $matches = array_reverse($matches);

            if(!empty($matches[0])){
                foreach($matches[0] as $k => $v){
                    $thumb_id = self::get_attached_id_by_url($v);
                    if($thumb_id){
                        //检查是否挂载过
                        if(!wp_get_post_parent_id($thumb_id) || (int)wp_get_post_parent_id($thumb_id) === 1){
                            wp_update_post(
                                array(
                                    'ID' => $thumb_id,
                                    'post_parent' => $post_id
                                )
                            );
                        }
                    }
                }
            }

            // 新增钩子
            // edited by fuzqing
            do_action('b2_user_write_post_success',$user_id,$post_id);

            $status = get_post_status($post_id);
            if($status == 'pending'){
                return get_post_permalink($post_id).'&post_type=post&viewtoken='.md5(AUTH_KEY.$user_id);
            }elseif($status == 'draft'){
                return get_author_posts_url($user_id).'/post';
            }else{
                return get_permalink($post_id);
            }

        }

        return array('error'=>__('发布失败','b2'));
    }

    //删除草稿
    public static function delete_draft_post($post_id){
        $user_id = (int)b2_get_current_user_id();

        if(self::user_can_edit($post_id,$user_id)){
            wp_trash_post($post_id);

            return true;
        }

        return false;

    }

    public static function get_attached_id_by_url($url){

        return attachment_url_to_postid($url);

        $path = parse_url($url);

        if($path['path']){
            global $wpdb;

            $sql = $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND guid LIKE %s",
                'attachment',
                '%'.$path['path'].'%'
            );

            $post_id = $wpdb->get_var( $sql );

            return (int) apply_filters( 'b2_attachment_url_to_postid', $post_id, $url );
        }

        return array('error'=>__('删除失败','b2'));
    }

    //检查用户是否有文章编辑权限
    public static function check_write_user($post_id){
        $user_id = b2_get_current_user_id();

        $time = self::user_can_edit($post_id,$user_id);

        if(!$time) return array('没有权限编辑此文','b2');

        $issuedAt = time();
        $expire = $issuedAt + 3600;//6小时时效

        $post_author = get_post_field('post_author', $post_id);

        $token = array(
            "iss" => B2_HOME_URI,
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            'exp'=>$expire,
            'data'=>array(
                'post_id'=>$post_id,
                'author'=>$post_author
            )
        );

        $jwt = JWT::encode($token, AUTH_KEY);

        return b2_get_custom_page_url('write').'?token='.$jwt;
    }

    public static function get_categorys($post_id,$term_type){

        $post_id = (int)$post_id;

        // $term_ids = wp_get_object_terms();
        $categories = wp_get_object_terms($post_id, $term_type,array('fields' => 'ids'));

        $data = array();

        if ( ! empty( $categories )) {

            foreach ($categories as $cat) {
                $term = get_term($cat,$term_type);
                $color = get_term_meta($cat,'b2_tax_color',true);
                $data[] = array(
                    'id'=>$term->term_id,
                    'name'=>$term->name,
                    'link'=>esc_url( get_term_link( $term->term_id ) ),
                    'color'=>$color
                );
            }

        }

        return $data;
    }

    //文章上一篇，下一篇
    public static function get_pre_next_post($post_id,$term_type){
        $previous_post = get_previous_post($post_id);
        $next_post = get_next_post($post_id);
        if(is_singular('shop')){
            $previous_post = get_previous_post(false,'','shoptype');
            $next_post = get_next_post(false,'','shoptype');
        }

        $html = '';
        $next_id = isset($next_post->ID) ? $next_post->ID : false;
        $pre_id = isset($previous_post->ID) ? $previous_post->ID : false;

        $args = array( 'number' => 1, 'orderby' => 'rand', 'post_status' => 'publish' );

        //如果没有上一篇或者下一篇，则显示随机文章
        if(!$pre_id){
            $rand_posts = get_posts( $args );
            $previous_post = $rand_posts[0];
            $pre_id = $previous_post->ID;
         
        }

        if(!$next_id){
            $rand_posts = get_posts( $args );
            $next_post = $rand_posts[0];
            $next_id = $next_post->ID;
           
        }

        $next_thumb = self::get_post_thumb($next_id);
        $pre_thumb = self::get_post_thumb($pre_id);

        $data = array(
            'next'=>array(
                'id'=>$next_id,
                'title'=>esc_attr($next_post->post_title),
                'link'=>get_permalink($next_id),
                'date'=>get_the_date('Y-n-j G:i:s',$next_id),
                'thumb'=>b2_get_thumb(array('thumb'=>$next_thumb,'width'=>370,'height'=>127)),
                'category'=>self::get_categorys($next_id,$term_type)
            ),
            'pre'=>array(
                'id'=>$pre_id,
                'title'=>esc_attr($previous_post->post_title),
                'link'=>get_permalink($pre_id),
                'date'=>get_the_date('Y-n-j G:i:s',$pre_id),
                'thumb'=>b2_get_thumb(array('thumb'=>$pre_thumb,'width'=>370,'height'=>127)),
                'category'=>self::get_categorys($pre_id,$term_type)
            )
        );

        return $data;
    }

    public static function get_related_posts($post_id){

        //获取当前的文章类型
        $post_type = get_post_type($post_id);

        //通过插件自动获取相关文章
        $yarpp_posts = defined('YARPP_VERSION') ? yarpp_get_related(array('limit' => 4,'post_type'=>$post_type),$post_id) : array();

        $data = array();
        if(!empty($yarpp_posts)){
            foreach ($yarpp_posts as $k => $v) {
                $view = (int)get_post_meta($v->ID,'views',true);

                $data[] = array(
                    'id'=>$v->ID,
                    'date'=>self::time_ago($v->post_date),
                    'title'=>$v->post_title,
                    'link'=>get_permalink($v->ID),
                    'thumb'=>b2_get_thumb(array('thumb'=>self::get_post_thumb($v->ID),'width'=>240,'height'=>150)),
                    'comment_count'=>b2_number_format(get_comments_number($v->ID)),
                    'views'=>b2_number_format($view)
                );
            }
        }

        return $data;
    }

    public static function get_post_tags($number){
        $tags = get_tags(array('orderby' => 'count','order'=>'desc','hide_empty' => false, 'number'=>$number,'public'=> true));

        $tags_list = array();
        if($tags){
            foreach ($tags as $k => $v) {
                $img = get_term_meta($v->term_id,'b2_tax_img',true);
                if($img){
                    $img = b2_get_thumb(array('thumb'=>$img,'width'=>120,'height'=>80));
                }
                $tags_list[] = array(
                    'img'=>$img,
                    'name'=>esc_attr($v->name),
                    'link'=>esc_url(get_tag_link( $v->term_id )),
                    'count'=>$v->count
                );
            }
        }

        return $tags_list;
    }

    public static function post_breadcrumb($post_id = 0){
        $tax = '';

        $tax = get_the_terms($post_id, 'category');
        $tax_links = '';
        $post_link = '';

        if($tax && $post_id){
            $tax = get_term($tax[0]->term_id, 'category' );

            $term_id = $tax->term_id;

         
        }else{
            $term = get_queried_object();
            $term_id = isset($term->term_id) ? $term->term_id : 0;

       
        }

        if($term_id){
            $tax_links = get_term_parents_list($term_id,'category');
            $tax_links = str_replace('>/<','><span>></span><',$tax_links);
            $tax_links = rtrim($tax_links,'/');
        }else{
            if(isset($_GET['s'])){
                $tax_links = __('搜索','b2');
            }
        }

        if($post_id){
            $post_link = '<span>></span>'.get_the_title($post_id);
        }

        return __('当前位置：','b2').'<a href="'.B2_HOME_URI.'">'.__('首页','b2').'</a><span>></span>'.$tax_links.$post_link;
    }

    public static function user_can_edit($post_id,$user_id){

        $admin = user_can( $user_id, 'manage_options' );
        $editor = user_can( $user_id, 'editor' );

        if($admin || $editor) {
            return 'long';
        }

        $time = \B2\Modules\Common\Circle::user_can_delete_post($post_id,$user_id);

        if(isset($time['error'])) return false;

        $author = get_post_field( 'post_author', $post_id);

        if($author != $user_id) return false;

        if($time === 'pending' || $time === 'draft') return 'long';

        return $time;

    }

    public static function get_post_gg($post_id){

        return array(
            'title'=>get_post_meta($post_id,'b2_post_gg_title',true),
            'content'=>get_post_meta($post_id,'b2_post_gg',true)
        );
    }

    public static function get_post_pay_data($post_id){
        $post_style = get_post_meta($post_id,'b2_single_post_style',true);

        $type = array();

        if($post_style === 'post-style-5'){
            $videos = get_post_meta($post_id,'b2_single_post_video_group',true);

            if(!empty($videos)){
                $type[] = array(
                    'type'=>'video',
                    'count'=>count($videos)
                );
            }
        }

        $down = get_post_meta($post_id,'b2_open_download',true);
        if($down){
            $down_data = get_post_meta($post_id,'b2_single_post_download_group',true);

            if(!empty($down_data)){
                $type[] = array(
                    'type'=>'download',
                    'count'=>count($down_data)
                );
            }
        }

        $hide = get_post_meta($post_id,'b2_post_reading_role',true);
        if($hide && $hide !== 'none'){
            $type[] = array(
                'type'=>'hide',
                'pay_type'=>$hide
            );
        }

        return $type;
    }

    public static function get_write_countent($post_id){

        $user_id = b2_get_current_user_id();

        $author = get_post_field('post_author', $post_id);

        if(user_can($user_id, 'administrator' ) || user_can( $user_id, 'editor' ) || $user_id == $author){
            $content = preg_replace( '/<!-- \/?wp:(.*?) -->/', '', get_post_field('post_content', $post_id) );
            $content = wpautop($content);
            return $content;
        }

        return '';
    }
}
