<?php namespace B2\Modules\Common;
use B2\Modules\Common\Circle;
class Seo{
    public function init(){
        if((int)b2_get_option('normal_main','open_seo') === 0){
            add_action('wp_head',array($this,'seo_head_meta'),5);
            add_filter( 'document_title_parts', array($this,'custom_page_document_title'));
        }
    }

    public function custom_page_document_title($title){
        
        if(is_singular()){
            global $post;
            $custom_title = self::get_post_meta($post->ID, 'zrz_seo_title');

            if($custom_title){ 
                $title["title"] = esc_attr($custom_title); 
            }
        }elseif(is_archive()){
            $term = get_queried_object();
            if(isset($term->term_id)){
                $custom_title = self::get_term_meta($term->term_id,'seo_title');
                if($custom_title){ 
                    $title["title"] = esc_attr($custom_title); 
                }
            }else{
                $title["title"] = '';
            }
            
        }

        return $title;
    }

    public function seo_head_meta(){

        $name = B2_BLOG_NAME;

        global $wp;
        $current_url = B2_HOME_URI.'/'.add_query_arg( array(), $wp->request );

        $head = b2_get_option('normal_main','header_code');

        $single = is_singular();

        $type = 'article';

        if($single){
            global $post;
            $post_type = get_post_meta($post->ID,'b2_single_post_style',true);
            if($post_type == 'post-style-5'){
                $type = 'video';
            }
        }

        if($head){
        echo $head;
        }

        echo '
    <meta property="og:locale" content="'.get_locale().'" />
    <meta property="og:type" content="'.$type.'" />
    <meta property="og:site_name" content="'.$name.'" />
    <meta property="og:title" content="'.wp_get_document_title().'" />
    <meta property="og:url" content="'.$current_url.'" />
        ';

        if(is_home() || is_front_page()){
           

                $meta = self::home_meta();
    
                echo '
        <meta name="keywords" content="'.$meta['keywords'].'" />
        <meta name="description" content="'.$meta['description'].'" />
        <meta property="og:image" content="'.$meta['image'].'" />
                ';
            
        }elseif(is_post_type_archive('shop')){
            echo '
    <meta name="keywords" content="'.b2_get_option('shop_main','shop_keywords').'" />
    <meta name="description" content="'.b2_get_option('shop_main','shop_desc').'" />
            ';
        }elseif(is_post_type_archive('newsflashes')){
            echo '
    <meta name="keywords" content="'.b2_get_option('newsflashes_main','newsflashes_tdk_keywords').'" />
    <meta name="description" content="'.b2_get_option('newsflashes_main','newsflashes_tdk_desc').'" />
            ';
        }elseif(is_post_type_archive('document')){
            echo '
    <meta name="keywords" content="'.b2_get_option('document_main','document_tdk_keywords').'" />
    <meta name="description" content="'.b2_get_option('document_main','document_tdk_desc').'" />
            ';
        }elseif(is_post_type_archive('circle')){
            echo '
    <meta name="keywords" content="'.b2_get_option('circle_main','circle_keywords').'" />
    <meta name="description" content="'.b2_get_option('circle_main','circle_desc').'" />
            ';
        }elseif(is_post_type_archive('links')){
            echo '
    <meta name="keywords" content="'.b2_get_option('links_main','links_tdk_keywords').'" />
    <meta name="description" content="'.b2_get_option('links_main','links_tdk_desc').'" />
            ';
        }elseif(is_post_type_archive('infomation')){
            echo '
        <meta name="keywords" content="'.b2_get_option('infomation_main','infomation_tdk_keywords').'" />
        <meta name="description" content="'.b2_get_option('infomation_main','infomation_tdk_desc').'" />
            ';
        }elseif(is_post_type_archive('ask')){
            echo '
        <meta name="keywords" content="'.b2_get_option('ask_main','ask_tdk_keywords').'" />
        <meta name="description" content="'.b2_get_option('ask_main','ask_tdk_desc').'" />
            ';
        }elseif($single){
            
            $meta = self::single_meta();

            echo '
    <meta name="keywords" content="'.$meta['keywords'].'" />
    <meta name="description" content="'.$meta['description'].'" />
    <meta property="og:image" content="'.$meta['image'].'" />
    <meta property="og:updated_time" content="'.$meta['updated_time'].'" />
    <meta property="article:author" content="'.$meta['author'].'" />
            ';

        }elseif(is_archive()){
            $term = get_queried_object();
            
            if(isset($term->term_id)){
                $img = self::get_term_meta($term->term_id,'b2_tax_img');
    
                echo '
    <meta name="keywords" content="'.self::get_term_meta($term->term_id,'seo_keywords').'" />
    <meta name="description" content="'.trim(strip_tags(get_the_archive_description())).'" />
    <meta property="og:image" content="'.b2_get_thumb(array('thumb'=>$img,'width'=>600,'height'=>400)).'" />
                ';
            }

        }
        
    }

    public static function get_desc(){
        if(is_singular()){
            $meta = self::single_meta();
            return $meta['description'];
        }

        if(is_archive()){
            return trim(strip_tags(get_the_archive_description()));
        }

        $meta = self::home_meta();

        return $meta['description'];
    }

    public static function get_term_meta($term_id,$key) {
        $value = get_term_meta($term_id,$key,true);
        $value = esc_attr($value);
        return $value;
    }

    public static function get_post_meta($post_id,$key) {
        $value = get_post_meta($post_id,$key,true);
        $value = esc_attr($value);
        return $value;
    }

    public static function home_meta(){

        $des = b2_get_option('normal_main','home_description');
        $img_logo = b2_get_option('normal_main','img_logo');

        return array(
            'keywords'=>b2_get_option('normal_main','home_keywords'),
            'description'=>$des ? $des : B2_BLOG_DESC,
            'title'=>B2_BLOG_NAME,
            'image'=>$img_logo
        );
    }

    public static function single_meta($post_id = 0){
        if(!$post_id){
            global $post;
            $post_id = $post->ID;
        }

        $post_type = get_post_type($post_id);

        $allow_read = true;
        if($post_type == 'circle'){
            $allow_read = Circle::allow_read($post_id,0);
            $allow_read = $allow_read['allow'];
        }

        $author = esc_attr(get_post_field('post_author',$post_id));
        $title = get_the_title($post_id);

        $thumb_url = Post::get_post_thumb($post_id);

        $desc = '';

        if($allow_read){
            $desc = self::get_post_meta($post_id,'zrz_seo_description');

            $desc = $desc ? $desc : b2_get_excerpt($post_id,100);
        }

        $key = self::get_post_meta($post_id,'zrz_seo_keywords');
        if(!$key){
            $key = wp_get_post_tags($post_id);
            $key = array_column($key, 'name');
            $key = implode(',',$key);
        }

        if($post_type == 'ask'){
            $tagsarr = [];
            $tags = wp_get_post_terms( $post_id, 'ask_cat');

            if(!empty($tags)){
                foreach ($tags as $tag) {
                    $tagsarr[] = $tag->name;
                }
            }

            $key = implode(',',$tagsarr);
        }

        return array(
            'id'=>$post_id,
            'title'=>esc_attr($title),
            'keywords'=>$key,
            'description'=>$desc,
            'image'=>b2_get_thumb(array('thumb'=>$thumb_url,'width'=>600,'height'=>400)),
            'url'=>esc_url(get_permalink($post_id)),
            'updated_time'=>get_the_modified_date('c',$post_id),
            'author'=>get_author_posts_url($author)
        );
    }
}