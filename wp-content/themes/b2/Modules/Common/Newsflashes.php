<?php namespace B2\Modules\Common;

use B2\Modules\Common\Post;
use B2\Modules\Templates\Single;
use B2\Modules\Common\User;

class Newsflashes{ 

    public static function submit_newsflashes($data){
        $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        wp_set_current_user($user_id);

        //检查3小时内发布总数
        $post_count_3 = User::check_post($user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        //防止重复提交
        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        //检查是否有权限
        $role = User::check_user_role($user_id,'newsflashes');

        if(!$role && !user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )) return array('error'=>sprintf(__('您没有权限发布%s','b2'),$newsflashes_name));

        if(!user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' ) && $data['type'] === 'publish'){
            //检查是否有草稿
            $args=array(
                'post_type' => 'newsflashes',
                'post_status' => 'pending',
                'posts_per_page' => 3,
                'author' => $user_id
            );

            $post_count = b2_get_option('normal_write','write_can_post');

            $posts = get_posts($args);
            if(count($posts) > $post_count){
                return array('error'=>sprintf(__('您还有未审核的%s，请审核完后再提交','b2'),$newsflashes_name));
            }
        }

        if(!isset($data['content'])) return array('error'=>__('非法参数','b2'));

        if(!isset($data['title'])) return array('error'=>__('非法参数','b2'));

        $data['content'] = b2_remove_kh($data['content']);
        $data['title'] = b2_remove_kh($data['title']);

        //检查文章内容
        if(!$data['content']){
            return array('error'=>__('内容不可为空','b2'));
        }

        $censor = apply_filters('b2_text_censor', $data['content']);
        if(isset($censor['error'])) return $censor;

        //检查文章内容
        if(!$data['title']){
            return array('error'=>__('标题不可为空','b2'));
        }
        
        if(user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'editor' )){
            $data['type'] = 'publish';
        }else{
            $data['type'] = 'pending';

            $allow = b2_get_option('newsflashes_main','newsflashes_can_post');

            //是否直接发布
            if(!empty($allow) && (in_array(get_user_meta($user_id,'zrz_lv',true),$allow) || in_array(get_user_meta($user_id,'zrz_vip',true),$allow))){
                $data['type'] = 'publish';
            }
        }

        //检查标签是否存在
        $tags = b2_get_option('newsflashes_main','newsflashes_tags');
        if($tags){
            $tags = explode(',',$tags);
            if(!in_array($data['tag'],$tags)){
                return array('error'=>__('标签不存在','b2'));
            }
        }

        $data['tag'] = b2_remove_kh($data['tag'],true);

        $term = get_term_by('name', $data['tag'], 'newsflashes_tags');

        if(!$term){
            $resout = wp_insert_term(
            $data['tag'],
                'newsflashes_tags',
                array(
                    'slug' => $data['tag'],
                )
            );

            if(is_wp_error( $resout )){
                return array('error'=>$resout->error_data);
            }
            $topic = $resout['term_id'];
        }else{
            $topic = $term->term_id;
        }

        //提交
        $arg = array(
            'post_type'=>'newsflashes',
            'post_title' => $data['title'],
            'post_content' => $data['content'],
            'post_status' => $data['type'],
            'post_author' => $user_id,
        );

        $post_id = wp_insert_post( $arg );
        
        if($post_id){
            apply_filters('b2_check_repo_after', $user_id,$public_count);

            User::save_check_post_count($user_id);
            
            if(isset($data['from']) && $data['from']){
                update_post_meta($post_id,'b2_newsflashes_from',b2_remove_kh($data['from'],true));
            }

            //设置话题
            wp_set_post_terms($post_id,array($topic),'newsflashes_tags');

            //设置特色图
            if(isset($data['img']['id']) && $data['img']['id']){
                $data['img']['id'] = (int)$data['img']['id'];
                if(get_post_type($data['img']['id']) == 'attachment'){
                    set_post_thumbnail($post_id,(int)$data['img']['id']);
                }
            }

            do_action('b2_submit_newsflashes',$data,$post_id);
            
            //设置自定义字段
            if(!empty($data['custom'])){
                $custom_arr = array();
                foreach($data['custom'] as $k => $v){
                    $k = b2_remove_kh($k,true);
                    if($v){
                        if(is_array($v)){
                            $i = 0;
                            foreach ($v as $_k => $_v) {
                                $v[$i] = b2_remove_kh($_v,true);
                                $i++;
                            }
                        }else{
                            $v = b2_remove_kh($v,true);
                        }
                        $custom_arr[] = $k;
                        update_post_meta($post_id,$k,$v);
                    }
                }

                update_post_meta($post_id,'b2_custom_key',$custom_arr);
            }
    
            return get_author_posts_url($user_id).'/post';
        }

