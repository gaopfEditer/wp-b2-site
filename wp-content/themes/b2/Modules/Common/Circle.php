<?php 
namespace B2\Modules\Common;

use B2\Modules\Common\Post;
use B2\Modules\Common\User;
use B2\Modules\Common\Shortcode;
use B2\Modules\Common\PostRelationships;
use B2\Modules\Common\CircleRelate;

class Circle{

    //通过ID获取圈子卡片
    public static function insert_topic_card($id){

        $circle_link = b2_get_option('normal_custom','custom_circle_link');

        if(!$id) return;

        if(!is_numeric($id)){
            $url = $id;
            $id = url_to_postid($id);
        }else{
            $url = get_the_permalink($id);
        }

        $post_type = get_post_type($id);

        if(!$post_type && strpos($url,'/'.$circle_link) === false) return array('error'=>__('不支持此类连接','b2'));

        if($post_type && ($post_type == 'post' || $post_type == 'shop' || $post_type == 'document' || $post_type == 'newsflashes' || $post_type == 'announcement' || $post_type == 'page')){
            $thumb_url = Post::get_post_thumb($id);

            $thumb_url = b2_get_thumb(array(
                'thumb'=>$thumb_url,
                'width'=>100,
                'height'=>100
            ));

            $title = get_the_title($id);
        }else{
            if($post_type === 'circle'){
                $images = get_post_meta($id,'b2_circle_image',true);
                if(!empty($images)){
                    $thumb_url = wp_get_attachment_url($images[0]);
                }else{
                    $thumb_url = '';
                }
                $thumb_url = b2_get_thumb(array(
                    'thumb'=>$thumb_url,
                    'width'=>100,
                    'height'=>100
                ));
                
                $title = get_the_title($id);
                if(!$title){
                    $title = get_post_meta($id,'b2_auto_title',true);
                }
            }else{
                if($url === B2_HOME_URI.'/'.$circle_link || $url === B2_HOME_URI.'/'.$circle_link.'/'){
                    $circle_id = get_option('b2_circle_default');
                }else{
                    $post_type = 'circle_tags';
                    if($id && is_numeric($id)){
                        $circle_id = $id;
                    }else{
                        $slug = str_replace(B2_HOME_URI.'/'.$circle_link.'/','',$url);

                        $circle_id = get_term_by('slug', $slug, 'circle_tags');
                        if(isset($circle_id->term_id)){
                            $circle_id = $circle_id->term_id;
                        }else{
                            return array('error'=>__('不支持此类连接','b2'));
                        }
                    }
                }

                $circle_data = self::get_circle_data($circle_id);

                $circle_name = b2_get_option('normal_custom','custom_circle_name');

                return array(
                    'id'=>$circle_id,
                    'type'=>'circle_tags',
                    'type_name'=>$circle_name,
                    'thumb'=>$circle_data['icon'],
                    'thumb_webp'=>$circle_data['icon_webp'],
                    'title'=>$circle_data['name'],
                    'link'=>$circle_data['link']
                );
            }
        }
        
        $post_meta = Post::post_meta($id);

        $post_name = apply_filters('b2_comment_post_type',$id);

        return array(
            'id'=>$id,
            'type'=>$post_type,
            'type_name'=>$post_name,
            'thumb'=>$thumb_url,
            'thumb_webp'=>apply_filters('b2_thumb_webp',$thumb_url),
            'title'=>$title,
            'link'=>get_permalink($id)
        );

    }

    public static function get_circle_top_cats(){

        $circle_square = b2_get_option('circle_main','circle_square');
        $circle_square = $circle_square == '' ? 1 : (int)$circle_square;

        $data = [
            'circle_square'=>$circle_square,
            'data'=>[]
        ];

        if($circle_square == 0){
            $cats = b2_get_option('circle_main','circle_home_cats');

            if(!empty($cats)){
                foreach ($cats as $k => $v) {
                    $data['data'][] = self::get_circle_data($v,0);
                }
            }
        }

        return $data;
    }

    //检查发表话题权限
    public static function check_topic_role($user_id,$circle_id = 0,$circles = true){

        $g_name = 'b2_check_topic_role_'.$user_id.'_'.$circle_id.'_'.($circles ? 'show' : 'hidden');

        if(isset($GLOBALS[$g_name])) return $GLOBALS[$g_name];

        $filter_data = apply_filters('b2_get_topic_filter_data', $user_id,$circle_id);

        $is_admin = $filter_data['is_admin'];
        $is_circle_admin = $filter_data['is_circle_admin'];

        $type_role = $filter_data['type_role'];
        $media_role = $filter_data['media_role'];
        $topic_read_role = $filter_data['topic_read_role'];

        $in_circle = apply_filters('b2_is_user_in_circle',$user_id,$circle_id) || $is_admin;

        $circle_role_data = array(
            'type'=>'free',
            'data'=>array(),
            'status'=>'none',
            'read'=>get_term_meta($circle_id,'b2_circle_read',true)
        );

        if(!$in_circle){
            $circle_role_data = self::circle_role_data($user_id,$circle_id);
        }

        $can_post = true;
        $pendings = self::get_user_pending_topic($user_id);
        $allow_pendings = (int)b2_get_option('circle_topic','topic_pending_count',$circle_id);

        if(count($pendings) >= $allow_pendings && !$is_admin && !$is_circle_admin){
            $can_post = false;
        }

        $data = array(
            'dark_room'=>(int)get_user_meta($user_id,'b2_dark_room',true),
            'topic_type_role'=>$type_role,
            'media_role'=>$media_role,
            'read_role'=>$topic_read_role,
            'circles'=>$circles ? self::get_user_circles($circle_id) : array(),
            'allow_create_circle'=>User::check_user_role($user_id,'circle_create') || $is_admin,
            'allow_create_topic'=>User::check_user_role($user_id,'circle_topic') || $is_admin || $is_circle_admin,
            'is_admin'=>$is_admin,
            'credit'=>get_user_meta($user_id,'zrz_credit_total',true),
            'money'=>get_user_meta($user_id,'zrz_rmb',true),
            'is_circle_admin'=>$is_circle_admin,
            'in_circle'=>$in_circle,
            'current_circle_role'=>$circle_role_data,
            'can_post'=>$can_post,
            'media_count'=>apply_filters('b2_get_circle_file_role', $circle_id),
            'allow_pendings'=>$allow_pendings
        );

        $GLOBALS[$g_name] = $data;

        return $data;
    }

