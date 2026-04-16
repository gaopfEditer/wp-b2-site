<?php namespace B2\Modules\Common;

use B2\Modules\Common\PostRelationships;
use B2\Modules\Templates\Modules\Sliders;

class Links{

    public static function get_links_cat_url($slug){
        if(is_numeric($slug)){
            $slug = (int)$slug;
        }

        return get_term_link($slug,'link_cat');
    }

    public static function get_link_by_id($id,$jump = 'self',$link = ''){

        if($jump == 'self'){
            $links_link = b2_get_option('normal_custom','custom_links_link');

            return get_post_permalink($id);
        }else{
            if($link) return $link;

            return get_post_meta($id,'b2_link_to',true);
        }

        return '';
    }

    public static function get_children_cat($term_id,$count = 5){
        $term_id = (int)$term_id;
        $cat_list = [];
        $childrens = get_term_children($term_id,'link_cat');
        if(!is_wp_error( $childrens )){
            if($count != -1){
                $childrens = array_slice($childrens,0,$count);
            }
    
            if(!empty($childrens)){
                foreach ($childrens as $key => $value) {
                    $cat_list[] = get_term($value,'link_cat');
                }
            }
        }
        

        return $cat_list;
    }

    public static function get_default_settings($term_id,$settings = [],$custom = false){

        $count = get_term_meta($term_id,'link_count',true);

        if($count && !$custom){
            $settings = array(
                'title'=>'',
                'link_cat'=>$term_id,
                'link_show_children'=>get_term_meta($term_id,'link_show_children',true),
                'link_junp'=>get_term_meta($term_id,'link_junp',true),
                'link_count'=>$count,
                'link_count_total'=>get_term_meta($term_id,'link_count_total',true),
                'link_order'=>get_term_meta($term_id,'link_order',true),
                'link_meta'=>get_term_meta($term_id,'link_meta',true)
            );
        }
        

        return apply_filters( 'b2_links_cat_settings', wp_parse_args($settings,array(
            'title'=>'',
            'link_cat'=>$term_id,
            'link_show_children'=>false,
            'link_junp'=>'self',
            'link_count'=>4,
            'link_count_total'=>20,
            'link_order'=>'ASC',
            'link_meta'=>['title','children','more','icon','desc','user','like']
        )));
    }

