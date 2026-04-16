<?php namespace B2\Modules\Common;

use B2\Modules\Templates\Modules\Sliders;


class Announcement{ 

    public static function get_latest_announcement($count){

        if($count > 10) return [];

        // $data = get_option('b2_latest_announcement');

        // if($data != '') return $data;

        $args = array(
            'post_type'=>'announcement',
            'post_status' => 'publish',
            'posts_per_page'=>$count,
            'order'=>'DESC',
            'orderby'=>'ID',
            'no_found_rows'=>true
        );

        $the_query = new \WP_Query( $args );

        $data = array();
        $current_user_id = b2_get_current_user_id();

        if ( $the_query->have_posts() ) {
            $k = 0;
            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                $post_id = $the_query->post->ID;

                $time = get_the_date('Y-n-j G:i:s',$post_id);

                $show = false;

                $type = (int)get_post_meta($post_id,'b2_gg_show',true);//公告显示条件

                $days = get_post_meta($post_id,'b2_gg_days',true);//公告关闭以后再弹出间隔的天数
                $days = $days ? $days : 1;

                $over = get_post_meta($post_id,'b2_gg_over',true);//公告过期时间
                $over = $over ? $over : 7;
               
                if($type === 0){
                    $show = true;
                }elseif($type === 1 && $current_user_id){
                    $show = true;
                }elseif($type === 2 && !$current_user_id){
                    $show = true;
                }elseif($type === 3){
                    $show = false;
                }

                if($show == true){
                    $c_time =  wp_strtotime(current_time( 'mysql' ));
                    $p_time = get_post_timestamp($post_id);
                    //如果过期，不显示
                    if($c_time > ($p_time + $over*86400)){
                        $show = false;
                    }else{
                        $gg = b2_getcookie('gg_info');
                        if($gg){
                            if(($gg + $days*86400) > $c_time){
                                $show = false;
                            }else{
                                b2_deletecookie('gg_info');
                            }
                        }
                    }
                }

                $data[] = array(
                    'title' => get_the_title($post_id),
                    'href' => get_permalink($post_id),
                    'thumb' => b2_get_thumb(array('thumb'=>Post::get_post_thumb($post_id),'width'=>336,'height'=>110)),
                    'date' => $time,
                    'desc' => b2_get_excerpt($post_id),
                    'timestamp' => wp_strtotime($time),
                    'days' => $days,
                    'show' => $show
                );         
            }
            wp_reset_postdata();
        }
       

        // update_option('b2_latest_announcement',$data);

        return $data;
    }

    public static function get_announcement_list($count){

        $count = $count > 30 ? 6 : $count;        
        $args = array(
            'post_type'=>'announcement',
            'post_status' => 'publish',
            'posts_per_page'=>$count,
            'order'=>'DESC',
            'orderby'=>'ID',
            'no_found_rows'=>true
        );

        $the_query = new \WP_Query( $args );

        $data = array();

        if ( $the_query->have_posts() ) {

            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                $post_id = $the_query->post->ID;

                $data[] = array(
                    'title'=>get_the_title($post_id),
                    'href'=>get_permalink($post_id)
                );
                          
            }
            
        }
        wp_reset_postdata();

        return $data;
    }
}