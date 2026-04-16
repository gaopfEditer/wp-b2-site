<?php namespace B2\Modules\Common;
use B2\Modules\Common\Post;
class Coupon{
    public function init(){

    }

    public static function get_coupons($ids,$count = 0){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_coupon';
        
        if($ids === 'all'){
            $res = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT * FROM $table_name
                ")
            ,ARRAY_A);
        }else{
            if(!is_array($ids)) return array('error'=>__('查询错误','b2'));
            $count = $count ? $count : count($ids);
            $ids = implode("','",$ids);
            $res = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT * FROM $table_name
                    WHERE id IN ('".$ids."')
                    LIMIT %d
                ",$count)
            ,ARRAY_A);
        }

        $data = array();

        if($res && !empty($res)){

            $user_id = b2_get_current_user_id();
            $user_lv = get_user_meta($user_id,'zrz_lv',true);
            $user_vip = get_user_meta($user_id,'zrz_vip',true);
            $coupons = get_user_meta($user_id,'b2_coupons',true);

            foreach ($res as $k => $v) {
                $lvs = maybe_unserialize($v['roles']);
                $lvs = $lvs ? $lvs : array();

                $lv_arr = array();
                if(!empty($lvs)){
                    foreach ($lvs as $_v) {
                        $_v = trim($_v, " \t\n\r\0\x0B\xC2\xA0");
                        if($_v){
                            $lv_arr[] = User::get_lv_icon($_v);
                        }
                    }
                }

                $cats = maybe_unserialize($v['cats']);
                $cat_arr = array();
                if(!empty($cats)){
                    foreach ($cats as $_v) {
                        $_v = trim($_v, " \t\n\r\0\x0B\xC2\xA0");
                        if($_v){
                            $term = get_term_by( 'slug', $_v, 'shoptype');
                            if($term){
                                $cat_arr[] = array(
                                    'name'=>$term->name,
                                    'link'=>get_term_link($term->term_id)
                                );
                            }
                        }
                    }
                }

                $posts = maybe_unserialize($v['products']);
                $post_arr = array();
                if(!empty($posts)){
                    foreach ($posts as $_v) {
                        $_v = trim($_v, " \t\n\r\0\x0B\xC2\xA0");
                        if($_v){
                            $post_arr[] = array(
                                'name'=>get_the_title($_v),
                                'link'=>get_permalink($_v),
                                'image'=>Post::get_post_thumb($_v)
                            ); 
                        }
                    }
                }

                $expired = false;
                $expired_date = '';
                //检查是否过期
                if((int)$v['expiration_date'] !== 0 && isset($coupons[$v['id']])){
                    
                    $expired_date = wp_date("Y-m-d H:i:s", wp_strtotime($coupons[$v['id']]." +".$v['expiration_date']." day"));
                    if(current_time('mysql') > $expired_date){
                        $expired = true;
                    }
                }

                $data[$v['id']] = array(
                    'id'=>$v['id'],
                    'money'=>$v['money'],
                    'receive_date'=>array(
                        'date'=>$v['receive_date'],
                        'expired'=>$v['receive_date'] > current_time('mysql') || (int)$v['receive_date'] === 0 ? false : true
                    ),
                    'expiration_date'=>array(
                        'date'=>$v['expiration_date'],
                        'expired'=>$expired,
                        'expired_date'=>$expired_date
                    ),
                    'roles'=>array(
                        'lvs'=>$lv_arr,
                        'can'=>empty($lvs) || ($user_lv && in_array($user_lv,$lvs)) || ($user_vip && in_array($user_vip,$lvs)) ? true : false
                    ),
                    'cats'=>$cat_arr,
                    'products'=>$post_arr
                );
            }

        }

        return $data;
    }

    //领取优惠劵
    public static function coupon_receive($id){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        //获取优惠劵信息
        $info = self::get_coupons(array($id),1);

        if(!isset($info[$id])) return array('error'=>__('优惠劵未领取或已使用','b2'));

        if($info[$id]['receive_date']['expired']){
            return array('error'=>__('已经过了领取时间，无法领取','b2'));
        }

        if($info[$id]['roles']['can'] === false) return array('error'=>__('您没有权限领取和使用这个优惠劵','b2'));

        $my_coupon = get_user_meta($user_id,'b2_coupons',true);
        $my_coupon = is_array($my_coupon) ? $my_coupon : array();

        if(isset($my_coupon[$id])) return array('error'=>__('您已经领取过这个优惠劵','b2'));

        $my_coupon[$id] = current_time('mysql');

        update_user_meta($user_id,'b2_coupons',$my_coupon);

        return true;
    }

    //获取我的优惠劵
    public static function get_my_coupons(){
        $user_id = b2_get_current_user_id();

        $data = get_user_meta($user_id,'b2_coupons',true);
        $data = is_array($data) ? $data : array();

        $ids = array();
        foreach ($data as $k => $v) {
            $ids[] = $k;
        }

        $data = self::get_coupons($ids,0);

        return array(
            'data'=>$data,
            'count'=>count($data)
        );
    }

    //删除我的优惠劵
    public static function delete_my_coupon($id){
        $user_id = b2_get_current_user_id();

        $data = get_user_meta($user_id,'b2_coupons',true);
        $data = is_array($data) ? $data : array();

        if(isset($data[$id])){
            unset($data[$id]);
            update_user_meta($user_id,'b2_coupons',$data);
        }

        return true;
    }

    //检查优惠劵是否可以使用
    public static function check_coupon($post_ids){
        
        //用户
        $user_id = b2_get_current_user_id();

        if(!$user_id) return [];

        $user_coupons = get_user_meta($user_id,'b2_coupons',true);
        $user_coupons = is_array($user_coupons) ? $user_coupons : array();

        if(empty($user_coupons)) return [];
        
        $allow_coupons = array();

        foreach ($user_coupons as $k => $v) {
            $i = [];
            foreach ($post_ids as $_k => $_v) {

                if(self::coupon_can_use_by_post($_v,$k)){
                    //检查使用权限
                    $info = self::get_coupons(array($k),1);
                    
                    if(!$info[$k]['expiration_date']['expired'] && $info[$k]['roles']['can']){
                        $i[] = $_v;
                        $info[$k]['ids'] = $i;
                        $info[$k]['show'] = true;
                        $allow_coupons[$k] = $info[$k];
                    }
                }
            }
        }

        return $allow_coupons;

    }

    //检查某个商品是否可以使用优惠劵
    public static function coupon_can_use_by_post($post_id,$coupon_id){

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_coupon';

        $res = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE id=%d
            ",$coupon_id)
        ,ARRAY_A);
        
        $allow_p = false;
        $allow_c = false;

        //检查文章ID
        if(!empty($res)){
            $products = maybe_unserialize($res[0]['products']);
            
            if($products === '' || !$products){
                $allow_p = true;
            }else{
                if(in_array($post_id,$products)){
                    $allow_p = true;
                }else{
                    $allow_p = false;
                }
            }

            $cats = maybe_unserialize($res[0]['cats']);
     
            if($cats === '' || !$cats){
                $allow_c = true;
            }else{
                $terms = get_the_terms($post_id,'shoptype');
                if($terms){
                    $terms_id = array();
                    foreach ($terms as $_k => $_v) {
                        $terms_id[] = $_v->slug;
                    }
    
                    $def = array_intersect($terms_id,$cats);
                 
                    if($def){
                        $allow_c = true;
                    }else{
                        $allow_c = false;
                    }
                }
            }

            if($allow_c && $allow_p) return true;
            
        }

        return false;
    }

    //优惠劵使用完毕，删掉
    public static function coupon_used($user_id,$coupon_id){
        $data = get_user_meta($user_id,'b2_coupons',true);
        $data = is_array($data) ? $data : array();

        if(isset($data[$coupon_id])){
            unset($data[$coupon_id]);
        }

        update_user_meta($user_id,'b2_coupon',$data);

        return true;
    }
    
}