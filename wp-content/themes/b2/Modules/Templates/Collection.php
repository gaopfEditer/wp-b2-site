<?php namespace B2\Modules\Templates;

use B2\Modules\Common\Post as Cpost;

class Collection{
    public function init(){
        //add_action('b2_archive_collection_top',array($this,'collection_top'),10);
    }

    public function collection_top(){
        $term = get_queried_object();

        if($term && isset($term->term_id)){
            $thumb = get_term_meta($term->term_id,'b2_tax_img',true);
        }

        $thumb = b2_get_thumb(array('thumb'=>$thumb,'height'=>354,'width'=>1000));

        $desc = get_the_archive_description();
        $title = get_the_archive_title();

        echo '
            <div class="collection-list-top mg-t-">
                <div class="collection-list-bg" style="background-image:url('.$thumb.')"></div>
                <div class="content-area wrapper">
                    <h1>'.$title.'</h1>
                    '.$desc.'
                    <div class="read-more mg-t">
                        <a href="'.b2_get_custom_page_url('collection').'" target="_blank">'.__('查看往期专题>>','b2').'</a>
                    </div>
                </div>
            </div>
        ';
    }

    public static function get_collection_list($arg){
        
        $orderby = b2_get_option('template_collection','collection_orderby');
        $orderby = $orderby = 'number' ? 'count' : $orderby;

        if(isset($arg['count']) && $arg['count']){
            $number = $arg['count'];
        }else{
            $number = b2_get_option('template_collection','collection_number');
        }

        $offset = ($arg['paged'] -1)*$number;

        $order = b2_get_option('template_collection','collection_order');
        $order = $order ? $order : 'DESC';

        $args= apply_filters( 'b2_collection_list_args',array(
            'taxonomy' => 'collection',
			'hide_empty' => true,
            'order'=>$order,
            'orderby' => 'meta_value_num',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'b2_tax_index',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => 'b2_tax_index',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'count'                  => true,
            'number'=>$number,
            'offset'=>$offset
        ),$arg);

        $the_query = new \WP_Term_Query($args);
        $data = array();

        foreach($the_query->get_terms() as $v){
            
            $data[] = array(
                'id'=>$v->term_id,
                'thumb'=>b2_get_thumb(array(
                    'thumb'=>get_term_meta($v->term_id,'b2_tax_img',true),
                    'width'=>500,
                    'height'=>200
                )),
                'q'=>get_term_meta($v->term_id,'b2_tax_index',true),
                'count'=>$v->count,
                'name'=>$v->name,
                'posts'=>isset($arg['posts']) ? '' : self::get_collection_post_list($v->term_id),
                'desc'=>$v->description ? $v->description : '',
                'link'=>get_term_link($v->term_id)
            );
        }

        unset($args['number']);
        unset($args['offset']);

        $count = wp_count_terms( 'collection', $args );

        return array(
            'data'=>$data,
            'count'=>$count,
            'pages'=>ceil($count/$number)
        );
    }

    public static function get_collection_post_list($term_id,$count = 0,$paged = 1){

        if($count > 50){
            $count = 5;
        }

        $order = b2_get_option('template_collection','collection_post_order');
  
        $args = array(
            'post_type'=>'post',
            'post_status'=>'publish',
            'order'=>$order ? $order : 'asc',
            'tax_query' => array(
                array(
                    'taxonomy' => 'collection',
                    'field'    => 'term_id',
                    'terms'    => $term_id
                )
            ),
            'posts_per_page'=>$count ? $count : 5,
        );

        $post_query = new \WP_Query( $args );

        $arr = array();
        $post_count = 0;
        if ( $post_query->have_posts() ) {
            $post_count = $post_query->found_posts;
            while ( $post_query->have_posts() ) {

                $post_query->the_post();

                $post_cats = get_the_category($post_query->post->ID);
                $cat = isset($post_cats[0]) ? $post_cats[0] : array();

                if(!empty($cat)){
                    $color = get_term_meta($cat->term_id,'b2_tax_color',true);
                    $color = $color ? $color : '#607d8b';
                    $link = get_category_link( $cat->term_id );
                    $cat = array(
                        'name'=>$cat->name,
                        'color'=>$color,
                        'link'=>$link
                    );
                }

                $thumb = \B2\Modules\Common\Post::get_post_thumb($post_query->post->ID);
                $arr[] = array(
                    'id'=>$post_query->post->ID,
                    'title'=>get_the_title($post_query->post->ID),
                    'href'=>get_permalink($post_query->post->ID),
                    'cat'=>$cat,
                    'date'=>get_the_date('Y-n-j G:i:s',$post_query->post->ID),
                    '_date'=>Cpost::time_ago(get_the_date('Y-m-d G:i:s',$post_query->post->ID),true),
                    'thumb_full'=>$thumb,
                    'thumb'=>b2_get_thumb(array(
                        'thumb'=>$thumb,
                        'width'=>42,
                        'height'=>42
                    ))
                );

            }
            
        }
        wp_reset_postdata();

        return array(
            'count'=>$post_count,
            'data'=>$arr
        );
    }
}
