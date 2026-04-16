<?php namespace B2\Modules\Common;

/**
 *
 * @author Li Ruchun <lemolee@163.com>
 * @version 2.6.9
 * @since 2021
 */
class CircleRelate{

    public static function get_count($arg){

        $cache_key = self::get_cache_key($arg);

        $cache = wp_cache_get( $cache_key, 'b2_circle_count');
     
        if($cache_key && $cache){
            return (int)filter_var($cache, FILTER_SANITIZE_NUMBER_INT);
        }

        $where = '';

        global $wpdb;

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $where .= $wpdb->prepare(' AND `user_id`=%d',$arg['user_id']);
        }

        if(isset($arg['circle_id']) && $arg['circle_id'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_id`=%d',$arg['circle_id']);
        }

        if(isset($arg['circle_role']) && $arg['circle_role'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_role`=%s',$arg['circle_role']);
        }

        if(isset($arg['circle_key']) && $arg['circle_key'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_key`=%s',$arg['circle_key']);
        }

        if(isset($arg['circle_value']) && $arg['circle_value'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_value`=%s',$arg['circle_value']);
        }

        if(!$where) return 0;

        $where = substr($where,4);

        
        $table_name = $wpdb->prefix . 'b2_circle_related';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
        
        if($cache_key){

            $time = 10 * MINUTE_IN_SECONDS;
            wp_cache_set($cache_key,'i_'.$count,'b2_circle_count',$time);
        }
        
        return (int)$count;
    }

    public static function isset($arg){
        $where = '';

        global $wpdb;

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $where .= $wpdb->prepare(' AND `user_id`=%d',$arg['user_id']);
        }

        if(isset($arg['circle_id']) && $arg['circle_id'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_id`=%d',$arg['circle_id']);
        }

        if(isset($arg['circle_role']) && $arg['circle_role'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_role`=%s',$arg['circle_role']);
        }

        if(isset($arg['circle_key']) && $arg['circle_key'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_key`=%s',$arg['circle_key']);
        }

        if(isset($arg['circle_value']) && $arg['circle_value'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_value`=%s',$arg['circle_value']);
        }

        if(!$where) return 0;

        $where = substr($where,4);

       
        $table_name = $wpdb->prefix . 'b2_circle_related';

        $res = $wpdb->get_var("SELECT 1 FROM $table_name WHERE $where Limit 1");

        return $res ? true : false;
    }

    public static function get_cache_key($arg){

        if(empty($arg)) return;

        $cache_key = '';

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $cache_key .= '_'.$arg['user_id'];
        }

        if(isset($arg['circle_id']) && $arg['circle_id'] !== ''){
            $cache_key .= '_'.$arg['circle_id'];
        }

        if(isset($arg['circle_role']) && $arg['circle_role'] !== ''){
            $cache_key .= '_'.$arg['circle_role'];
        }

        if($cache_key){
            return sanitize_key( $cache_key );
        }

        return false;

    }

    public static function delete_cache($key){
        if(!$key) return;
        wp_cache_delete($key,'b2_circle_count');
        wp_cache_delete($key,'b2_circle_data');
    }

    public static function flash_cache($arg){
       $user_id = isset($arg['user_id']) && $arg['user_id'] !== '' ? '_'.$arg['user_id'] : '';
       $circle_id = isset($arg['circle_id']) && $arg['circle_id'] !== '' ? '_'.$arg['circle_id'] : '';

        $roles = array('_admin','_member','_pending');

        foreach ($roles as $role) {
            self::delete_cache($role);
            self::delete_cache($user_id.$role);
            self::delete_cache($circle_id.$role);
            self::delete_cache($user_id.$circle_id.$role);
        }

       self::delete_cache($user_id);
       self::delete_cache($circle_id);
       self::delete_cache($user_id.$circle_id);
    }

    public static function update_data($new_data,$where = array()){
       
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_circle_related';

        if($new_data['end_date'] == ''){
            $new_data['end_date'] = '0000-00-00 00:00:00';
        }

        $arr = array(
            'id'=>'%d',
            'user_id'=>'%d',
            'circle_id'=>'%d',
            'circle_role'=>'%s',
            'join_date'=>'%s',
            'end_date'=>'%s',
            'circle_key'=>'%s',
            'circle_value'=>'%s'
        );

        $format_new_data = array();
        foreach ($new_data as $k => $v) {
            $format_new_data[] = $arr[$k];
        }

        if(empty($where)){

            if($wpdb->insert($table_name,$new_data,$format_new_data)){

                self::flash_cache($new_data);
                
                return true;
            }
            return false;

        }else{

            $format_where = array();
            foreach ($where as $k => $v) {
                $format_where[] = $arr[$k];
            }
    
            if($wpdb->update( 
                $table_name,
                $new_data,
                $where,
                $format_new_data,
                $format_where
            )){

                self::flash_cache($where);

                return true;
            }
            return false;

        }
    }

    public static function get_data($arg){

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_circle_related';

        $where = '';

        if(isset($arg['id']) && $arg['id'] !== ''){
            $where .= $wpdb->prepare(' AND `id`=%d',$arg['id']);
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $where .= $wpdb->prepare(' AND `user_id`=%d',$arg['user_id']);
        }

        if(isset($arg['circle_id']) && $arg['circle_id'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_id`=%d',$arg['circle_id']);
        }

        if(isset($arg['circle_role']) && $arg['circle_role'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_role`=%s',$arg['circle_role']);
        }

        if(isset($arg['circle_key']) && $arg['circle_key'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_key`=%s',$arg['circle_key']);
        }

        if(isset($arg['circle_value']) && $arg['circle_value'] !== ''){
            $where .= $wpdb->prepare(' AND `circle_value`=%s',$arg['circle_value']);
        }

        if(isset($arg['count'])){
            $arg['count'] = (int)$arg['count'];
            if($arg['count'] > 50) $arg['count'] = 1;
            $where .= ' ORDER BY id DESC LIMIT '.$arg['count'];
        }else{
            $where .= ' ORDER BY id DESC LIMIT 1';
        }

        if(!$where) return array();

        $cache_key = self::get_cache_key($arg);

        $where = substr($where,4);

        $cache = wp_cache_get( $cache_key, 'b2_circle_data');

        if($cache_key && $cache){
            return $cache;
        }

        $res = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE $where"
        ,ARRAY_A);

        if($cache_key){
            $time = 10 * MINUTE_IN_SECONDS;
            wp_cache_set($cache_key,$res,'b2_circle_data',$time);
        }

        return $res;
    }

    public static function delete_data($arg){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_circle_related';

        $arr = array();

        if(isset($arg['id'])){
            $arr['id'] = (int)$arg['id'];
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $arr['user_id'] = (int)$arg['user_id'];
        }

        if(isset($arg['circle_id']) && $arg['circle_id'] !== ''){
            $arr['circle_id'] = (int)$arg['circle_id'];
        }

        self::flash_cache($arr);

        return $wpdb->delete( $table_name, $arr );
    }
}