    //加入圈子
    public static function join_circle($circle_id){
        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));
       
        $circle_id = (int)$circle_id;

        //检查入圈权限
        if(apply_filters('b2_is_user_in_circle',$user_id,$circle_id)){
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            return array('error'=>sprintf(__('您已经加入了此%s','b2'),$circle_name));
        } 

        $role = self::circle_role_data($user_id,$circle_id);

        if($role['type'] === 'free'){

            if($role['data'] === 'free'){

                if(CircleRelate::update_data(array(
                    'user_id'=>$user_id,
                    'circle_id'=>$circle_id,
                    'circle_role'=>'member',
                    'join_date'=>current_time('mysql')
                ))){
                    return 'success';
                }

            }elseif($role['data'] === 'check'){

                $pending = CircleRelate::isset(array('user_id'=>$user_id,'circle_id'=>$circle_id));

                if(!$pending && CircleRelate::update_data(array(
                    'user_id'=>$user_id,
                    'circle_id'=>$circle_id,
                    'circle_role'=>'pending',
                    'join_date'=>current_time('mysql')
                ))){
                    return 'pending';
                }else{
                    return array('error'=>__('您已经提交了申请','b2'));
                }

            }
        }elseif($role['type'] === 'lv'){
            if(!$role['data']['allow_join']) return array('error'=>__('您还未获得入圈资格','b2'));

            if(CircleRelate::update_data(array(
                'user_id'=>$user_id,
                'circle_id'=>$circle_id,
                'circle_role'=>'member',
                'join_date'=>current_time('mysql')
            ))){
                return 'success';
            }

            return 'success';
        }
        
        return array('error'=>__('入群失败','b2'));
    }

    public static function circle_role_data($user_id,$circle_id){

        $circle_id = (int)$circle_id;

        $data = array(
            // 'public'=>get_term_meta($circle_id,'b2_circle_read',true),
            'type'=>'free',
            'data'=>array(),
            'status'=>'none'
        );

        $type = get_term_meta($circle_id, 'b2_circle_type', true);

        if($type === 'lv'){
            $roles = (array)get_term_meta($circle_id, 'b2_circle_join_lv', true);
            $arr = array();
            $_arr = array();

            $lvs = self::get_current_user_lvs($user_id);

            $allow = false;

            if(!empty(array_intersect($roles, $lvs))){
                $allow = true;
            }

            foreach ($roles as $k => $v) {
                $arr[] = User::get_lv_icon($v);
                $_arr[] = User::get_lv_icon($v,true);
            }

            $data['type'] = 'lv';
            $data['data'] = array(
                'list'=>$arr,
                '_list'=>$_arr,
                'allow_join'=> $allow
            );

        }elseif($type === 'money'){
            $data['type'] = 'money';

            $arg = array('permanent'=>__('永久有效','b2'),'year'=>__('按年付费','b2'),'halfYear'=>__('半年付费','b2'),'season'=>__('按季付费','b2'),'month'=>__('按月付费','b2'));
            foreach ($arg as $k=>$v) {
                $money = get_term_meta($circle_id, 'b2_circle_money_'.$k, true);

                if($money){
                    $data['data'][] = array(
                        'type'=>$k,
                        'name'=>$v,
                        'money'=>$money
                    );
                }
                
            }
        }elseif($type === 'free'){
            $data['data'] = get_term_meta($circle_id, 'b2_circle_join_type', true);
            if($data['data'] === 'check'){
                $data['status'] = self::is_circle_pending($user_id,$circle_id) ? 'pending' : 'none';
            }
        }

        $data['read'] = get_term_meta($circle_id,'b2_circle_read',true);

        return $data;

    }

    // public static function insert_circle_user_related($data){

    //     $r = apply_filters('b2_circle_data_insert',wp_parse_args($data,array(
    //         'user_id'=>0,
    //         'circle_id'=>0,
    //         'circle_role'=>'',
    //         'join_date'=>current_time('mysql'),
    //         'end_date'=>current_time('mysql'),
    //         'circle_key'=>'',
    //         'circle_value'=>''
    //     )));

    //     global $wpdb;

    //     $table_name = $wpdb->prefix . 'b2_circle_related';

    //     return $wpdb->insert(
    //         $table_name, 
    //         $r,
    //         array( 
    //             '%d',//user_id
    //             '%d',//circle_id
    //             '%s',//circle_role
    //             '%s',//join_date
    //             '%s',//end_date
    //             '%s',//circle_key
    //             '%s',//circle_value
    //         )
    //     );
    // }

    //获取用户的圈子数据
    public static function get_user_circles($circle_id = 0){

        $user_id = b2_get_current_user_id();

        $res = CircleRelate::get_data(array(
            'user_id'=>$user_id,
            'count'=>20
        ));

        $circles = array();

        $circle_square = b2_get_option('circle_main','circle_square');
        $circle_square = $circle_square == '' ? 1 : (int)$circle_square;

        if($circle_square == 1){
            $default = self::get_circle_data(get_option('b2_circle_default'),$circle_id);
            $circles[(int)$default['id']] = $default;
        }
        

        if(!empty($res)){
            foreach ($res as $k => $v) {
                if($v['circle_id'] && get_term_by('id', $v['circle_id'], 'circle_tags')){
                    $circles[$v['circle_id']] = self::get_circle_data($v['circle_id'],$circle_id);
                }
            }
        }

        if((int)$circle_id !== 0 && get_term_by('id', $circle_id, 'circle_tags')){
            $circles[$circle_id] = self::get_circle_data($circle_id,$circle_id);
        }

        return $circles;
    }

    //获取某个圈子数据
    public static function get_circle_data($circle_id,$current_circle_id = 0){

        $user_id = b2_get_current_user_id();

        // $cache = wp_cache_get($circle_id.'_'.$user_id, 'b2_get_circle_data');

        $current = false;

        $default = (int)get_option('b2_circle_default');

        $c = get_term_by('id',$circle_id , 'circle_tags');
        if(!isset($c->count)){
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            return array('error'=>sprintf(__('不存在该%s','b2'),$circle_name));
        }

        if((int)$current_circle_id === 0){
            if($c->term_id == $default){
                $current = true;
            }
        }elseif((int)$circle_id === (int)$current_circle_id){

            $current = true;
        }

        // if($cache){
        //     $cache['default'] = $current;
        //     return $cache;
        // }

        // $size = apply_filters('b2_get_circle_size',456);


        $circle_count = wp_count_terms('circle_tags');
        $icon = b2_get_thumb(array('thumb'=>get_term_meta($c->term_id, 'b2_circle_icon', true),'width'=>100,'height'=>100));

        $file_role = apply_filters('b2_get_circle_file_role', $c->term_id);

        $circle_data = array(
            'name'=>esc_attr($c->name),
            'desc'=>esc_attr($c->description),
            'icon'=>$icon,
            'icon_webp'=>apply_filters('b2_thumb_webp',$icon),
            //'cover'=>b2_get_thumb(array('thumb'=>get_term_meta($c->term_id, 'b2_circle_cover', true),'width'=>$size['w'],'height'=>$size['h'])),
            'id'=>$c->term_id,
            'default'=>$current,
            'in_circle'=>apply_filters('b2_is_user_in_circle',$user_id,$c->term_id),
            'is_circle_admin'=>self::is_circle_admin($user_id,$c->term_id),
            'admin'=>self::get_circle_admin_data($c->term_id),
            'circle_count'=>b2_number_format($circle_count),
            'user_count'=>b2_number_format($default == $c->term_id ? User::user_count() : self::user_count_in_circle($c->term_id)),
            'topic_count'=>b2_number_format($default == $c->term_id ? wp_count_posts('circle')->publish : $c->count),
            'link'=>get_term_link($c->term_id),
            'file_role'=>$file_role,
            'type'=>get_term_meta($c->term_id, 'b2_circle_type', true),
            //'private'=>get_term_meta($c->term_id,'b2_circle_read',true)
        );  

        // wp_cache_set($circle_id.'_'.$user_id,$circle_data,'b2_get_circle_data',3 * MINUTE_IN_SECONDS);

        return $circle_data;
    }

    //获取圈子管理员信息
    public static function get_circle_admin_data($circle_id){

        $res = CircleRelate::get_data(array(
            'circle_id'=>$circle_id,
            'circle_role'=>'admin'
        ));

        if(!empty($res)){
            $res = $res[0];
            return self::get_circle_people_page(User::get_user_public_data($res['user_id'],true));
        }

        return self::get_circle_people_page(User::get_user_public_data(1,true));
    }

    public static function get_circle_people_page($data){

        $data['link'] = b2_get_custom_page_url('circle-people').'?id='.$data['id'];

        return $data;
    }

    //用户是否为某个圈子的管理员
    public static function is_circle_admin($user_id,$circle_id){

        if(!$user_id) return false;

        // $cache = wp_cache_get($user_id.'_'.$circle_id,'b2_is_circle_admin');

        // if($cache === 1 || $cache === 0){
        //     return $cache;
        // } 

        $isset = CircleRelate::isset(array(
            'user_id'=>$user_id,
            'circle_id'=>$circle_id,
            'circle_role'=>'admin'
        ));

        // wp_cache_set($user_id.'_'.$circle_id,($isset ? 1 : 0),'b2_is_circle_admin',10 * MINUTE_IN_SECONDS);

        return $isset;
    }

    public static function is_circle_member($user_id,$circle_id){
        if(!$user_id) return false;

        // $cache = wp_cache_get($user_id.'_'.$circle_id,'b2_is_circle_member');

        // if($cache === 1 || $cache === 0){
        //     return $cache;
        // } 

        $isset =  CircleRelate::isset(array(
            'user_id'=>$user_id,
            'circle_id'=>$circle_id,
            'circle_role'=>'member'
        ));

        // wp_cache_set($user_id.'_'.$circle_id,($isset ? 1 : 0),'b2_is_circle_member',10 * MINUTE_IN_SECONDS);

        return $isset;
    }

    public static function is_circle_pending($user_id,$circle_id){
        if(!$user_id) return false;

        // $cache = wp_cache_get($user_id.'_'.$circle_id,'b2_is_circle_pending');

        // if($cache === 1 || $cache === 0){
        //     return $cache;
        // } 

        $isset = CircleRelate::isset(array(
            'user_id'=>$user_id,
            'circle_id'=>$circle_id,
            'circle_role'=>'pending'
        ));

        // wp_cache_set($user_id.'_'.$circle_id,($isset ? 1 : 0),'b2_is_circle_pending',10 * MINUTE_IN_SECONDS);

        return $isset;
    }

    //获取用户已加入的圈子数据
    public static function get_user_circle_data($user_id,$circle_id){
        if(!$user_id) return false;
        return CircleRelate::get_data(array(
            'user_id'=>$user_id,
            'circle_id'=>$circle_id,
            'count'=>10
        ));

    }

    //加入圈子的用户数
    public static function user_count_in_circle($circle_id){

        $res = CircleRelate::get_count(array(
            'circle_id'=>$circle_id,
            'circle_role'=>'member'
        ));

        return $res + 1;
    }

    //发布话题
    public static function insert_circle_topic($data){

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        wp_set_current_user($user_id);

        //检查3小时内发布总数
        $post_count_3 = User::check_post($user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        $topic_type = $data['type'] ? $data['type'] : 'say';

        $circle_name = b2_get_option('normal_custom','custom_circle_name');

        if(!$data['circle']) return array('error'=>sprintf(__('请选择话题所在的%s','b2'),$circle_name));

        $data['circle'] = (int)$data['circle'];
        $data['topic_id'] = isset($data['topic_id']) ? (int)$data['topic_id'] : 0;

        $role = self::check_topic_role($user_id,$data['circle'],false);

        if($role['dark_room']) return array('error'=>__('小黑屋禁闭中，无法操作','b2'));
        if(!$role['allow_create_topic']) return array('error'=>__('权限不足','b2'));

        if(!isset($role['topic_type_role'][$topic_type]) || !$role['topic_type_role'][$topic_type]) return array('error'=>__('权限不足','b2'));

        if(!$role['in_circle']) return array('error'=>sprintf(__('您没有权限在此%s发布话题','b2'),$circle_name));

        if(!$role['can_post']) return array('error'=>__('您有待审的话题还未审核，请稍后再发布新话题','b2'));

        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        $censor = apply_filters('b2_text_censor', $data['title'].$data['content']);
        if(isset($censor['error'])) return $censor;

        //标题
        $data['title'] = b2_remove_kh($data['title'],true);

        if(b2getStrLen($data['title']) > 200) return array('error'=>__('标题太长，请限制在1-200个字符之内','b2'));

        if($topic_type === 'guess' && $data['title'] == ''){
            return array('error'=>__('你猜功能必须要设置一个标题','b2'));
        }

        //话题内容
        $content = wp_strip_all_tags(str_replace(array('{{','}}'),'',$data['content']));
      
        $count = b2_get_option('circle_topic','topic_word_count',$data['circle']);
        $count = explode('-',$count);

        if(b2getStrLen($content) < (int)$count[0] || b2getStrLen($content) > (int)$count[1]) return array('error'=>sprintf(__('话题字数请限制在%s到%s之间','b2'),$count[0],$count[1]));

        //检查话题类型数据
        if($topic_type !== 'say'){
            $check_type = self::check_topic_type($data);

            if(isset($check_type['error'])) return $check_type;
        }

        //检查媒体文件
        $check_file = self::check_file($data);

        if(isset($check_file['error'])) return $check_file;

        //检擦阅读权限
        $check_read_role = self::check_role($data);
        
        if(isset($check_read_role['error'])) return $check_read_role;

        $status = 'pending';

        if($role['topic_type_role']['public']){
            $status = 'publish';
        }

        $auto_title = false;

        if(empty($data['title'])){
            $data['title'] = mb_strimwidth($content,0,100,'','utf-8');
            if(strlen($data['title']) < strlen($content)) $data['title'] = $data['title'].' ......';
            $auto_title = true;
        } 

        //提交
        $arg = array(
            'post_type'=>'circle',
            'post_title' => $data['title'],
            'post_content' => $content,
            'post_status' => $status,
            'post_author' => $user_id,
        );

        if($data['topic_id']){
            if(get_post_type($data['topic_id']) !== 'circle') return array('error'=>__('文章类型错误','b2'));
            
            $is_admin = user_can( $user_id, 'manage_options' );
            $is_circle_admin = self::is_circle_admin($user_id,$data['circle']);

            if(!$is_admin && !$is_circle_admin){
                $can_delete = self::user_can_delete_post($data['topic_id'],$user_id);
                if(isset($can_delete['error'])) return $can_delete;
            }
            $arg['ID'] = (int)$data['topic_id'];
            unset($arg['post_author']);
            
            $post_id = wp_update_post($arg);

        }else{
            $post_id = wp_insert_post($arg);
        }

        if($post_id){

            User::save_check_post_count($user_id);

            apply_filters('b2_check_repo_after', $user_id,$public_count);

            //设置圈子
            wp_set_post_terms($post_id,array($data['circle']),'circle_tags');

            //设置阅读权限
            $role = self::save_topic_role($post_id,$data);

            //关联图片
            if(!empty($check_file['image']) || (isset($data['topic_id']) && (int)$data['topic_id'])){
                foreach ($check_file['image'] as $k => $v) {
                    wp_update_post(
                        array(
                            'ID' => $v, 
                            'post_parent' => $post_id
                        )
                    );
                }

                update_post_meta($post_id,'b2_circle_image',$check_file['image']);
            }

            //关联视频
            if(!empty($check_file['video']) || (isset($data['topic_id']) && (int)$data['topic_id'])){
                foreach ($check_file['video'] as $k => $v) {
                    wp_update_post(
                        array(
                            'ID' => $v, 
                            'post_parent' => $post_id
                        )
                    );
                }

                update_post_meta($post_id,'b2_circle_video',$check_file['video']);
            }

            //关联文件
            if(!empty($check_file['file']) || (isset($data['topic_id']) && (int)$data['topic_id'])){
                foreach ($check_file['file'] as $k => $v) {
                    wp_update_post(
                        array(
                            'ID' => $v['id'], 
                            'post_parent' => $post_id
                        )
                    );
                }

                update_post_meta($post_id,'b2_circle_file',$check_file['file']);
            }

            if(get_term_meta($data['circle'],'b2_circle_read',true) === 'private'){
                update_post_meta($post_id,'b2_currentCircle',1);
            }

            //关联卡片
            if(!empty($check_file['card']) || (isset($data['topic_id']) && (int)$data['topic_id'])){
                update_post_meta($post_id,'b2_circle_card',$check_file['card']);
            }

            //保存话题类型
            update_post_meta($post_id,'b2_circle_topic_type',$topic_type);

            if($auto_title){
                update_post_meta($post_id,'b2_auto_title',1);
            }

            $apply = array(
                'topic_type'=>$topic_type,
                'status'=>$status,
                'user_id'=>$user_id,
                'post_id'=>$post_id
            );

            if($topic_type !== 'say'){

                //重新计算投票数据
                if(isset($data['topic_id']) && (int)$data['topic_id'] && $topic_type == 'vote'){
                    $list = (array)get_post_meta($data['topic_id'],'b2_circle_vote_list',true);

                    foreach ($check_type['data']['vote'] as $k => $v) {
                        $check_type['data']['vote'][$k]['vote'] = isset($list[$k]['vote']) ? $list[$k]['vote'] : 0;
                    }
                }

                $apply['check_type'] = $check_type;
            }

            apply_filters('b2_publish_topic',$apply);

            $data = self::get_data_by_topic_id($post_id,$user_id);
            if(!isset($data['error'])){
                return $data;
            }
        }

        return array('error'=>__('发布失败','b2'));
    }

    public static function check_role($data){
        if(!isset($data['role']['see']) || empty($data['role']['see'])) return array('error'=>__('请设置话题的阅读权限','b2'));

        $title = str_replace(array('{{','}}'),'',$data['title']);
        $title = sanitize_text_field($title);

        if($data['role']['see'] !== 'public' && $title == ''){
            return array('error'=>__('请设置一个标题，让用户了解您隐藏的是什么内容！','b2'));
        }

        switch ($data['role']['see']) {
            case 'money':
                if((float)$data['role']['money'] <= 0 || (float)$data['role']['money'] > 99999) return array('error'=>__('阅读权限，设置的金额错误','b2'));
            break;
            case 'lv':
                if(empty($data['role']['lvPicked'])) return array('error'=>__('请选择允许查看的用户组','b2'));

                $lvs = User::get_user_roles();

                foreach ($data['role']['lvPicked'] as $k => $v) {
                    if($v !== 'verify'){
                        if(!isset($lvs[$v])) return array('error'=>sprintf(__('不存在%s用户组','b2'),$v));
                    }
                }
            break;
            case 'credit':
                if((int)$data['role']['credit'] <= 0 || (int)$data['role']['credit'] > 99999) return array('error'=>__('阅读权限，设置的积分数额错误','b2'));
            break;
        }

        return true;

    }

    public static function save_topic_role($post_id,$data){

        $role = array(
            'type'=>'public',
            'data'=>array()
        );
        switch ($data['role']['see']) {
            case 'public':
            case 'comment':
            case 'login':
                update_post_meta($post_id,'b2_topic_read_role',b2_remove_kh($data['role']['see'],true));
                $role['type'] = $data['role']['see'];
                break;
            case 'money':
                update_post_meta($post_id,'b2_topic_read_role','money');
                update_post_meta($post_id,'b2_topic_pay',(float)$data['role']['money']);
                $role['type'] = 'money';
                $role['data'] = (float)$data['role']['money'];
            break;
            case 'lv':
                update_post_meta($post_id,'b2_topic_read_role','lv');
                $role['type'] = 'lv';

                foreach ($data['role']['lvPicked'] as $k => $v) {
                    $role['data'][$k] = User::get_lv_icon($v);
                    $data['role']['lvPicked'][$k] = b2_remove_kh($v,true);
                    if(!$role['data'][$k]){
                        unset($data['role']['lvPicked'][$k]);
                    }
                }
                
                update_post_meta($post_id,'b2_topic_lvs',$data['role']['lvPicked']);
            break;
            case 'credit':
                update_post_meta($post_id,'b2_topic_read_role','credit');
                update_post_meta($post_id,'b2_topic_pay',(int)$data['role']['credit']);
                $role['type'] = 'credit';
                $role['data'] = (int)$data['role']['credit'];
            break;
        }

        if(isset($data['role']['currentCircle']) && (bool)$data['role']['currentCircle'] === true){
            update_post_meta($post_id, 'b2_currentCircle', 1);
        }else{
            delete_post_meta($post_id,'b2_currentCircle');
        }

        return $role;
    }

    //获取话题附件信息
    public static function get_topic_attachment($post_id){
        
        // if(isset($GLOBAL['b2_get_topic_attachment_'.$post_id])) return $GLOBAL['b2_get_topic_attachment_'.$post_id];

        $arr = array(
            'image'=>array(),
            'file'=>array(),
            'video'=>array(),
            'card'=>array()
        );

        $size = apply_filters('b2_get_circle_size',456);

        //图片
        $image = get_post_meta($post_id,'b2_circle_image',true);

        if(!empty($image)){
            foreach ($image as $k => $v) {
                $img_data = wp_get_attachment_metadata($v);

                if($img_data){
                    $full_size = wp_get_attachment_url($v);

                    if(!isset($img_data['width']) || !$img_data['width']){
                        $img_data['width'] = 200;
                    }

                    if(!isset($img_data['height']) || !$img_data['height']){
                        $img_data['height'] = 200;
                    }

                    $w = 200;
                    $h = round(($w*$img_data['height'])/$img_data['width']);

                    if($img_data['width'] <= 200){
                        $w = $img_data['width'];
                        $h = $img_data['height'];
                    }

                    if($h > 200){
                        $h = 200;
                        $w = round(($h*$img_data['width'])/$img_data['height']);
                    }

                    $thumb = b2_get_thumb(array('thumb'=>$full_size,'width'=>round($w*2),'height'=>round($h*2)));
                    $first_img = b2_get_gif_first($thumb);
                    $first_img_webp = apply_filters('b2_thumb_webp',$first_img);

                    $big_thumb_w = 600;
                    $big_thumb_h = round(($big_thumb_w*$img_data['height'])/$img_data['width']);

                    $big_thumb = b2_get_thumb(array('thumb'=>$full_size,'width'=>$big_thumb_w,'height'=>$big_thumb_h));

                    $small_thumb_w = 50;
                    $small_thumb_h = 50;
                    $small_thumb = b2_get_thumb(array('thumb'=>$full_size,'width'=>$small_thumb_w,'height'=>$small_thumb_h));

                    $current = $first_img ? $first_img : $thumb;

                    $arr['image'][] = array(
                        'small_thumb'=>$small_thumb,
                        'small_thumb_webp'=>apply_filters('b2_thumb_webp',$small_thumb),
                        'big_thumb'=>$big_thumb,
                        'big_thumb_webp'=>apply_filters('b2_thumb_webp',$big_thumb),
                        'big_thumb_w'=>$big_thumb_w,
                        'big_thumb_h'=>$big_thumb_h,
                        'big_ratio'=>round($img_data['height']/$img_data['width'],5),
                        'thumb_w'=>$w,
                        'thumb_h'=>$h,
                        'width'=>$img_data['width'],
                        'height'=>$img_data['height'],
                        'link'=>$full_size,
                        'id'=>$v,
                        'thumb'=>$thumb,
                        'thumb_webp'=>apply_filters('b2_thumb_webp',$thumb),
                        'gif_first'=>$first_img,
                        //'gif'=>strpos($link,'.gif') !== false ? true : false,
                        'current'=>$current,
                        'current_webp'=>$first_img ? $first_img_webp : apply_filters('b2_thumb_webp',$thumb),
                        'play'=>''
                    );
                }
            }
        }

        //视频
        $video = get_post_meta($post_id,'b2_circle_video',true);
        if(!empty($video)){
            
            foreach ($video as $k => $v) {

                $video_data = wp_get_attachment_metadata($v);
               
                $link = wp_get_attachment_url($v);


                $thumb = '';

                $thumb_id = get_post_thumbnail_id($v);
                if($thumb_id){
                    $thumb_url = wp_get_attachment_url($thumb_id);
        
                    if($thumb_url){
                        $thumb = $thumb_url;
                    }

                    $thumb_data = wp_get_attachment_metadata($thumb_id);

                }else{
                    $thumb = b2_get_yun_video_poster($link);
                }


                $w = 500;
                $h = round($w/16*9);

                if(isset($thumb_data['width']) && isset($thumb_data['height'])){
                    $h = round(($w*$thumb_data['height'])/$thumb_data['width']);
                }else{
                    $h = round($w/16*9);
                }
                

                if(isset($thumb_data['width']) && $thumb_data['width'] < 390){
                    $w = $thumb_data['width'];
                    $h = $thumb_data['height'];
                    
                    if($h > 200){
                        $h = 200;
                        $w = round(($h*$thumb_data['width'])/$thumb_data['height']);
                    }
                }

                $w_t = 390;
                $h_t = round($w_t/16*9);

                $arr['video'][] = array(
                    'thumb_w'=>$w_t,
                    'thumb_h'=>$h_t,
                    'width'=>$w_t,
                    'height'=>$h_t,
                    'height_normal'=>$h,
                    'width_normal'=>$w,
                    'link'=>$link,
                    'filesize'=>isset($video_data['filesize']) ? $video_data['filesize'] : 0,
                    'ratio'=>round($h_t/$w_t,5),
                    'ratio_normal'=>round($h/$w,5),
                    'mime_type'=>isset($video_data['mime_type']) ? $video_data['mime_type'] : '',
                    'id'=>$v,
                    'poster'=>$thumb
                );
                
            }
        }

        //文件
        $file = get_post_meta($post_id,'b2_circle_file',true);
        if(!empty($file)){
            foreach ($file as $k => $v) {
                $arr['file'][] = array(
                    'link'=>wp_get_attachment_url($v['id']),
                    'size'=>$v['size'],
                    'ext'=>$v['ext'],
                    'name'=>str_replace(array('{{','}}'),'',wp_strip_all_tags($v['name'])),
                    'id'=>$v['id']
                );
            }
        }

        //卡片
        $card = get_post_meta($post_id,'b2_circle_card',true);
        if(!empty($card)){
            foreach ($card as $k => $v) {
                $arr['card'][] = self::insert_topic_card($v);
            }
        }

        // $GLOBAL['b2_get_topic_attachment_'.$post_id] = $arr;

        return $arr;
    }

    //话题类型
    public static function check_topic_type($data){
        if(!method_exists(__CLASS__,'check_'.$data['type'])) return array('error'=>__('非法请求','b2'));
        
        $method = 'check_'.$data['type'];

        return self::$method($data);
    }

    //文件数据校验
    public static function check_file($data){

        $user_id = b2_get_current_user_id();

        $allow = self::check_topic_role($user_id,$data['circle'],false);

        $arg = array('image'=>__('图片','b2'),'video'=>__('视频','b2'),'file'=>__('文件','b2'),'card'=>__('卡片','b2'));
        
        $list = array(
            'image'=>array(),
            'video'=>array(),
            'file'=>array(),
            'card'=>array()
        );

        foreach ($arg as $k => $v) {
            if(in_array($k,$allow['media_role'])){
                $files = (array)$data[$k];
                
                $count = (int)b2_get_option('circle_topic','topic_'.$k.'_count',$data['circle']);

                $files['list'] = isset($files['list']) ? (array)$files['list'] : array();

                if(count($files['list']) > $count) 
                return array('error'=>sprintf(__('最多允许上传%s','b2'),$count.$v));

                foreach ($files['list'] as $_k => $_v) {
                    if($k === 'file'){
                        if(get_post_type($_v['id']) === 'attachment'){
                            $list[$k][] = array(
                                'id'=>(int)$_v['id'],
                                'size'=>b2_remove_kh($_v['size'],true),
                                'ext'=>b2_remove_kh($_v['ext'],true),
                                'name'=>b2_remove_kh($_v['name'],true)
                            );
                        }
                    }else{
                        if(get_post_type($_v['id'])){
                            $list[$k][] = (int)$_v['id'];
                        }
                    }
                }
            }
        }

        return $list;
    }

    //提问校验
    public static function check_ask($data){
        $ask = $data['ask'];

        $current_user = b2_get_current_user_id();

        if(!isset($ask['type'])) return array('error'=>__('提问类型错误','b2'));

        //检查提问用户
        if($ask['type'] !== 'everyone' && $ask['type'] !== 'someone') return array('error'=>__('提问类型错误','b2'));
        if($ask['type'] === 'someone' && !isset($ask['pickedList'])) return array('error'=>__('请指定提问对象','b2'));

        $ids = array();
        if(isset($ask['pickedList'])){
            foreach ((array)$ask['pickedList'] as $k => $v) {
                $user = get_userdata((int)$v['id']);
                if ($user === false) return array('error'=>__('指定的用户不存在','b2'));
                $is_admin = user_can( $current_user, 'manage_options' );
                $is_circle_admin = self::is_circle_admin($current_user,$data['circle']);

                if((int)$current_user === (int)$v['id'] && !$is_admin && !$is_circle_admin) return array('error'=>__('不能向自己提问','b2'));
                $ids[] = (int)$v['id'];
            }
        }

        if(isset($ask['pickedList']) && count((array)$ask['pickedList']) > 6) return array('error'=>__('指定的用户过多','b2'));

        if($ask['reward'] !== 'money' && $ask['reward'] !== 'credit') return array('error'=>__('奖励类型错误','b2'));

        if(!is_numeric($ask['time'])) return array('error'=>__('请输入过期时间（纯数字）','b2'));
        if($ask['time'] > 30 || $ask['time'] < 1) return array('error'=>sprintf(__('过期天数应该大于%s并且小于%s','b2'),1,30));

        //如果是积分，检查积分
        if($ask['reward'] === 'credit'){
            if((int)$ask['pay'] <= 0 || (int)$ask['pay'] > 999999) return array('error'=>__('积分数额错误','b2'));

            $credit = get_user_meta($current_user, 'zrz_credit_total', true );
            if((int)$ask['pay'] > (int)$credit) return array('error'=>__('积分不足','b2'));
        }

        if(!is_numeric($ask['pay'])) return array('error'=>__('积分或金额填写错误','b2'));

        if($ask['reward'] === 'money'){
            if((float)$ask['pay'] <= 0 || (float)$ask['pay'] > 999999) return array('error'=>__('金额错误','b2'));

            $money = get_user_meta($current_user,'zrz_rmb',true);
            if((float)$ask['pay'] > (float)$money) return array('error'=>sprintf(__('%s不足','b2'),B2_MONEY_NAME));
        }

        $end_time = wp_date('Y-m-d H:i:s',wp_strtotime('+'.$ask['time'].' day'));

        return array(
            'topic_type'=>'ask',
            'data'=>array(
                'type'=>$ask['type'],
                'pay'=>$ask['reward'] === 'money' ? (float)$ask['pay'] : (int)$ask['pay'],
                'users'=>$ids,
                'reward'=>$ask['reward'],
                'time'=>$end_time
            )
        );
    }

    //投票校验
    public static function check_vote($data){
        $vote = $data['vote'];

        $list = array();

        if(!isset($vote['type']) || ($vote['type'] !== 'pk' && $vote['type'] !== 'radio' && $vote['type'] !== 'multiple') ) return array('error'=>__('非法操作','b2'));

        $vote['list'] = (array)$vote['list'];

        foreach ($vote['list'] as $k => $v) {
            $v = b2_remove_kh($v,true);
            if($v){
                $list[] = array(
                    'title'=>$v,
                    'vote'=>0
                );
            }
        }

        if($vote['type'] === 'pk' && count($list) > 2){
            return array('error'=>__('PK只能设置两个选项','b2'));
        }

        if(count($list) < 2) return array('error'=>__('至少要设置两个投票选项','b2'));

        return array(
            'topic_type'=>'vote',
            'data'=>array(
                'type'=>$vote['type'],
                'vote'=>$list
            )
        );
    }

    //你猜校验
    public static function check_guess($data){
        $guess = $data['guess'];

        $list = array();
        if(!is_numeric($guess['right'])) return array('error'=>__('请设置一个正确答案','b2'));

        $right = '';
        $guess['list'] = (array)$guess['list'];

        foreach ($guess['list'] as $k => $v) {
            $v = b2_remove_kh($v,true);
            if($v){
                $list[] = array(
                    'title'=>$v
                );
            }
        }

        if(count($list) < 2) return array('error'=>__('请设置2个以上的题目','b2'));

        return array(
            'topic_type'=>'guess',
            'right'=>$guess['right'],
            'data'=>$list
        );
    }

    //创建圈子
    public static function create_circle($data){

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        //if(CircleRelate::isset(array('user_id'=>$user_id,'circle_role'=>'admin')) && !user_can( $user_id, 'manage_options' )) return array('error'=>sprintf(__('您已经创建过%s个圈子了!','b2'),$count));
        
        $circle_name = b2_get_option('normal_custom','custom_circle_name');

        //检查是否有权限创建圈子
        $role = User::check_user_role($user_id,'circle_create');

        if(!$role && !user_can( $user_id, 'manage_options' )) return array('error'=>sprintf(__('您没有权限创建%s','b2'),$circle_name));

        //数据合法性检查
        if($data['pay']['type'] === 'free' && ($data['role']['join'] !== 'free' && $data['role']['join'] !== 'check')){
            return array('error'=>__('非法数据','b2'));
        }

        if(!$data['tags']) return array('error'=>sprintf(__('请选择%s类别','b2'),$circle_name));

        $tags = self::get_circle_tags();
        if(isset($tags['error'])) return $tags;

        if(!in_array($data['tags'],$tags)) return array('error'=>__('不存在的类别','b2'));

        if($data['pay']['type'] === 'money'){
            $empty = true;
            foreach ($data['role']['money'] as $k => $v) {
                if(is_numeric($v) && !empty($v)){
                    $empty = false;
                }
            }

            if($empty) return array('error'=>sprintf(__('请填写加入%s所需金额','b2'),$circle_name));
        }

        if($data['pay']['type'] === 'lv'){
            if(empty($data['role']['lv'])) return array('error'=>sprintf(__('请选择允许加入%s的用户组','b2'),$circle_name));

            $lvs = User::get_user_roles();

            $setting_lvs = array();
            foreach($lvs as $k => $v){
                $setting_lvs[$k] = $v['name'];
            }

            if(b2_get_option('verify_main','verify_allow')){
                $setting_lvs['verify'] = __('认证用户','b2');
            }

            foreach ($data['role']['lv'] as $v) {
                if(!isset($setting_lvs[$v])){
                    return array('error'=>__('您选择了不存在的用户组','b2'));
                }
            }
        }

        if($data['read'] !== 'public' && $data['read'] !== 'private') return array('error'=>sprintf(__('请设置%s隐私','b2'),$circle_name));

        if(!$data['info']['icon'] || !$data['info']['name'] || !$data['info']['desc']){//|| !$data['info']['cover']
            return array('error'=>sprintf(__('请完善%s资料','b2'),$circle_name));
        }
        
        $has_icon = Post::get_attached_id_by_url($data['info']['icon']);

        if(!$has_icon){
            return array('error'=>sprintf(__('%s图标错误','b2'),$circle_name));
        }

        if($data['pay']['type'] === 'lv'){
            $data['role']['lv'] = (array)$data['role']['lv'];

            foreach ($data['role']['lv'] as $key => $value) {
                $icon = User::get_lv_icon($value);
                if(!$icon){
                    unset($data['role']['lv'][$key]);
                }
            }unset($value);

            if(empty($data['role']['lv'])) return array('error'=>__('非法参数','b2'));
        }

        // if(!preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $data['info']['cover'])){
        //     return array('error'=>__('圈子封面错误','b2'));
        // }

        $censor = apply_filters('b2_text_censor', $data['info']['name'].$data['info']['desc']);
        if(isset($censor['error'])) return $censor;

        $data['info']['name'] = b2_remove_kh($data['info']['name'],true);
        $data['info']['desc'] = b2_remove_kh($data['info']['desc'],true);
        $data['info']['slug'] = b2_remove_kh($data['info']['slug'],true);

        if(b2getStrLen($data['info']['name']) < 2 || b2getStrLen($data['info']['name']) > 20){
            return array('error'=>sprintf(__('%s名称必须大于2个字符，小于10个字符','b2'),$circle_name));
        }

        if(!$data['info']['slug']) return array('error'=>sprintf(__('请填写%s英文名称','b2'),$circle_name));

        $mb = mb_strlen($data['info']['slug'],'utf-8');
        $st = strlen($data['info']['slug']);
        if($st !== $mb) return array('error'=>__('请使用纯英文','b2'));

        if(get_term_by('slug',$data['info']['slug'], 'circle_tags')) return array('error'=>sprintf(__('%s英文名称有重复，请更换英文名称','b2'),$circle_name));

        if(get_term_by('name',$data['info']['name'], 'circle_tags')) return array('error'=>sprintf(__('%s名称有重复，请更换名称','b2'),$circle_name));

        if(b2getStrLen($data['info']['desc']) < 10 || b2getStrLen($data['info']['desc']) > 100){
            return array('error'=>sprintf(__('%s简介必须大于10个字符，小于10个字符','b2'),$circle_name));
        }
        
        $resout = wp_insert_term(
            $data['info']['name'],
            'circle_tags',
            array(
                'slug' => $data['info']['slug'],
                'description'=>$data['info']['desc']
            )
        );

        if(is_wp_error( $resout )){
            return array('error'=>$resout->get_error_message());
        }
        $circle_id = $resout['term_id'];
        
        if($data['pay']['type'] === 'free'){
            update_term_meta($circle_id, 'b2_circle_type', 'free');

            update_term_meta($circle_id, 'b2_circle_join_type', $data['role']['join']);
        }

        if($data['pay']['type'] === 'money'){
            update_term_meta($circle_id, 'b2_circle_type', 'money');

            foreach ($data['role']['money'] as $k => $v) {
                if(($k === 'permanent' || $k === 'year' || $k === 'halfYear' || $k === 'season' || $k === 'month') && (float)$v){
                    update_term_meta($circle_id, 'b2_circle_money_'.$k, (float)$v);
                }
            }
        }

        if($data['pay']['type'] === 'lv'){
            update_term_meta($circle_id, 'b2_circle_type', 'lv');
            update_term_meta($circle_id,'b2_circle_join_lv',$data['role']['lv']);
        }

        update_term_meta($circle_id,'b2_circle_read',$data['read']);

        update_term_meta($circle_id, 'b2_circle_icon', esc_url($data['info']['icon']));
        //update_term_meta($circle_id, 'b2_circle_cover', esc_url($data['info']['cover']));

        update_term_meta($circle_id,'b2_circle_tag',$data['tags']);
        
        if(CircleRelate::update_data(array(
            'user_id'=>$user_id,
            'circle_id'=>$circle_id,
            'circle_role'=>'admin',
            'join_date'=>current_time('mysql')
        ))){
            $term_link = get_term_link($circle_id);
            update_term_meta($circle_id,'b2_circle_admin',$user_id);

            return array(
                'link'=>$term_link,
                'id'=>$circle_id
            );
        };

        return array('error'=>__('创建失败','b2'));

    }

    public static function ask_time_pass($topic_id){
        $end_time = get_post_meta($topic_id,'b2_circle_ask_time',true);
        if($end_time){
            $end_time = b2_after_days($end_time);
            if($end_time['second'] < 0){
                $end_time = -1;
            }elseif($end_time['day']){
                $end_time = sprintf(__('%s天后过期','b2'),$end_time['day']);
            }elseif($end_time['hour']){
                $end_time = sprintf(__('%s小时后过期','b2'),$end_time['hour']);
            }elseif($end_time['minute']){
                $end_time = sprintf(__('%s分钟后过期','b2'),$end_time['minute']);
            }elseif($end_time['second']){
                $end_time = sprintf(__('%s秒后过期','b2'),$end_time['second']);
            }
        }else{
            $end_time = -1;
        }

        return $end_time;
    }

    public static function check_best($data){

        //如果已有采纳答案，不做处理
        if($data['best']) return;

        //如果问题未过期不做处理
        if($data['end_time'] !== -1) return;
        
        //如果是向所有人提问，不做处理
        // if($data['type'] === 'everyone') return;

        //如果已结算
        if(get_post_meta($data['topic_id'],'b2_ask_settled',true)) return;
        
        $users = self::get_answer_authors($data['topic_id']);

        $asker = get_post_field('post_author',$data['topic_id']);

        //如果没有人回答，返还提问奖励
        if(empty($users)){

            $gold_type = 1;

            if($data['reward'] === 'credit'){
                $gold_type = 0;
            }

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$asker,
                'gold_type'=>$gold_type,
                'no'=>$data['pay'],
                'post_id'=>$data['topic_id'],
                'msg'=>sprintf(__('您的提问到期没有人回答，返还 %s ：${post_id}','b2'),$data['pay']),
                'type_text'=>__('提问返还','b2'),
                'type'=>'ask_back'
            ]);

            update_post_meta($data['topic_id'],'b2_ask_settled',1);
            return;
        }

        //每人分得金额
        $pay_one = intval($data['pay']/count($users));

        //如果每人分得小于1，不处理
        if($pay_one < 1) {
            update_post_meta($data['topic_id'],'b2_ask_settled',1);
            return;
        }

        //所有回答者平分奖励
        foreach ($users as $v) {

            $gold_type = 1;

            if($data['reward'] === 'credit'){
                $gold_type = 0;
            }

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$v,
                'gold_type'=>$gold_type,
                'no'=>$pay_one,
                'post_id'=>$data['topic_id'],
                'msg'=>sprintf(__('提问者没有选择最佳答案，所有回答者均分奖金 %s ：${post_id}','b2'),$pay_one),
                'type_text'=>__('奖金均分','b2'),
                'type'=>'ask_average'
            ]);
    
        }

        update_post_meta($data['topic_id'],'b2_ask_settled',1);
        return;
    }

    public static function get_topic_type_data_by_id($topic_id,$user_id = 0){
        if(!$user_id) $user_id = b2_get_current_user_id();

        $topic_type = get_post_meta($topic_id,'b2_circle_topic_type',true);
        $arr = array(
            'type'=>$topic_type,
            'data'=>array()
        );

        if($topic_type === 'ask'){
            $users = get_post_meta($topic_id,'b2_circle_ask_users',true);
            $u = array();
            if($users){
                foreach ($users as $k => $v) {
                    $u_data = get_userdata($v);
                    if($u_data){
                        $u[] = array(
                            'id'=>$v,
                            'name'=>$u_data->display_name,
                            'link'=>get_author_posts_url($v)
                        );
                    }else{
                        $u[] = array(
                            'id'=>0,
                            'name'=>__('未知用户','b2'),
                            'link'=>'#'
                        );
                    }
                    
                }
            }

            $end_time = self::ask_time_pass($topic_id);

            $type = get_post_meta($topic_id,'b2_circle_ask_type',true);

            $pay = get_post_meta($topic_id,'b2_circle_ask_pay',true);
            $pay_read = bcdiv($pay,10,0);

            $best = get_post_meta($topic_id,'b2_ask_best',true);

            $reward = get_post_meta($topic_id,'b2_circle_ask_reward',true);

            $author = get_post_field('post_author', $topic_id);

            $arr['data'] = array(
                'reward'=>$reward,
                'type'=>$type,
                'pay'=>$pay,
                'pay_read'=>$pay_read < 1 ? 1 : $pay_read,
                'can_answer'=>$type === 'everyone' && $user_id != $author ? true : in_array($user_id,(array)$users),
                'can_read'=>self::can_read_answer($topic_id,$user_id),
                'users'=>$u,
                'end_time'=>$end_time,
                'answer_count'=>self::answer_count($topic_id),
                'best'=>$best
            );

            self::check_best(array(
                'topic_id'=>$topic_id,
                'end_time'=>$end_time,
                'type'=>$type,
                'reward'=>$reward,
                'best'=>$best,
                'pay'=>$pay,
                'users'=>$users,
                'pay_read'=>$pay_read
            ));
        }

        if($topic_type === 'vote'){
            $vote_type = get_post_meta($topic_id,'b2_circle_vote_type',true);
            $arr['data'] = array(
                'type'=>$vote_type,
                'list'=>[],
                'picked'=>$vote_type === 'radio' ? '' : []
            );

            $list = get_post_meta($topic_id,'b2_circle_vote_list',true);
            $list = is_array($list) ? $list : array();

            $right_picked = PostRelationships::get_data(array('type'=>'topic_vote','user_id'=>$user_id,'post_id'=>$topic_id));
            $count = PostRelationships::get_count(array('type'=>'topic_vote','post_id'=>$topic_id));

            if(!empty($right_picked)){
                $right_picked = $right_picked[0];
                $arr['data']['current'] = unserialize($right_picked['v']);
                $arr['data']['voted'] = true;
            }else{
                $arr['data']['voted'] = false;
                foreach ($list as $k => $v) {
                    $list[$k]['vote'] = 0;
                }
            }
            $arr['data']['total'] = $count;
            $arr['data']['list'] = $list;
            
        }

        if($topic_type === 'guess'){

            $right_guess =  get_post_meta($topic_id,'b2_circle_guess_right',true);

            $picked = false;
            $answer = '';

            if($user_id){
               $right_picked = PostRelationships::get_data(array('type'=>'topic_guess','user_id'=>$user_id,'post_id'=>$topic_id));
               if(!empty($right_picked)){
                    $right_picked = $right_picked[0];
                    $picked = (int)$right_picked['v'];
                    $answer = (int)$right_guess;
               }
            }

            $arr['data'] = array(
                'picked'=>$picked,
                'answer'=>$answer,
                'list'=>get_post_meta($topic_id,'b2_circle_guess_list',true)
            );
        }

        return $arr;
    }

    //获取话题列表
    public static function get_topic_list($data){

       
        
        $user_id = b2_get_current_user_id(); 

        if(isset($data['count']) && (int)$data['count'] <= 50){
            $count = (int)$data['count'];
        }else{
            $count = b2_get_option('circle_main','topic_per_count');
        }

        $paged = (int)$data['paged'];

        $default_circle_id = (int)get_option('b2_circle_default');

        $circle_id = isset($data['circle_id']) ? (int)$data['circle_id'] : $default_circle_id;

        $offset = ($paged - 1) * $count;

        $stickys = get_term_meta($circle_id,'b2_topic_sticky');
        
        $is_admin = user_can( $user_id, 'manage_options' );
        $is_circle_admin = self::is_circle_admin($user_id,$circle_id);

        $status = 'publish';

        if(($is_admin || $is_circle_admin) && (isset($data['status']) && $data['status'])){
            $status = $data['status'];
        }

        if(!($is_admin || $is_circle_admin) && (isset($data['status']) && $data['status'] == 'pending')){
            return array('error'=>__('您无权访问此页','b2'));
        }

        $args = array(
            'post_type' => 'circle',
            'orderby'  => 'date',
            'order'=>'DESC',
            'post_status'=>$status,
            'posts_per_page'=>(int)$count,
            'offset'=>$offset,
            'paged'=>$paged,
            'suppress_filters' => false,
            'ignore_sticky_posts' => 1,
            'post__not_in'=>$stickys,
            // 'no_found_rows' => true, 
            'fields' => 'ids',
        );

        if(isset($data['author']) && $data['author']){
            $args['author'] = (int)$data['author'];
        }

        $meta_query = array();

        if(isset($data['type']) && $data['type'] !== 'all'){
            $fliter = array('say','ask','vote','guess');
            if(!in_array($data['type'],$fliter)){
                return array('error'=>__('参数错误','b2'));
            }
            $meta_query['type'] = array(
                'key'     => 'b2_circle_topic_type',
                'value'   => $data['type'],
                'compare' => '='
            );
        }

        if(isset($data['order_by']) && $data['order_by'] !== 'date'){
            $fliter = array('up','comment','best');
            if(!in_array($data['order_by'],$fliter)){
                return array('error'=>__('参数错误','b2'));
            }

            if($data['order_by'] === 'up'){
                $args['meta_key'] = 'b2_vote_up_count';
                $args['orderby'] = 'meta_value_num';
            }

            if($data['order_by'] === 'comment'){
                $args['orderby'] = 'comment_count';
            }

            if($data['order_by'] === 'best'){

                $meta_query['best'] = array(
                    'key'     => 'b2_topic_best',
                    'value'   => 1,
                    'compare' => '='
                );
            }
        }

        if(isset($data['file']) && $data['file'] !== 'all'){
            $fliter = array('image','video','file','card');
            if(!in_array($data['file'],$fliter)){
                return array('error'=>__('参数错误','b2'));
            }
            $meta_query['file'] = array(
                'key'     => 'b2_circle_'.$data['file'],
                'compare' => 'EXISTS'
            );
        }

        if(isset($data['role']) && $data['role'] !== 'all'){
            $fliter = array('public','login','comment','money','credit','lv');
            if(!in_array($data['role'],$fliter)){
                return array('error'=>__('参数错误','b2'));
            }
            $meta_query['role'] = array(
                'key'     => 'b2_topic_read_role',
                'value'   => $data['role'],
                'compare' => '='
            );
        }

        //根据评论时间排序
        $order_by = b2_get_option('circle_topic','topic_order_by',$circle_id);

        if($order_by === ''){
            $order_by = b2_get_option('circle_topic','topic_order_by');
        }

        if($order_by == 'comment'){
            $meta_query['comment_update'] = array(
                array(
                    'key' => 'b2_hotness'
                )
            );
        }

        if($circle_id === $default_circle_id){

            $meta_query['current_circle'] = array(
                array(
                    'key'     => 'b2_currentCircle',
                    'compare' => 'NOT EXISTS'
                )
            );
        }

        $meta_query['relation'] = 'AND';
        if($order_by == 'comment' && isset($data['order_by']) && $data['order_by'] !== 'comment'){
            $args['orderby'] = 'meta_value';
            $args['order'] = array('comment_update'=>'DESC');
        }

        $args['meta_query'] = $meta_query;

        if($circle_id && $circle_id !== $default_circle_id){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'circle_tags',
                    'field' => 'term_id',
                    'terms' => $circle_id
                )
            );

        }

        //return array('error'=>$args);

        $topic_query = new \WP_Query( $args );

        $arr = array(
            'pages'=>1,
            'data'=>array()
        );

        if ( $topic_query->have_posts()) {
            $_pages = $topic_query->max_num_pages;
            while ( $topic_query->have_posts() ) {
                $topic_query->the_post();

                $topic_id = get_the_ID();

                $topic_data = self::get_data_by_topic_id($topic_id,$user_id,$topic_query);
                if(!isset($topic_data['error'])){
                    $arr['data'][] = $topic_data;
                }
            }
            
            $arr['pages'] = $_pages;

            wp_reset_postdata();
        }

        

        if(!(isset($data['author']) && $data['author']) || $user_id == (int)$data['author']){
            $pendings = self::get_user_pending_topic($user_id);
            if(!empty($pendings) && $paged === 1 && $status !== 'pending'){
                foreach ($pendings as $v) {
                    $_circle_id = (int)self::get_circle_id_by_topic_id($v);
                    if($circle_id === $_circle_id || $circle_id === $default_circle_id || (isset($data['author']) && $data['author'])){
                        $pending_topic = self::get_data_by_topic_id($v,$user_id);
                        if(!isset($pending_topic['error'])){
                            array_unshift($arr['data'],$pending_topic);
                        }
                    }
                }
            }
        }
        

        if(!empty($stickys) && $paged === 1 && $status !== 'pending' && $data['type'] === 'all'){
            $stickys_data = array();
            asort($stickys);
            foreach ($stickys as $k => $v) {
                $_new_stickys = self::get_data_by_topic_id($v,$user_id);
                if(!isset($_new_stickys['error']) && !empty($_new_stickys)){
                    $_new_stickys['sticky'] = 1;
                    array_unshift($arr['data'],$_new_stickys);
                }
            }
        }

        return $arr; 
    }

    //获取某个用户的待审文章
    public static function get_user_pending_topic($user_id){
        if(!$user_id) return array();

        // $cache = wp_cache_get($user_id,'b2_get_user_pending_topic');

        // if(is_array($cache)){
        //     return $cache;
        // }

        $recent = wp_get_recent_posts(array(
                'post_status' => 'pending',
                'numberposts' => b2_get_option('circle_topic','topic_pending_count'),
                'post_type'   => 'circle',
                'author'=>$user_id
        ));

        $res = array_column($recent,'ID');

        // wp_cache_set($user_id,$res,'b2_get_user_pending_topic',10 * MINUTE_IN_SECONDS);

        return $res;
    }

    public static function allow_read($topic_id,$user_id){

        // return $user_id;

        $data  = array(
            'allow'=>true,
            'type'=>'',
            'data'=>array(),
            'count'=>0
        );

        $current_circle_can_read = (int)get_post_meta($topic_id,'b2_currentCircle',true);

        $circle_id = (int)self::get_circle_id_by_topic_id($topic_id);
        if($current_circle_can_read && !apply_filters('b2_is_user_in_circle',$user_id,$circle_id)){
            
            $data['type'] = 'current_circle_read';
            $data['allow'] = false;
            
        }else{
            $role = get_post_meta($topic_id,'b2_topic_read_role',true);

            if(!$role){
                $data['type'] = 'allow';
            }
    
            switch ($role) {
                case 'public':
                    $data['type'] = 'public';
                    break;
                case 'login':
                    $data['type'] = 'login';
                    if(!$user_id){
                        $data['allow'] = false;
                    }
                    break;
                case 'comment':
                    $data['type'] = 'comment';
                    $data['allow'] = Shortcode::check_user_commented($user_id,$topic_id);
                    break;
                case 'money':
                case 'credit':
                    $data['type'] = $role;
                    $data['data'] = get_post_meta($topic_id,'b2_topic_pay',true);
                    
                    $buy = PostRelationships::isset(array('type'=>'circle_buy_hidden_content','user_id'=>$user_id,'post_id'=>$topic_id));
                    if(!$buy){
                        $data['allow'] = false;
                    }

                    $data['count'] = PostRelationships::get_count(array('type'=>'circle_buy_hidden_content','post_id'=>$topic_id));
                    
                    break;
                case 'lv':
                    $data['type'] = 'lv';
    
                    $lv_allow = self::allow_read_check_user_lv($topic_id,$user_id);
                    
                    foreach ($lv_allow['roles'] as $k => $v) {
                        $data['data'][] = User::get_lv_icon($v);
                    }
                    $data['allow'] = $lv_allow['allow'];
                    
                    break;
            }
        }

        return $data;
    }

    public static function get_current_user_lvs($user_id){
        $lvs = array();

        $lv = get_user_meta($user_id,'zrz_lv',true);
        $vip = get_user_meta($user_id,'zrz_vip',true);
        $verify = get_user_meta($user_id,'b2_title',true);

        if($lv){
            $lvs[] = $lv;
        }

        if($vip){
            $lvs[] = $vip;
        }

        if($verify){
            $lvs[] = 'verify';
        }

        return $lvs;
    }

    //检查当前用户阅读帖子的权限
    public static function allow_read_check_user_lv($topic_id,$user_id){

       $lvs = self::get_current_user_lvs($user_id);

        $roles = get_post_meta($topic_id,'b2_topic_lvs',true);
        $roles = is_array($roles) ? $roles : array();

        $allow = false;

        if(!empty(array_intersect($roles, $lvs))){
            $allow = true;
        }
        
        return array(
            'allow'=>$allow,
            'roles'=>$roles
        );
    }

    //通过话题ID获取话题信息
    public static function get_data_by_topic_id($topic_id,$user_id = 0,$topic_query = null){

        if(!$user_id){
            $user_id = b2_get_current_user_id();
        }

        $author_id = get_post_field ('post_author', $topic_id);

        if(!$author_id) return array();

        $author_data = self::get_circle_people_page(User::get_user_public_data($author_id,true));

        $circle = wp_get_object_terms($topic_id,'circle_tags');
        if(empty($circle)){
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            return array('error'=>sprintf(__('不存在此%s','b2'),$circle_name));
        }

        $circle = $circle[0];

        $author_data['is_circle_admin'] = self::is_circle_admin($author_id,(int)$circle->term_id);
        $author_data['is_admin'] = user_can( $author_id, 'manage_options' );

        if(get_post_type($topic_id) !== 'circle') return array('error'=>__('话题不存在','b2'));

        $date = get_the_date('Y-n-j G:i:s',$topic_id);
        
        //热门评论
        $hot_comment = Comment::get_hot_comment($topic_id);
        
        //话题点赞
        $post_vote = Post::get_post_vote_up($topic_id);

        //话题阅读权限
        $allow_read = self::allow_read($topic_id,$user_id);

        //话题类型数据
        $data = self::get_topic_type_data_by_id($topic_id,$user_id);

        $attacment = array(
            'image'=>array(),
            'file'=>array(),
            'video'=>array(),
            'card'=>array()
        );

        $title = html_entity_decode(get_the_title($topic_id));
        if(get_post_meta($topic_id,'b2_auto_title',true)){
            $title = '';
        }
        
        $content = '';

        $best = get_post_meta($topic_id,'b2_topic_best', true);

        if($allow_read['allow']){
            $content = html_entity_decode(get_post_field('post_content',$topic_id));
            $attacment = self::get_topic_attachment($topic_id);
        }

        if($data['type'] == 'guess'){
            if($data['data']['picked'] !== $data['data']['answer']){
                $content = '';
                $attacment = array(
                    'image'=>array(),
                    'file'=>array(),
                    'video'=>array(),
                    'card'=>array()
                );
            }
        }

        $stickys = get_term_meta((int)$circle->term_id,'b2_topic_sticky');

        $def = get_option('b2_circle_default');

        $status = get_post_status($topic_id);

         //return $def;

        if($status == 'pending'){
            $link = get_permalink($topic_id).'?viewtoken='.md5(AUTH_KEY.$user_id);
        }else{
            $link = get_permalink($topic_id);
        }

        return array(
            'topic_id'=>(int)$topic_id,
            'date'=>$date,
            'allow_read'=>$allow_read,
            'title'=>$title,
            'content'=>$content,
            'data'=>$data,
            'author'=>$author_data,
            'attachment'=>$attacment,
            'role'=>apply_filters('b2_circle_user_topic_role',array('user_id'=>$user_id,'topic_id'=>$topic_id,'circle_id'=>(int)$circle->term_id)),
            'meta'=>array(
                'date'=>Post::time_ago($date),
                'ctime'=>get_the_date('c',$topic_id),
                'vote'=>array(
                    'locked'=>false,
                    'up'=>$post_vote['up'],
                    'down'=>$post_vote['down'],
                    'isset_up'=>PostRelationships::isset(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$topic_id)),
                    'isset_down'=>PostRelationships::isset(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$topic_id))
                ),
                'comment'=>b2_number_format(get_comments_number($topic_id)),
            ),
            'circle'=>array(
                'id'=>(int)$circle->term_id,
                'name'=>$circle->name,
                'link'=>get_term_link($circle->term_id),
                'icon'=>b2_get_thumb(array('thumb'=>get_term_meta((int)$circle->term_id, 'b2_circle_icon', true),'width'=>100,'height'=>100))
            ),
            'hot_comment'=>$hot_comment,
            'comment'=>array(),
            'link'=>$link,
            'best'=>$best,
            'status'=>$status,
            'sticky'=>in_array($topic_id,$stickys) && $def == $circle->term_id ? 1 : 0,
            'can_comment'=>comments_open($topic_id),
            // 'gift'=>[
            //     'type'=>get_post_meta($topic_id,'single_circle_gift_key',true),
            //     'money'=>get_post_meta($topic_id,'single_circle_gift_value',true),
            //     'gift'=>get_post_meta($topic_id,'single_circle_gift_notice',true)
            // ]
        );
    }

    //获取圈子列表
    public static function get_circles_list($data){

        $user_id = b2_get_current_user_id();

        $data['count'] = (int)$data['count'];
        $data['paged'] = (int)$data['paged'];

        if($data['count'] > 50) return array('error'=>__('每页显示数量过多','b2'));

        $offset = ($data['paged'] - 1) * $data['count'];

        if($data['type'] === 'hot'){
            $arg = array(
                'taxonomy' => 'circle_tags',
                'orderby' => 'count',
                'number'=>$data['count'],
                'offset' => $offset,
                'order' => 'DESC',
                'hide_empty' => false,
            );
        }else{

            if($data['type'] === 'join'){
                $role = 'member';
            }elseif($data['type'] === 'create'){
                $role = 'admin';
            }else{
                $role = '';
            }

            if($role){
                $res = CircleRelate::get_data(array(
                    'user_id'=>$user_id,
                    'circle_role'=>$role,
                    'count'=>$data['count']
                ));
            }else{
                // $res = [];
                global $wpdb;
                $table_name = $wpdb->prefix . 'b2_circle_related';

                $res = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM $table_name WHERE `user_id`=%d AND `circle_role`!=%s ORDER BY id DESC LIMIT 8",$user_id,'pending')
                ,ARRAY_A);

            }
            

            $ids = array();

            foreach ($res as $k => $v) {
                $ids[] = $v['circle_id'];
            }

            if(empty($ids)) return array();
            
            $arg = array(
                'taxonomy' => 'circle_tags',
                'include'=>$ids,
                'orderby' => 'count',
                'number'=>$data['count'],
                'offset' => $offset,
                'order' => 'DESC',
                'hide_empty' => false,
                'cache_domain'=>'b2_circle_tags'
            );
        }


        $terms = get_terms($arg);

        $list = array();


        if(!empty($terms)){


            foreach ($terms as $k => $v) {

                $list[] = self::get_circle_data($v->term_id);

            }
        }

        return $list;
    }

    //获取圈子标签
    public static function get_circle_tags(){
        
        $tags = b2_get_option('circle_main','circle_tags');
        if(!$tags){
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            return array('error'=>sprintf(__('请设置%s标签','b2'),$circle_name));
        }
        
        $str = trim($tags, " \t\n\r\0");
        $str = explode(PHP_EOL, $str );
        $tags = array();

        foreach ($str as $k => $v) {
            $tags[] = trim($v, " \t\n\r\0");
        }

        return $tags;
    }

    //获取所有圈子信息
    public static function get_all_circles($tag = '',$paged = 1){
        
        $tags = self::get_circle_tags();
        if(!$tags){
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            return array('error'=>sprintf(__('没有%s类别','b2'),$circle_name));
        } 

        $data = array(
            'tags'=>$tags,
            'list'=>array()
        );

        foreach ($tags as $v) {
    
            $arg = array(
                'taxonomy' => 'circle_tags',
                'orderby' => 'count',
                'number'=>60,
                'exclude' => array(get_option('b2_circle_default')),
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key'       => 'b2_circle_tag',
                        'value'     => $v,
                        'compare'   => '='
                     )
                ),
                'hide_empty' => false,
                'cache_domain'=>'b2_circle_tags'
            );
            $terms = get_terms($arg);

            $circle_data = array();

            if(!empty($terms)){
                foreach ($terms as $k => $_v) {
                    $circle_data[$k] = self::get_circle_data($_v->term_id);
                }
            }

            $data['list'][] = array(
                'tag_name'=>$v,
                'list'=>$circle_data
            );
        }

        return $data;
    }

    public static function get_circle_term_data($tag = ''){
        $offset = 6;
        $arg = array(
            'taxonomy' => 'circle_tags',
            'orderby' => 'count',
            'number'=>-1,
            'offset' => $offset,
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key'       => 'b2_circle_tag',
                    'value'     => $tag,
                    'compare'   => '='
                    )
            ),
            'hide_empty' => false,
            'cache_domain'=>'b2_circle_tags'
        );
        $terms = get_terms($arg);

        $circle_data = array();

        if(!empty($terms)){
            foreach ($terms as $k => $_v) {
                $circle_data[$k] = self::get_circle_data($_v->term_id);
            }
        }

        return $circle_data;
    }

    public static function get_circle_id_by_topic_id($topic_id){

        
        $circle_id = 0;
        $terms = wp_get_object_terms($topic_id,'circle_tags');
        if(!empty($terms)){
            $terms = $terms[0];
            $circle_id = $terms->term_id;
        }

        return $circle_id;
    }

    //话题置顶
    public static function set_sticky($topic_id){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $role = self::check_topic_role($user_id,$circle_id);

        if(!$role['is_circle_admin'] && !$role['is_admin']) return array('error'=>__('您没有权限这么做','b2'));

        $type = true;

        if($circle_id){
            $stickys = get_term_meta($circle_id, 'b2_topic_sticky');

            if(in_array($topic_id,$stickys)){
                delete_term_meta($circle_id, 'b2_topic_sticky', $topic_id);
                $type = false;
            }else{
                add_term_meta($circle_id,'b2_topic_sticky', $topic_id);
            }
        }

        return $type;
    }

    //加精
    public static function set_best($topic_id){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $role = self::check_topic_role($user_id,$circle_id);

        if(!$role['is_circle_admin'] && !$role['is_admin']) return array('error'=>__('您没有权限这么做','b2'));

        $best = get_post_meta($topic_id, 'b2_topic_best',true);

        $type = true;

        if($best){
            delete_post_meta($topic_id, 'b2_topic_best');
            $type = false;
        }else{
            update_post_meta($topic_id,'b2_topic_best', 1);
        }
        
        return $type;
    }

    public static function user_can_delete_post($post_id,$user_id){

        $status = get_post_status($post_id);

        if($status == 'pending') return 'pending';

        if($status == 'draft') return 'draft';

        $author = (int)get_post_field('post_author', $post_id);

        if(user_can($user_id, 'manage_options' )){
            return 'admin';
        } 

        if($author !== (int)$user_id) return array('error'=>__('没有权限','b2'));

        $post_date = get_the_time('Y-n-j G:i:s',$post_id);

        $m = round(( wp_strtotime(current_time( 'mysql' )) - wp_strtotime($post_date)) / 60);

        if(get_post_type($post_id) === 'circle'){
            $edit_time = b2_get_option('circle_topic','topic_delete_time',self::get_circle_id_by_topic_id($post_id));
        }else{
            $edit_time = b2_get_option('circle_topic','topic_delete_time');
        }

        if($m >= $edit_time){
            $owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
            return array('error'=>sprintf(__('已过期，无法删除，请联系%s或管理员','b2'),$owner_name));
        }

        return $edit_time - $m;
    }

    //删除话题
    public static function delete_topic($topic_id){
        $user_id = b2_get_current_user_id();

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $role = self::check_topic_role($user_id,$circle_id,false);
        
        $user_can_delete = self::user_can_delete_post($topic_id,$user_id);
        
        if(isset($user_can_delete['error'])){
            
            if(!$role['is_circle_admin'] && !$role['is_admin']){
                
                return $user_can_delete;
            }
        }

        return wp_trash_post($topic_id,true) ? true : false;
    }

    //删除回答
    public static function delete_answer($answer_id){
        $user_id = b2_get_current_user_id();

        $author_id = get_post_field('post_author', $answer_id);

        $topic_id = wp_get_post_parent_id($answer_id);

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $user_can_delete = self::user_can_delete_post($answer_id,$user_id);

        $role = self::check_topic_role($user_id,$circle_id,false);

        if(isset($user_can_delete['error'])){
            if(!$role['is_circle_admin'] || !$role['is_admin']){
                return array('error'=>__('无权删除答案','b2'));
            }
        }

        // wp_cache_delete($topic_id,'b2_answer_count');

        return wp_trash_post($answer_id,true) ? true : false;
    }

    public static function get_circle_data_by_circle_ids($ids){
        $data = array();
        if(empty($ids)){
            $circle_name = b2_get_option('normal_custom','custom_circle_name');
            return array('error'=>sprintf(__('请指定%s的ID','b2'),$circle_name));
        }
        foreach ($ids as $k => $v) {
            $item = self::get_circle_data($v);
            $item['widget'] = true;
            $data[] = $item;
        }

        return $data;
    }

    //话题审核
    public static function topic_change_status($topic_id){
        $user_id = b2_get_current_user_id();

        wp_set_current_user($user_id);

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $role = self::check_topic_role($user_id,$circle_id,false);

        if(!$role['is_circle_admin'] && !$role['is_admin']){
            return array('error'=>__('您没有权限这么做','b2'));
        }

        if(get_post_status($topic_id) === 'pending'){
            $data = get_post_meta($topic_id,'b2_topic_ask_pending_data',true);
            if($data){
                $data['status'] = 'publish';
                apply_filters( 'b2_insert_ask_action', $data);
            }
            delete_post_meta($topic_id,'b2_topic_ask_pending_data');
        }

        return wp_update_post( array('ID'=>$topic_id,'post_status'=>'publish') );
    }

    //获取圈子用户
    public static function get_circle_users($circle_id,$paged,$count = 0){
        $user_id = b2_get_current_user_id();

        $is_admin = user_can( $user_id, 'manage_options' );
        $is_circle_admin = self::is_circle_admin($user_id,$circle_id);

        $circle_id = (int)$circle_id;
        $paged = (int)$paged;

        if(!$count){
            $count = 18;
        }

        $offset = ($paged - 1) * $count;

        if($circle_id == get_option('b2_circle_default')){
            $users = new \WP_User_Query( array(
                'number'=>$count,
                'offset'=>$offset
            ) );
        
            $users_found = $users->get_results();
            $total = $users->get_total();

            $data = array(
                'is_admin'=>$is_circle_admin || $is_admin,
                'list'=>array(),
                'pages'=>ceil($total/$count)
            );
    
            if(!empty($users_found)){
                foreach ($users_found as $k => $v) {
                    $data['list'][] = array(
                        'id'=>$v->ID,
                        'user_data'=>self::get_circle_people_page(User::get_user_public_data($v->ID,true)),
                        'date'=>$v->user_registered,
                        'role'=>'member',
                        'is_circle_admin'=>self::is_circle_admin($v->ID,$circle_id)
                    );
                }
            }

        }else{
            global $wpdb;
            $table_name = $wpdb->prefix . 'b2_circle_related';
    
            if($is_admin || $is_circle_admin){
                $where = "WHERE `circle_id`=$circle_id";
            }else{
                $where = "WHERE `circle_id`=$circle_id AND `circle_role`!='pending'";
            }
    
            $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
    
            $res = $wpdb->get_results("
                SELECT * FROM $table_name
                $where ORDER BY circle_role ASC,join_date ASC LIMIT $offset,$count
            ",ARRAY_A);
    
            $data = array(
                'is_admin'=>$is_circle_admin || $is_admin,
                'list'=>array(),
                'pages'=>ceil($total/$count)
            );
    
            if(!empty($res)){
                foreach ($res as $k => $v) {
                    if($v['user_id']){
                        $user = get_user_by( 'ID', $v['user_id']);
                        if($user){
                            $data['list'][] = array(
                                'id'=>$v['user_id'],
                                'user_data'=>self::get_circle_people_page(User::get_user_public_data($v['user_id'],true)),
                                'date'=>$v['join_date'],
                                'role'=>$v['circle_role'],
                                'is_circle_admin'=>self::is_circle_admin($v['user_id'],$circle_id)
                            );
                        }
                    }
                }
            }
        }

        return $data;
    }

    public static function remove_user_form_circle($user_id,$circle_id){
        $current_user = b2_get_current_user_id();

        $is_admin = user_can( $current_user, 'manage_options' );
        $is_circle_admin = self::is_circle_admin($current_user,$circle_id);

        if(!$is_admin && !$is_circle_admin){
            return array('error'=>__('您没有权限这么做','b2'));
        }

        if(CircleRelate::delete_data(array('circle_id'=>$circle_id,'user_id'=>$user_id))){
            CircleRelate::flash_cache(array('circle_id'=>$circle_id,'user_id'=>$user_id,'circle_role'=>'member'));
            CircleRelate::flash_cache(array('circle_id'=>$circle_id,'user_id'=>$user_id,'circle_role'=>'admin'));
            CircleRelate::flash_cache(array('circle_id'=>$circle_id,'user_id'=>$user_id,'circle_role'=>'pending'));
            wp_cache_delete( $circle_id.'_'.$user_id, 'b2_get_circle_data');
        }

        return true;
    }

    public static function change_user_role($user_id,$circle_id){

        $current_user = b2_get_current_user_id();

        $is_admin = user_can( $current_user, 'manage_options' );
        $is_circle_admin = self::is_circle_admin($current_user,$circle_id);

        if(!$is_admin && !$is_circle_admin){
            return array('error'=>__('您没有权限这么做','b2'));
        }

        return CircleRelate::update_data(array('circle_role'=>'member','join_date'=>current_time('mysql')),array('user_id'=>$user_id,'circle_id'=>$circle_id,'circle_role'=>'pending'));
    }

    public static function topic_vote($topic_id,$index){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $topic_id = (int)$topic_id;

        $index = explode(',',$index);
        $index = array_unique($index);

        //检查是否投票过
        $check = PostRelationships::isset(array(
            'type'=>'topic_vote',
            'user_id'=>$user_id,
            'post_id'=>$topic_id
        ));

        if($check) return array('error'=>__('您已投过票了','b2'));

        //检查话题类型
        $topic_type = get_post_meta($topic_id,'b2_circle_topic_type',true);
        if($topic_type !== 'vote') return array('error'=>__('当前话题不是投票','b2'));

        $list = get_post_meta($topic_id,'b2_circle_vote_list',true);

        if(empty($list)) return array('error'=>__('数据不完整','b2'));

        foreach ($index as $k => $v) {

            if(!isset($list[$v])) return array('error'=>__('参数错误','b2'));
            $list[$v]['vote'] = (int)$list[$v]['vote']+1;
        }

        update_post_meta($topic_id,'b2_circle_vote_list',$list);

        $index = serialize($index);

        //记录投票
        PostRelationships::update_data(array(
            'type'=>'topic_vote',
            'user_id'=>$user_id,
            'post_id'=>$topic_id,
            'v'=>$index
        ));

        do_action('b2_circle_topic_vote',$topic_id,$user_id);

        return self::get_topic_type_data_by_id($topic_id,$user_id);
    }

    public static function topic_guess($topic_id,$index){

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $index = (int)$index;

        //检查是否投票过
        $check = PostRelationships::isset(array(
            'type'=>'topic_guess',
            'user_id'=>$user_id,
            'post_id'=>$topic_id
        ));

        if($check) return array('error'=>__('您已猜过一次了','b2'));

        //检查话题类型
        $topic_type = get_post_meta($topic_id,'b2_circle_topic_type',true);
        if($topic_type !== 'guess') return array('error'=>__('当前话题不是<你猜>','b2'));

        PostRelationships::update_data(array(
            'type'=>'topic_guess',
            'user_id'=>$user_id,
            'post_id'=>$topic_id,
            'v'=>$index
        ));

        do_action('b2_circle_topic_guess',$topic_id,$user_id);

        return self::get_data_by_topic_id($topic_id,$user_id);
    }

    public static function answer_count($topic_id){

        $cache = wp_cache_get($topic_id,'b2_answer_count');

        if($cache !== false) return $cache;

        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE post_parent=%d AND post_type=%s AND post_status=%s",$topic_id,'circle_answer','publish'));

        $count = (int)$count;

        wp_cache_set($topic_id,$count,'b2_answer_count',10 * MINUTE_IN_SECONDS);

        return (int)$count;
    }

    public static function submit_topic_answer($data){

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        wp_set_current_user($user_id);

        //检查3小时内发布总数
        $post_count_3 = User::check_post($user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        $topic_id = (int)$data['parent'];

        if((isset($data['image']) && $data['image'] && !is_numeric($data['image'])) || (isset($data['file']) && $data['file'] && !is_numeric($data['file']))) return array('error'=>__('图片数据错误','b2'));

        //防止重复提交
        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        $content = b2_remove_kh($data['content']);

        if($content == '') return array('error'=>__('回答不能为空','b2'));

        $censor = apply_filters('b2_text_censor', $content);
        if(isset($censor['error'])) return $censor;
        
        //检查话题是否为问答
        $type = get_post_meta($topic_id,'b2_circle_topic_type',true);

        if($type !== 'ask') return array('error'=>__('该话题不是问答','b2'));

        $ask_type = get_post_meta($topic_id,'b2_circle_ask_type',true);

        //检查是否有权回答
        $users = get_post_meta($topic_id,'b2_circle_ask_users',true);

        if($ask_type !== 'everyone' && !in_array($user_id,$users)){
            return array('error'=>__('您无权回答这个问题','b2'));
        }

        $author = get_post_field('post_author', $topic_id);

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $filter_data = apply_filters('b2_get_topic_filter_data', $user_id,$circle_id);

        $is_admin = $filter_data['is_admin'];
        $is_circle_admin = $filter_data['is_circle_admin'];

        $arg = array(
            'post_type'=>'circle_answer',
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_parent'=>$topic_id
        );

        if($author == $user_id && !$is_admin && !$is_circle_admin){
            return array('error'=>__('您不能回答自己的问题','b2'));
        }

        $data['id'] = (int)$data['id'];

        //如果是编辑
        if($data['id']){
            $author = get_post_field('post_author', $data['id']);
            if(!$is_admin && $author != $user_id){
                return array('error'=>__('您无权编辑这个问题','b2'));
            }

            $user_can_delete = self::user_can_delete_post($data['id'],$user_id);

            if(isset($user_can_delete['error']) && !$is_admin && !$is_circle_admin){
                return array('error'=>__('已过期，无法编辑','b2'));
            }

            $arg['ID'] = (int)$data['id'];
            $arg['post_author'] = $author;

            $post_id = wp_update_post($arg);
            
        }else{
            $post_id = wp_insert_post($arg);

            if($post_id){

                update_post_meta($post_id,'b2_vote_up_count',0);
                update_post_meta($post_id,'b2_answer_best',0);
            }
        }

        if($post_id){

            User::save_check_post_count($user_id);

            if($data['image']){
                update_post_meta($post_id,'b2_circle_answer_image',(int)$data['image']);
            }

            if($data['file']){

                update_post_meta($post_id,'b2_circle_answer_file',(int)$data['file']);
            }

            apply_filters('b2_check_repo_after', $user_id,$public_count);

            return self::get_answer_by_id($post_id,$user_id);
        }

        return array('error'=>__('提交错误','b2'));

    }

    public static function get_answer_by_id($answer_id,$user_id){

        $parent = get_post_field('post_parent', $answer_id);
        
        $parent_author = get_post_field('post_author',$parent);

        $author = get_post_field('post_author',$answer_id);

        //回答点赞
        $post_vote = Post::get_post_vote_up($answer_id);

        $image = get_post_meta($answer_id,'b2_circle_answer_image',true);
        if($image){
            $img_data = wp_get_attachment_image_src($image,'full');
            $thumb = b2_get_thumb(array('thumb'=>$img_data[0],'width'=>600,'height'=>'100%'));

            // $file_size = wp_get_attachment_metadata($image);
            // $file_size = isset($file_size['filesize']) ? $file_size['filesize'] : __('未知','b2');

            $image = array(
                'size'=>0,
                'thumb'=>$thumb,
                'thumb_webp'=>apply_filters('b2_thumb_webp',$thumb),
                'width'=>$img_data[1],
                'height'=>$img_data[2],
                'full'=>$img_data[0],
                'id'=>$image
            );
        }

        $file = get_post_meta($answer_id,'b2_circle_answer_file',true);
        if($file){
            $link = wp_get_attachment_url($file);

            // $file_size = wp_get_attachment_metadata($file);
            // $file_size = isset($file_size['filesize']) ? $file_size['filesize'] : __('未知','b2');

            $file = array(
                'link'=>$link,
                'size'=>0,
                'ext'=>pathinfo($link, PATHINFO_EXTENSION),
                'name'=>pathinfo($link, PATHINFO_FILENAME),
                'id'=>$file
            );
        }

        $date = get_the_date('Y-n-j G:i:s',$answer_id);

        $can_edit = self::user_can_delete_post($answer_id,b2_get_current_user_id());
        $can_edit = is_numeric($can_edit) ? $can_edit : false;

        if(!$can_edit){
            $topic_id = wp_get_post_parent_id($answer_id);

            $circle_id = self::get_circle_id_by_topic_id($topic_id);
            $filter_data = apply_filters('b2_get_topic_filter_data', $user_id,$circle_id);

            $is_admin = $filter_data['is_admin'];
            $is_circle_admin = $filter_data['is_circle_admin'];

            if($is_admin || $is_circle_admin){
                $can_edit = true;
            }
        }

        $data = array(
            'id'=>$answer_id,
            'content'=>html_entity_decode(get_post_field('post_content', $answer_id)),
            'user'=>self::get_circle_people_page(User::get_user_public_data($author,true)),
            'date'=>Post::time_ago($date),
            'full_answer'=>false,
            'is_author'=>(int)$parent_author === (int)$user_id,
            'vote'=>array(
                'locked'=>false,
                'up'=>$post_vote['up'],
                'down'=>$post_vote['down'],
                'isset_up'=>PostRelationships::isset(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$answer_id)) ? true : false,
                'isset_down'=>PostRelationships::isset(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$answer_id)) ? true : false
            ),
            'can_edit'=>$can_edit,
            'image'=>$image,
            'file'=>$file
        );

        return $data;
    }

    public static function can_read_answer($topic_id,$user_id){

        //如果是向所有人提问
        $ask_type = get_post_meta($topic_id,'b2_circle_ask_type',true);
        if($ask_type === 'everyone') return true;

        //检查是不是提问者本人
        $author = get_post_field('post_author', $topic_id);
        if((int)$author === (int)$user_id) return true;

        //检查是不是圈主或者管理员
        $circle_id = self::get_circle_id_by_topic_id($topic_id);
        $filter_data = apply_filters('b2_get_topic_filter_data', $user_id,$circle_id);

        if($filter_data['is_admin'] || $filter_data['is_circle_admin']) return true;

        //检查是否有权回答
        $users = (array)get_post_meta($topic_id,'b2_circle_ask_users',true);
        if(in_array($user_id,$users)){
            return true;
        }

        //检查是否购买过回答
        $check = PostRelationships::isset(array(
            'type'=>'circle_buy_answer',
            'user_id'=>$user_id,
            'post_id'=>$topic_id
        ));

        if($check) return true;

        return false;
    }

    //获取答案
    public static function get_topic_answer_list($topic_id,$paged){

        $user_id = b2_get_current_user_id();

        if(!self::can_read_answer($topic_id,$user_id)) return false;

        // if(!$user_id) return array('error'=>__('请先登录','b2'));

        $count = 6;

        $topic_id = (int)$topic_id;
        $paged = (int)$paged;
        $offset = ($paged - 1) * $count;

        $topic_query = new \WP_Query(array(
            'post_parent'=>$topic_id,
            'post_type'=>'circle_answer',
            'posts_per_page'=>$count,
            'offset'=>$offset,
            'paged'=>$paged,
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                'vote_clause' => array(
                    'key' => 'b2_vote_up_count',
                    'compare' => 'EXISTS'
                ),
                'best_clause' => array(
                    'key' => 'b2_answer_best',
                    'compare' => 'EXISTS'
                )
            ),
            'orderby' => array(
                'best_clause' => 'DESC',
                'vote_clause' => 'DESC'
            )
        ));

        $data = array(
            'pages'=>0,
            'list'=>array()
        );

        if ( $topic_query->have_posts()) {
            $_pages = $topic_query->max_num_pages;
            while ( $topic_query->have_posts() ) {
                $topic_query->the_post();

                $data['list'][] = self::get_answer_by_id($topic_query->post->ID,$user_id);
            }
            
            $data['pages'] = $_pages;
        }

        wp_reset_postdata();

        return $data;

    }

    //获取回答的用户
    public static function get_answer_authors($topic_id){
        $topic_query = new \WP_Query(array(
            'post_parent'=>$topic_id,
            'post_type'=>'circle_answer',
            'post_status' => 'publish'
        ));

        $authors = array();

        if ( $topic_query->have_posts()) {
            while ( $topic_query->have_posts() ) {
                $topic_query->the_post();
                $authors[] = $topic_query->post->post_author;;
            }
        }

        wp_reset_postdata();
        array_unique($authors);

        return $authors;
    }

    //问答采纳
    public static function answer_right($answer_id){
        $user_id = (int)b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $answer_id = (int)$answer_id;

        $parent = (int)get_post_field('post_parent', $answer_id);

        if(self::ask_time_pass($parent) === -1) return array('error'=>__('问题已过期，无法采纳','b2'));
        
        //提问者
        $asker = (int)get_post_field('post_author', $parent);

        $answer = (int)get_post_field('post_author', $answer_id);

        if($user_id !== $asker){
            return array('error'=>__('无权操作！','b2'));
        }

        if($answer === $asker){
            return array('error'=>__('不能采纳自己的答案','b2'));
        }

        $best = get_post_meta($parent,'b2_ask_best',true);
        if($best) return array('error'=>__('该问题已有最佳答案','b2'));

        update_post_meta($parent,'b2_ask_best',$answer_id);
        update_post_meta($answer_id,'b2_answer_best',1);

        $pay_type = get_post_meta($parent,'b2_circle_ask_reward',true);
        $pay = get_post_meta($parent,'b2_circle_ask_pay',true);

        $gold_type = 1;

        if($pay_type === 'credit'){
            $gold_type = 0;
        }

        Message::update_data([
            'date'=>current_time('mysql'),
            'from'=>$asker,
            'to'=>$answer,
            'post_id'=>$parent,
            'msg'=>__('您的回答被${from}采纳了：${post_id}','b2'),
            'type'=>'best_answer',
            'type_text'=>__('最佳答案','b2')
        ]);

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$answer,
            'gold_type'=>$gold_type,
            'no'=>$pay,
            'post_id'=>$parent,
            'msg'=>sprintf(__('您的回答被采纳了，奖励 %s ：${post_id}','b2'),$pay),
            'type_text'=>__('最佳答案','b2'),
            'type'=>'best_answer'
        ]);

        return true;

    }

    public static function circle_pass($user_id){

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_circle_related';

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `user_id`=%d AND `circle_key`!=''",$user_id)
        ,ARRAY_A);

        if($res){
            $ids = array();
            foreach ($res as $k => $v) {
                if($v['end_date'] !== '0000-00-00 00:00:00'){
                    if($v['end_date'] < current_time('mysql')){
                        $ids[] = array(
                            'id'=>$v['id'],
                            'circle_id'=>$v['circle_id']
                        );
                    }
                }
            }

            if(!empty($ids)){
                foreach ($ids as $v) {
                    if(CircleRelate::delete_data(array('circle_id'=>$v['circle_id'],'user_id'=>$user_id))){
                        CircleRelate::flash_cache(array('circle_id'=>$v['circle_id'],'user_id'=>$user_id,'circle_role'=>'member'));
                        CircleRelate::flash_cache(array('circle_id'=>$v['circle_id'],'user_id'=>$user_id,'circle_role'=>'admin'));
                        CircleRelate::flash_cache(array('circle_id'=>$v['circle_id'],'user_id'=>$user_id,'circle_role'=>'pending'));
                        // wp_cache_delete( $v['circle_id'].'_'.$user_id, 'b2_get_circle_data');
                    }
                }
            }
        }

        return;
    }

    public static function get_edit_data($topic_id){
        $topic_id = (int)$topic_id;

        if(!$topic_id) return array('error'=>__('缺少ID','b2'));

        if(get_post_type($topic_id) !== 'circle') return array('error'=>__('文章类型错误','b2'));

        $circle_id = self::get_circle_id_by_topic_id($topic_id);

        $user_id = b2_get_current_user_id();

        $role = self::check_topic_role($user_id,$circle_id,false);
        
        $user_can_delete = self::user_can_delete_post($topic_id,$user_id);
        
        if(isset($user_can_delete['error'])){
            if(!$role['is_circle_admin'] && !$role['is_admin']){
                
                return $user_can_delete;
            }
        }

        $ask_users = get_post_meta($topic_id,'b2_circle_ask_users',true);
        $pickedList = array();
        if(!empty($ask_users)){
            foreach ($ask_users as $k => $v) {
                $avatar = get_avatar_url($v);
                $pickedList[] = array(
                    'id'=>$v,
                    'name'=>get_the_author_meta('display_name',$v),
                    'avatar'=>$avatar,
                    'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar)
                );
            }
        }

        $vote_list = get_post_meta($topic_id,'b2_circle_vote_list',true);
        $vote_list = is_array($vote_list) ? $vote_list : array('');

        $guess_list = get_post_meta($topic_id,'b2_circle_guess_list',true);
        $guess_list = is_array($guess_list) ? $guess_list : array('');

        $videos = get_post_meta($topic_id,'b2_circle_video',true);
        $att = self::get_topic_attachment($topic_id);

        $videos = $att['video'];
        $_videos = array();
        foreach ($videos as $k => $v) {
            $_videos[] = array(
                'id'=>$v['id'],
                'url'=>$v['link'],
                'poster'=>b2_get_yun_video_poster($v['link'])
            );
        }

        $images = $att['image'];
        $_images = array();
        foreach ($images as $k => $v) {
            $_images[] = array(
                'id'=>$v['id'],
                'url'=>$v['link'],
            );
        }

        $files = $att['file'];
        $_files = array();
        foreach ($files as $k => $v) {
            $_files[] = array(
                'id'=>$v['id'],
                'url'=>$v['link'],
                'size'=>$v['size'],
                'ext'=>$v['ext'],
                'name'=>$v['name']
            );
        }

        $see = get_post_meta($topic_id,'b2_topic_read_role',true);

        $lvs = get_post_meta($topic_id,'b2_topic_lvs',true);
        $lvs = !empty($lvs) ? $lvs : array();

        $ask_time = get_post_meta($topic_id,'b2_circle_ask_time',true);

        $avatar = get_avatar_url(get_post_field('post_author', $topic_id),array('size'=>50));

        return array(
            'type'=>get_post_meta($topic_id,'b2_circle_topic_type',true),
            'circle'=>self::get_circle_data($circle_id),
            'circleId'=>$circle_id,
            'ask'=>array(
                'type'=>get_post_meta($topic_id,'b2_circle_ask_type',true),
                'pay'=>get_post_meta($topic_id,'b2_circle_ask_pay',true),
                'pickedList'=>$pickedList,
                'userList'=>$ask_users,
                'reward'=>get_post_meta($topic_id,'b2_circle_ask_reward',true),
                'time'=>$ask_time ? round((wp_strtotime($ask_time) -  wp_strtotime(current_time( 'mysql' ))) / 86400) : ''
            ),
            'vote'=>array(
                'type'=>get_post_meta($topic_id,'b2_circle_vote_type',true),
                'list'=>$vote_list
            ),
            'guess'=>array(
                'list'=>$guess_list,
                'right'=>get_post_meta($topic_id,'b2_circle_guess_right',true)
            ),
            'title'=>get_post_meta($topic_id,'b2_auto_title',true) ? '' : html_entity_decode(get_the_title($topic_id)),
            'content'=>html_entity_decode(get_post_field('post_content', $topic_id)),
            'image'=>$_images,
            'video'=>$_videos,
            'file'=>$_files,
            'card'=>$att['card'],
            'role'=>array(
                'see'=>$see ? $see : 'public',
                'money'=>get_post_meta($topic_id,'b2_topic_pay',true),
                'credit'=>get_post_meta($topic_id,'b2_topic_pay',true),
                'lvPicked'=>$lvs,
                'currentCircle'=>(int)get_post_meta($topic_id,'b2_currentCircle',true)
            ),
            'userData'=>array(
                'avatar'=>$avatar,
                'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar)
            )
        );
    }

    public static function circle_search($key){
        $args = array(
            'taxonomy'      => array( 'circle_tags' ), // taxonomy name
            'orderby'       => 'count', 
            'order'         => 'ASC',
            'hide_empty'    => false,
            'fields'        => 'all',
            'count'=>10,
            'name__like'    => sanitize_text_field($key)
        ); 
        
        $terms = get_terms( $args );

        $data = array();

        if(!empty($terms)){
            foreach ($terms as $k => $v) {
                $data[] = self::get_circle_data($v->term_id);
            }
        }
        
        return $data;
    } 
}