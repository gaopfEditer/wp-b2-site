<?php namespace B2\Modules\Common;

use B2\Modules\Common\PostRelationships;

class UpdateData{
    public static function commentVote($paged){

        $user_id = get_current_user_id();

        if(!user_can( $user_id, 'manage_options' )) return array('error'=>__('只能管理员操作','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'commentmeta';

        $number = 20;
        $offset = ($paged-1)*$number;

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `meta_key`=%s LIMIT $offset,$number",'zrz_comment_vote'),
            ARRAY_A
        );

        if(!empty($res)){
            foreach ($res as $k => $v) {
                $comment_id = $v['comment_id'];
                $users = unserialize($v['meta_value']);

                $comment_data = get_comment($comment_id); 

                $up = isset($users['comment_up']) ? $users['comment_up'] : array();
                $down = isset($users['comment_down']) ? $users['comment_down'] : array();

                if(!empty($up)){
                    foreach ($up as $k => $v) {
                        $arg = array('type'=>'comment_up','user_id'=>$v,'post_id'=>$comment_data->comment_post_ID,'comment_id'=>$comment_id);
                        if(!PostRelationships::get_count($arg)){
                            PostRelationships::update_data($arg);
                        }
                        
                    }
                }

                if(!empty($down)){
                    foreach ($down as $k => $v) {
                        if(!in_array($v,$up)){
                            $arg = array('type'=>'comment_down','user_id'=>$v,'post_id'=>$comment_data->comment_post_ID,'comment_id'=>$comment_id);
                            if(!PostRelationships::get_count($arg)){
                                PostRelationships::update_data($arg);
                            }
                        }
                    }
                }

                //delete_comment_meta($comment_id,'zrz_comment_vote');
            }
            return 'go';
        }else{
            return 'success';
        }
    }

    public static function postVote($paged){

        $paged = (int)$paged;

        $user_id = get_current_user_id();

        if(!user_can( $user_id, 'manage_options' )) return array('error'=>__('只能管理员操作','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';

        $number = 20;
        $offset = ($paged-1)*$number;

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `meta_key`=%s LIMIT $offset,$number",'b2_vote'),
            ARRAY_A
        );

        if(!empty($res)){
            foreach ($res as $k => $v) {
                $post_id = $v['post_id'];
                $users = unserialize($v['meta_value']);

                $up = isset($users['up']) ? $users['up'] : array();
                $down = isset($users['down']) ? $users['down'] : array();

                if(!empty($up)){
                    foreach ($up as $k => $v) {
                        $arg = array('type'=>'post_up','user_id'=>$v,'post_id'=>$post_id);
                        if(!PostRelationships::get_count($arg)){
                            PostRelationships::update_data($arg);
                        }
                    }
                }

                if(!empty($down)){
                    foreach ($down as $k => $v) {
                        if(!in_array($v,$up)){
                            $arg = array('type'=>'post_down','user_id'=>$v,'post_id'=>$post_id);
                            if(!PostRelationships::get_count($arg)){
                                PostRelationships::update_data($arg);
                            }
                        }
                    }
                }

                //delete_comment_meta($comment_id,'zrz_comment_vote');
            }
            return 'go';
        }else{
            return 'success';
        }
    }

    static function avatar_save_to_localhost(){

        $user_id = get_current_user_id();

        if(!user_can( $user_id, 'manage_options' )) return array('error'=>__('只能管理员操作','b2'));

        global $wpdb;

        $paged = 3;

        $number = 2000;
        $offset = ($paged-1)*$number;

        $table_name = $wpdb->prefix.'usermeta';
        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE `meta_key`='zrz_open' LIMIT $offset,$number",ARRAY_A);


        $dir = wp_get_upload_dir();

        $dir = $dir['basedir'];

        $old = 'https://static.7b2.com/wp-content/uploads/';

        foreach ($data as $v) {
            $res = unserialize($v['meta_value']);
            if(isset($res['avatar']) && $res['avatar'] != ''){

                $file_path = $dir.B2_DS.str_replace('/',B2_DS,$res['avatar']);
                $dir_path = dirname($file_path);

                $content = file_get_contents($old.$res['avatar']);

                if(!is_dir($dir_path)) {
                    mkdir($dir_path, 0755, true);
                }
                file_put_contents($file_path, $content);
            }

            if(isset($res['cover']['key']) && $res['cover']['key'] != ''){

                $file_path = $dir.B2_DS.str_replace('/',B2_DS,$res['cover']['key']);
                $dir_path = dirname($file_path);

                $content = file_get_contents($old.$res['cover']['key']);

                if(!is_dir($dir_path)) {
                    mkdir($dir_path, 0755, true);
                }
                file_put_contents($file_path, $content);
            }

        }

        echo 'success';
    }

    static function save_comment_img_to_localhost(){

        $user_id = get_current_user_id();

        if(!user_can( $user_id, 'manage_options' )) return array('error'=>__('只能管理员操作','b2'));

        global $wpdb;

        $paged = 1;

        $number = 8000;
        $offset = ($paged-1)*$number;

        $table_name = $wpdb->prefix.'comments';
        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE `comment_approved`='1' LIMIT $offset,$number",ARRAY_A);

        $dir = wp_get_upload_dir();

        $dir = $dir['basedir'];

        $old = 'https://static.7b2.com/wp-content/uploads/';

        foreach ($data as $v) {

            preg_match_all('/(\s+src\s?\=)\s?[\'|"]([^\'|"]*)/is',$v['comment_content'], $match);
        
            if(isset($match[0]) && !empty($match[0])){
                foreach ($match[0] as $_v) {

                    $_v = str_replace(' src="','',$_v);
                    
                    if(strpos($_v,$old) !== false){

                        $dir = str_replace($old,'/www/wwwroot/www.7b2.com/wp-content/uploads/',$_v);
                        // var_dump($dir);
                        // exit;
                        $dir_path = dirname($dir);

                        $content = file_get_contents($_v);

                        if(!is_dir($dir_path)) {
                            mkdir($dir_path, 0755, true);
                        }
                        file_put_contents($dir, $content);

                    }
                }
                
            }
        }
    }

    static function indexModules(){

        $user_id = get_current_user_id();

        if(!user_can( $user_id, 'manage_options' )) return array('error'=>__('只能管理员操作','b2'));

        $old = get_option('b2_template_index');
        $group = isset($old['index_group']) && !empty($old['index_group']) ? (array)$old['index_group'] : false;

        if(!$group) return;

        $cats = array();
        foreach ($group as $key=>$_old) {
            if(isset($_old['module_type']) && $_old['module_type'] == 'posts'){
                if(!isset($_old['post_cat'])) continue;
                $sulg = (array)$_old['post_cat'];
                foreach($sulg as $s){
                    $term = get_term_by('slug', $s, 'category');
                    if(isset($term->name)){
                        $cats[$key][] = $term->term_id;
                    }
                }
                
            }
        }

        if(!empty($cats)){
            foreach ($cats as $key => $value) {
                $old['index_group'][$key]['post_cat'] = $value;
            }
            
            update_option('b2_template_index',$old);

            b2_delete_index_cache();
        }

        return 'success';

    }

    static function updateCats(){
        $user_id = get_current_user_id();

        if(!user_can( $user_id, 'manage_options' )) return array('error'=>__('只能管理员操作','b2'));

        //升级文档页面
        $cats = get_option('b2_document_main');

        if(!$cats) return;

        if(!isset($cats['document_cat'])) return;

        if($cats['document_cat']){
            $ids = array();
            foreach ($cats['document_cat'] as $k => $v) {
                $t = get_term_by('slug', $v, 'document_cat');
                if(is_wp_error( $t ) || !isset($t->name)) continue;
                $ids[] = $t->term_id;
            }

            if(!empty($ids)){
                $cats['document_cat'] = $ids;

                update_option('b2_document_main',$cats);
            }
        }

        //升级分类目录页面
        $args = array(
            'hide_empty'      => false,
        );

        $cats = get_categories($args);

        if(!empty($cats)){
            foreach ($cats as $cat) {
                $item = get_term_meta($cat->term_id,'b2_filter',true);

                if(isset($item[0]['cat'])){
                    foreach ($item[0]['cat'] as $key => $sulg) {
                        $t = get_term_by('slug', $sulg, 'category');
                        if(is_wp_error( $t ) || !isset($t->name)) continue;
                        $item[0]['cat'][$key] = $t->term_id;
                    }
                }

                if(isset($item[0]['collection'])){
                    foreach ($item[0]['collection'] as $key => $sulg) {
                        $t = get_term_by('slug', $sulg, 'collection');
                        if(is_wp_error( $t ) || !isset($t->name)) continue;
                        $item[0]['collection'][$key] = $t->term_id;
                    }
                }

                update_term_meta($cat->term_id, 'b2_filter',$item);
            }
        }

        return 'success';
    }
}


// function b2_replace_post_meta(){

//     global $wpdb;
//     $table_name = $wpdb->prefix . 'postmeta';

//     $res = $wpdb->get_results(
//         $wpdb->prepare("SELECT * FROM $table_name WHERE `meta_key`=%s",'b2_single_post_video_group'),
//         ARRAY_A
//     );

//     if(!empty($res)){
//         foreach ($res as $k => $v) {
//             $post_id = $v['post_id'];
//            if($v['meta_value']){
//                 $arr = unserialize($v['meta_value']);
//                 foreach ($arr as $_k => $_v) {
//                     $arr[$_k]['poster'] = str_replace('https://baidu.com','https://bilibili.com',$arr[$_k]['poster']);
//                     $arr[$_k]['url'] = str_replace('https://baidu.com','https://bilibili.com',$arr[$_k]['url']);
//                 }
//                 update_post_meta($post_id,'b2_single_post_video_group',$arr);
//            }
//         }
//         return 'go';
//     }else{
//         return 'success';
//     }
// }