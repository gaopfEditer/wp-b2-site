<?php namespace B2\Modules\Common;

use B2\Modules\Common\Post;
use B2\Modules\Common\User;
use B2\Modules\Common\Infomation;

class Stream{
    public static function get_list($data){

        $current_user_id = b2_get_current_user_id();

        if(isset($data['count'])){
            $count = (int)$data['count'];
        }else{
            $count = get_option('posts_per_page');
        }

        $data['paged'] = isset($data['paged']) ? (int)$data['paged'] : 1;

        $offset = ($data['paged'] -1)*$count;

        if(isset($data['author']) && (int)$data['author']){
            $methods = 'b2_stream_author_post_type';
        }else{
            $methods = 'b2_stream_post_type';
        }

        $types = apply_filters($methods, array(
            'post','circle','document','newsflashes','infomation','shop'
        ));

        if(!isset($data['post_types']) || !is_array($data['post_types']) || empty($data['post_types'])){
            $data['post_types'] = $types;
        }else{
            foreach ((array)$data['post_types'] as $v) {
                if(!in_array($v,$types)){
                    return array('error'=>__('错误的文章类型','b2'));
                }
            }
        }
        
        $args = array(
            'post_type'=>$data['post_types'],
            'posts_per_page' => $count,
            'orderby' => 'date',
            'offset'=>$offset,
            'post_status'=>'publish',
            'include_children' => true,
            'paged'=>(int)$data['paged'],
            'ignore_sticky_posts'=>true
        );

      
            // //排除某些分类的文章
            // $args['tax_query'] = array(
            //     array(
            //         'taxonomy' => 'category',
            //         'field'    => 'id',
            //         'terms'    => array('分类ID'),
            //         'operator' => 'NOT IN'
            //     )
            // );

        

        if(isset($data['s']) && $data['s']){
            $args['s'] = $data['s'];
        }

        $data['author'] = isset($data['author']) ? (int)$data['author'] : 0;

        if((int)$data['author']){
            $args['author'] = (int)$data['author'];
        }

        $the_query = new \WP_Query( $args );

        $post_data = array();
        $_pages = 1;
        $_count = 0;
        if ( $the_query->have_posts() ) {

            $_pages = $the_query->max_num_pages;
            $_count = $the_query->found_posts;

            while ( $the_query->have_posts() ) {

                $the_query->the_post();

                $post_data[] = self::get_item($the_query,$data,$current_user_id);
            }
            wp_reset_postdata();
        }

        if(isset($data['pages']) || (isset($data['count']) && $data['count'])){
            return array(
                'data'=>$post_data,
                'pages'=>$_pages,
                'count'=>$_count
            );
        }

        return $post_data;

    }

    public static function get_item($the_query,$data,$current_user_id){
        
        if(isset($the_query->post->ID)){
            $post_id = $the_query->post->ID;
        }else{
            $post_id = $the_query;
        }
        
        if(isset($the_query->post->post_author)){
            $post_author = $the_query->post->post_author;
        }else{
            $post_author = get_post_field( 'post_author',$post_id);
        }

        if(isset($the_query->post->post_type)){
            $post_type =  $the_query->post->post_type;
        }else{
            $post_type =  get_post_type($post_id);
        }

        // $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url = Post::get_post_thumb($post_id,true);

        $isset_up = PostRelationships::isset(array('type'=>'post_up','user_id'=>$current_user_id,'post_id'=>$post_id));
        $isset_down = PostRelationships::isset(array('type'=>'post_down','user_id'=>$current_user_id,'post_id'=>$post_id));

        $count_vote = Post::get_post_vote_up($post_id);

        $post_meta = Post::post_meta($post_id);

        $post_meta['lv'] = User::get_user_lv($post_author,true);

        $meta = array(
            'terms'=>self::get_term_data(array('post_id'=>$post_id,'post_type'=>$post_type)),
            'meta'=>$post_meta,
            'data'=>array(
                'up'=>$count_vote['up'],
                'down'=>$count_vote['down'],
                'up_isset'=>$isset_up,
                'down_isset'=>$isset_down
            )
        );

        if($post_type == 'newsflashes'){
            $meta['meta']['date'] = b2_newsflashes_date(get_the_date('Y-n-j G:i:s',$post_id));
        }

        // $post_temp = get_post_meta($post_id,'b2_single_post_style',true);

        $imgs = array();
        
        // if($post_type === 'post-style-3'){
        //     $images = b2_get_images_from_content(get_the_content($post_id),'all');
        //     if(count($images) >= 4){
        //         foreach ($images as $k => $v) {
        //             if($k <=3){
        //                 $t = b2_get_thumb(array('thumb'=>$v,'width'=>180,'height'=>105,'ratio'=>2));
        //                 $imgs[] = array(
        //                     'thumb'=>$t,
        //                     'thumb_webp'=>apply_filters('b2_thumb_webp',$t)
        //                 );
        //             }
        //         }
        //     }
        // }

        $thumb = '';
        if($thumb_url){
            $thumb = b2_get_thumb(array('thumb'=>$thumb_url,'width'=>180,'height'=>105,'ratio'=>2));
        }
        
        if($post_type == 'infomation'){
            $info = new Infomation();
            $meta['meta']['infomation'] = $info->infomation_text($post_id);
        }

        if(!isset($data['post_type'])){
            $data['post_type'] =  $post_type;
        }

        return array(
            'id'=>$post_id,
            'title'=>array(
                'name'=>get_the_title($post_id),
                'link'=>get_permalink($post_id)
            ),
            'thumb'=>$thumb,
            'thumb_webp'=>apply_filters('b2_thumb_webp',$thumb),
            'images'=>$imgs,
            'desc'=>$data['post_type'] !== 'circle' ? b2_get_excerpt($post_id,120) : '',
            'data'=>$meta
        );
    }

    public static function get_term_data($data){

        switch ($data['post_type']) {
            case 'post':
                $type = 'category';
                $name = __('文章','b2');
                break;
            case 'circle':
                $type = 'circle_tags';
                $circle_name = b2_get_option('normal_custom','custom_circle_name');
                $name = $circle_name;
                break;
            case 'document':
                $type = 'document_cat';
                $name = b2_get_option('normal_custom','custom_document_name');
                break;
            case 'newsflashes':
                $type = 'newsflashes_tags';
                $name = b2_get_option('normal_custom','custom_newsflashes_name');
                break;
            case 'shop':
                $type = 'shoptype';
                $name = b2_get_option('normal_custom','custom_shop_name');
                break;
            case 'infomation':
                $type = 'infomation_cat';
                $name = b2_get_option('normal_custom','custom_infomation_name');
                break;
            case 'ask':
                $type = 'ask_cat';
                $name = b2_get_option('normal_custom','custom_ask_name');
                break;
            case 'answer':
                $type = '';
                $name = b2_get_option('normal_custom','custom_answer_name');
                break;
            default:
                return array();
                break;
        }

        $terms = wp_get_object_terms( $data['post_id'], $type);

        $list = array();

        if ( ! empty( $terms ) ) {
            if ( ! is_wp_error( $terms ) ) {
  
                foreach( $terms as $k=>$term ) {
                    if($k <= 3){
                        $list[] = array(
                            'id'=>$term->term_id,
                            'name'=>esc_html( $term->name ),
                            'link'=>esc_url( get_term_link( $term->slug, $type ) )
                        );
                    }
                }

            }
        }

        return array(
            'post_type'=>array('type'=>$data['post_type'],'name'=>$name),
            'terms'=> $list
        );
    }
}