        return array('error'=>__('发布失败','b2'));
    }

    public static function get_newsflashes_item_data($post_id){

        $user_id = b2_get_current_user_id();

        $from = get_post_meta($post_id,'b2_newsflashes_from',true);

        $author = get_post_field('post_author',$post_id);

        $img =  get_the_post_thumbnail_url($post_id,'full');
        $img = $img ? $img : b2_get_first_img(get_post_field('post_content',$post_id),0);

        $tax = get_the_terms($post_id, 'newsflashes_tags');

        $tag = array();

        if($tax){
            $tax = $tax[0];
            $link = get_term_link($tax->term_id);
            $tag = array(
                'id'=>$tax->term_id,
                'name'=>$tax->name,
                'link'=>esc_url($link)
            );
        }

        $vote = Post::get_post_vote_up($post_id);

        $vote['up_isset'] = PostRelationships::isset(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id));
        $vote['down_isset'] = PostRelationships::isset(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id));
        $vote['up_text'] = b2_get_option('newsflashes_main','newsflashes_vote_up_text');
        $vote['down_text'] = b2_get_option('newsflashes_main','newsflashes_vote_down_text');

        $thumb = '';

        if($img){
            $thumb = b2_get_thumb(array('thumb'=>$img,'width'=>720,'height'=>480));
        }
        
        $date = get_the_date('Y-n-j G:i:s',$post_id);
        $data = array(
            'id'=>$post_id,
            'title'=>html_entity_decode(get_the_title($post_id)),
            'link'=>esc_url(get_permalink($post_id)),
            '_date'=>Post::time_ago($date),
            'date'=>b2_newsflashes_date($date),
            '__date'=>b2_newsflashes_date_day($date),
            'content'=>html_entity_decode(get_the_content($post_id)),
            'desc'=>html_entity_decode(b2_get_excerpt($post_id)),
            'author'=>User::get_user_public_data($author,true),
            'from'=>$from,
            'img'=>$thumb,
            'img_webp'=>$thumb ? apply_filters('b2_thumb_webp',$thumb) : '',
            'tag'=>$tag,
            'vote'=>$vote,
            'comment_count'=>b2_number_format(get_comments_number($post_id)),
            'share'=>Single::get_share_links(false,$post_id)
        );

        return apply_filters('b2_get_newsflashes_item_data',$data,$post_id);
    }

    public static function get_newsflashes_data($paged,$term_id = 0,$user_id = 0,$s = '',$count = 0){

        if(!$count){
            $count = b2_get_option('newsflashes_main','newsflashes_show_count');
        }
        
        $offset = ($paged -1)*$count;
        
        $_pages = 0;

        $args = array(
            'post_type' => 'newsflashes',
            'orderby'  => 'date',
            'order'=>'DESC',
            'post_status'=>'publish',
            'posts_per_page'=>$count,
            'offset'=>$offset,
            'paged'=>$paged,
        );

        if($s){
            $args['search_tax_query'] = true;
            $args['s'] = esc_attr($s);
        }

        if($term_id){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'newsflashes_tags',
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                )
            );
        }
        if($user_id){
            $args['author__in'] = $user_id;
        }
        
        $news_the_query = new \WP_Query( $args );

        $group = array();
        $index = 0;
        $key = '';
        if ( $news_the_query->have_posts()) {
            $_pages = $news_the_query->max_num_pages;
            while ( $news_the_query->have_posts() ) {
                $news_the_query->the_post();
                $data = self::get_newsflashes_item_data($news_the_query->post->ID);
                $data['paged'] = $paged;
                
                if(!$key){
                    $key = $data['date']['key'];
                }elseif($key !== $data['date']['key']){
                    $index++;
                }

                $group[$index][] = $data;

                $key = $data['date']['key'];
            }
        }
        wp_reset_postdata();

        return array(
            'data'=>$group,
            'pages'=>$_pages
        );
    }

    //快讯小工具数据
    public static function get_widget_Newsflashes($options){
        $options['post_type'] = 'newsflashes';
        $options['no_found_rows'] = true;
        $options['posts_per_page'] = $options['posts_per_page'] > 30 ? 6 : $options['posts_per_page'];
        $news_the_query = new \WP_Query( $options );

        $group = array();

        if ( $news_the_query->have_posts()) {
            while ( $news_the_query->have_posts() ) {
                $news_the_query->the_post();
                $data = self::get_newsflashes_item_data($news_the_query->post->ID);
 
                $group[] = $data;

            }
            
        }

        wp_reset_postdata();

        return $group;
    }

}