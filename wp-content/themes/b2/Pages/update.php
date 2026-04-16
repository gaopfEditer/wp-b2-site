<?php
use B2\Modules\Common\User;
//get_header();

if(!current_user_can('administrator')) wp_die('您无权访问此页');

//用户等级设置项数据更新
$lv_arg = array();
$vip_arg = array();
$lvs = get_option('zrz_lv_setting');
if(!empty($lvs)){
    foreach ($lvs as $k => $v) {
        if(strpos($k,'lv') !== false){
            $lv_arg[] = array(
                'lv'=>$k,
                'name'=>$v['name'],
                'credit'=>$v['credit'],
                'user_role'=>$v['capabilities']
            );
        }
        if(strpos($k,'vip') !== false){
            $vip_arg[] = array(
                'lv'=>($k == 'vip' ? 'vip0' : $k),
                'name'=>$v['name'],
                'price'=>$v['price'],
                'time'=>$v['time'],
                'allow_read'=>isset($v['allow_read']) ? $v['allow_read'] : 0,
                'allow_down'=>isset($v['allow_down']) ? $v['allow_down'] : 0,
                'user_role'=>$v['capabilities']
            );
        }
    }
    
    $normal_settings = get_option('b2_normal_user');
    $normal_settings = is_array($normal_settings) ? $normal_settings : array();
    $normal_settings['user_lv_group'] = $lv_arg;
    $normal_settings['user_vip_group'] = $vip_arg;
    update_option('b2_normal_user',$normal_settings);
}

//---------------------------------------------------------------------//

//vip过期时间格式化

$users = get_users(array('number'=>-1));
foreach($users as $user){

    //重建时间
    $time = get_user_meta($user->ID,'zrz_vip_time',true);
    if(is_array($time)){
        $time['start'] = wp_strtotime($time['start']);
        $time['end'] = wp_strtotime($time['end']) ? wp_strtotime($time['end']) : 0;
        update_user_meta($user->ID,'zrz_vip_time',$time);
    }


    //更新用户等级
    $vip = get_user_meta($user->ID,'zrz_lv',true);
    if($vip && strpos($vip,'vip') !== false){
        update_user_meta($user->ID,'zrz_vip',$vip === 'vip' ? 'vip0' : $vip);
        User::rebuild_user_lv($user->ID);
    }
 
}


//---------------------------------------------------------------------//

//专题和分类页面缩略图更新
$terms = get_terms( array(
    'taxonomy' => array('category','collection'),
    'hide_empty' => false,
) );

$place = get_option('zrz_media_setting');

if(isset($place['media_place']) && $place['media_place'] == 'aliyun'){
    $url = $place['aliyun']['host'].'/'.$place['aliyun']['path'].'/';
}else{
    $upload_dir = apply_filters('b2_upload_path_arg',wp_upload_dir());

    $url = $upload_dir['baseurl'].'/';
}

foreach($terms as $term){
    $img = get_term_meta($term->term_id,'cat_img',true);
    if(is_array($img)){
        update_term_meta($term->term_id,'b2_tax_img',$url.$img['image']);
    }
}

//---------------------------------------------------------------------//

// //更新文章数据
$args = array(
    'post_status'=>'publish',
    'post_type'=>'post',
    'posts_per_page'=>-1
);

// The Query
$the_query = new WP_Query( $args );

if ( $the_query->have_posts() ) {

	while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $id = get_the_id();

        //更新文章权限到新版
        $cap = get_post_meta($id,'capabilities',true);

        if(!empty($cap)){
            if(isset($cap['key'])){
                if(isset($cap['val'])){
                    if($cap['key'] == 'lv'){
                        $cap['key'] = 'roles';
                    }elseif($cap['key'] == 'rmb'){
                        $cap['key'] = 'money';
                    }elseif($cap['key'] == 'default'){
                        $cap['key'] = 'none';
                    }
                    update_post_meta($id,'b2_post_reading_role',$cap['key']);
                    update_post_meta($id,'b2_post_'.$cap['key'],$cap['val']);
                }
            }
            
        }
	}

	wp_reset_postdata();
}


//更新商品信息
$args = array(
    'post_status'=>'publish',
    'post_type'=>'shop',
    'posts_per_page'=>-1
);

// The Query
$the_query = new WP_Query( $args );

if ( $the_query->have_posts() ) {

	while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $id = get_the_id();

        //更新文章权限到新版
        $str = '';
        $attr = get_post_meta($id,'zrz_shop_attributes',true);
        if(!empty($attr)){
            foreach ($attr as $k => $v) {
                $str .= $v['title'].'|'.$v['track'].PHP_EOL;
            }
        }

        $new_attr = get_post_meta($id,'shop_attr',true);

        if($str && !$new_attr){
            update_post_meta($id,'shop_attr',$str);
        }
	}

	wp_reset_postdata();
}

echo 'success';
exit;

//get_footer();