    public static function get_links_data($data){

        $children = $data['link_show_children'];
        $term_id = $data['link_cat'];
        $count = (int)$data['link_count_total'] == 0 ? '15' : $data['link_count_total'];
        $order = $data['link_order'];
        $jump = $data['link_junp'] ? $data['link_junp'] : 'self';

        $user_id = b2_get_current_user_id();

        $paged = isset($data['post_paged']) ? (int)$data['post_paged'] : 1;
        $offset = ($paged -1)*(int)$count;

        $arg = array(
            'post_type'=>'links',
            'posts_per_page' => $count,
            'offset'=>$offset,
            'post_status'=>'publish',
            'orderby'=>array(
                // 'date'=>'ASC',
                'b2_link_rating'=>'ASC'
            ),
            'tax_query' => array(
                'relation' => 'AND',
            ),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'b2_link_rating',
                    'compare' => 'NUMERIC',
                ),
                array(
                    'key' => 'b2_link_rating',
                    'compare' => 'NOT EXISTS'
                )
            )
        );

        if($order == 'DESC' || $order == 'ASC'){
            $arg['orderby'] = array(
                'date'=>$order
            );
        }

        $cat_name = '';

        if($term_id){
            array_push($arg['tax_query'],array(
                'taxonomy' => 'link_cat',
                'field'    => 'term_id',
                'terms'    => $term_id,
                'include_children'=>$children,
                'operator' => 'IN'
            ));

            $cat = get_term($term_id,'link_cat');

            if(isset($cat->name)){
                $cat_name = $cat->name;
            }
        }

        if(is_singular('links')){
            global $post;
            if(isset($post->ID)){
                $arg['post__not_in'] = array($post->ID);
            }
        }

        $data = array();

        $the_query = new \WP_Query( $arg );
        $_pages = 1;

        if ( $the_query->have_posts() ) {

            $_pages = $the_query->max_num_pages;

            while ( $the_query->have_posts() ) {

                $the_query->the_post();

                $id = $the_query->post->ID;

                $owner = get_post_meta($id,'b2_link_owner',true);

                $user = get_userdata( $owner );

                $link_to = get_post_meta($id,'b2_link_to',true);
                $link_icon = get_post_meta($id,'b2_link_icon',true);
                $link_icon = b2_get_thumb(array('thumb'=>$link_icon,'width'=>50,'height'=>50));
                if($user){
                    $user = array(
                        'name'=>$user->display_name,
                        'link'=>get_author_posts_url( $owner)
                    );
                }else{
                    $user = array(
                        'name'=>'未名',
                        'link'=>''
                    );
                }

                $desc = $the_query->post->post_excerpt ? $the_query->post->post_excerpt : $the_query->post->post_content;
                $desc = $desc ? $desc : __('这个网站没有任何描述信息','b2');
                $desc = b2_get_excerpt($id);
    
                $data[] = array(
                    'pages'=>$_pages,
                    'cat_id'=>$term_id,
                    'cat_name'=>$cat_name,
                    'id'=>$id,
                    'user'=>$user,
                    'img'=>$link_icon,
                    'name'=>esc_attr(get_the_title()),
                    'url'=>self::get_link_by_id($id,$jump,$link_to),
                    'link'=>$link_to,
                    'desc'=>$desc,
                    'link_rating'=>PostRelationships::get_count(array('type'=>'link_up','post_id'=>$id)),
                    'has_rating'=>PostRelationships::isset(array('type'=>'link_up','user_id'=>$user_id,'post_id'=>$id))
                );

                //apply_filters('b2_get_post_meta', $the_query->post->ID,$data);
            }
            wp_reset_postdata();
        }

        return $data;

    }

    public static function has_pending(){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return true;

        if(user_can( $user_id, 'manage_options' )) return false;

        $args = array(
            'post_type' => 'links',
            'post_status' => 'pending',
            'posts_per_page' => 1,
            'author' => $user_id
        );

        $link = get_posts($args);

        return count($link) > 0 ? true : false;
        
    }

    public static function submit_link($data){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请登录后操作','b2'));

        wp_set_current_user($user_id);

        $data['link_owner'] = $user_id;
        $data['link_visible'] = 'N';

        $arg = array(
            'link_name'=>'',
            'link_url'=>'',
            'link_category'=>'',
            'link_image'=>'',
            'link_content'=>'',
            'link_owner'=>$user_id,
            'link_visible'=>'N'
        );

        $content_count = b2_get_option('links_submit','link_submit_content_count');

        if(!isset($data['link_content']) || b2getStrLen($data['link_content']) < $content_count){
            return array('error'=>sprintf(__('介绍内容应该大于%s个字符','b2'),$content_count));
        }

        foreach ($data as $key => $value) {
            if(!isset($arg[$key]) || $value == '') return array('error'=>__('请填写完整的参数','b2'));
            if($key == 'link_name' || $key == 'link_content'){
                $data[$key] = b2_remove_kh($value);
            }else{
                $data[$key] = b2_remove_kh($value,true);
            }
            
        }unset($value);



        //是否在允许入驻的分类中
        $allow_cats = b2_get_option('links_submit','link_submit_cats');

        if(is_array($allow_cats) && !empty($allow_cats)){
            if(!in_array((string)$data['link_category'],$allow_cats)) return array('error'=>__('不允许入驻此分类','b2'));
        }

        $data['link_url'] = esc_url($data['link_url']);

        //检查是否有待审
        if(self::has_pending($user_id) && !user_can( $user_id, 'manage_options' )) return array('error'=>__('您已经提交过，审核通过后方可再次提交','b2'));

        if(!self::or_url($data['link_url'])) return array('error'=>__('您提交的不是网址','b2'));
        if(!self::or_url($data['link_image'])) return array('error'=>__('图标格式错误','b2'));

        if(!attachment_url_to_postid($data['link_image'])) return array('error'=>__('参数错误','b2'));

        $arr = [
            'post_type'=>'links',
            'post_title'=>esc_attr($data['link_name']),
            'post_content'=>esc_attr($data['link_content']),
            'post_status'=>user_can($user_id, 'administrator' ) ? 'publish' : 'pending'
        ];

        $link_id = wp_insert_post($arr);

        if($link_id){

            //存图标
            update_post_meta($link_id,'b2_link_icon',$data['link_image']);

            //存站长
            update_post_meta($link_id,'b2_link_owner',$data['link_owner']);

            //存网址
            update_post_meta($link_id,'b2_link_to',$data['link_url']);

            //存分类
            wp_set_object_terms($link_id,intval($data['link_category']), 'link_cat');
            
        }

        return $link_id;
    }

    public static function or_url($url){
        $preg = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
    
        if(preg_match($preg,$url)){
            return true;
        }else{
            return false;
        }
    }

    public static function link_vote($link_id){
        $link_id = (int)$link_id;

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请登陆后操作','b2'));

        $vote_count = (int)get_post_meta($link_id,'b2_link_rating',true);

        $voted = PostRelationships::isset(array('type'=>'link_up','user_id'=>$user_id,'post_id'=>$link_id));

        $type = 1;

        if($voted){
            PostRelationships::delete_data(array('type'=>'link_up','user_id'=>$user_id,'post_id'=>$link_id));

            $type = -1;
        }else{
            PostRelationships::update_data(array('type'=>'link_up','user_id'=>$user_id,'post_id'=>$link_id));
        }

        update_post_meta($link_id,'b2_link_rating',$vote_count+$type);

        return $type;

    }

    public static function get_link_vote($link_id){

        $user_id = b2_get_current_user_id();

        $data = [
            'isup'=>$user_id ? PostRelationships::isset(array('type'=>'link_up','user_id'=>$user_id,'post_id'=>$link_id)) : false,
            'count'=>PostRelationships::get_count(array('type'=>'link_up','post_id'=>$link_id))
        ];
        
        return $data;
    }

    public static function link_total(){
        $term_count = wp_count_terms('link_cat',array('hide_empty' => true));

        $numlinks = wp_count_posts('links');

        return array(
            'term_count'=>$term_count,
            'link_count'=>$numlinks->publish
        );
    }

    public static function link_breadcrumb($post_id = 0){
        $shop = get_post_type_archive_link('links');
        $tax = '';

        $tax = get_the_terms($post_id, 'link_cat');
        $tax_links = '';
        $post_link = '';

        if($tax && $post_id){
            $tax = get_term($tax[0]->term_id, 'link_cat' );

            $term_id = $tax->term_id;

        }else{
            $term = get_queried_object();
            $term_id = isset($term->term_id) ? $term->term_id : 0;

        }

        if($term_id){
            $tax_links = get_term_parents_list($term_id,'link_cat');
            $tax_links = str_replace('>/<','><span>></span><',$tax_links);
            $tax_links = rtrim($tax_links,'/');
        }else{
            if(isset($_GET['s'])){
                $tax_links = __('搜索','b2');
            }else{
                $tax_links = __('未分类','b2');
            }
        }

        if($post_id){
            $post_link = '<span>></span>'.get_the_title($post_id);
        }

        return '<a href="'.B2_HOME_URI.'">'.__('首页','b2').'</a><span>></span>'.'<a href="'.$shop.'">'.b2_get_option('normal_custom','custom_links_name').'</a><span>></span>'.$tax_links.$post_link;
    }
}
