<?php namespace B2\Modules\Common;

class PostRelationships{


    public static function get_count($arg){

        $where = '';
        

        if(isset($arg['type'])){
            $cache_key = self::get_cache_key($arg);
   
            $cache = wp_cache_get( $cache_key, 'b2_relate_count_'.$arg['type'] );
         
            if($cache){
                return (int)filter_var($cache, FILTER_SANITIZE_NUMBER_INT);;
            }
        }

        global $wpdb;

        if(isset($arg['type']) && $arg['type']){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',(int)$arg['post_id']);
        }

        if(isset($arg['comment_id']) && $arg['comment_id'] !== ''){
            $where .= $wpdb->prepare(' AND `comment_id`=%d',(int)$arg['comment_id']);
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $where .= $wpdb->prepare(' AND `user_id`=%d',(int)$arg['user_id']);
        }

        if(isset($arg['k']) && $arg['k'] !== ''){
            $where .= $wpdb->prepare(' AND `k`=%s',$arg['k']);
        }

        if(isset($arg['v']) && $arg['v'] !== ''){
            $where .= $wpdb->prepare(' AND `v`=%s',$arg['v']);
        }

        if(!$where) return 0;

        $where = substr($where,4);

        
        $table_name = $wpdb->prefix . 'b2_post_relationships';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");

        if(isset($arg['type'])){
            wp_cache_set($cache_key,'i_'.$count,'b2_relate_count_'.$arg['type'],10 * MINUTE_IN_SECONDS);
        }

        return (int)$count;
    }

    public static function isset($arg){
        $where = '';

        global $wpdb;

        if(isset($arg['type']) && $arg['type']){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',(int)$arg['post_id']);
        }

        if(isset($arg['comment_id']) && $arg['comment_id'] !== ''){
            $where .= $wpdb->prepare(' AND `comment_id`=%d',(int)$arg['comment_id']);
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $where .= $wpdb->prepare(' AND `user_id`=%d',(int)$arg['user_id']);
        }

        if(isset($arg['k']) && $arg['k'] !== ''){
            $where .= $wpdb->prepare(' AND `k`=%s',$arg['k']);
        }

        if(isset($arg['v']) && $arg['v'] !== ''){
            $where .= $wpdb->prepare(' AND `v`=%s',$arg['v']);
        }

        if(!$where) return 0;

        $where = substr($where,4);

        
        $table_name = $wpdb->prefix . 'b2_post_relationships';

        $res = $wpdb->get_row("SELECT * FROM $table_name WHERE $where");

        return $res ? true : false;
    }

    public static function get_cache_key($arg){

        if(empty($arg)) return;

        $cache_key = '';

        if(isset($arg['post_id']) && !is_object($arg['post_id']) && $arg['post_id'] !== ''){
            $cache_key .= '_'.(int)$arg['post_id'];
        }

        if(isset($arg['comment_id']) && $arg['comment_id'] !== ''){
            $cache_key .= '_'.(int)$arg['comment_id'];
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $cache_key .= '_'.(int)$arg['user_id'];
        }

        return sanitize_key( $cache_key );

    }

    public static function delete_cache($key,$type){
        if(!$key) return;
        wp_cache_delete($key,'b2_relate_count_'.$type);
        wp_cache_delete($key,'b2_relate_data_'.$type);
    }

    public static function flash_cache($arg){
        $user_id = isset($arg['user_id']) && $arg['user_id'] !== '' ? '_'.$arg['user_id'] : '';
        $post_id = isset($arg['post_id']) && $arg['post_id'] !== '' ? '_'.$arg['post_id'] : '';
        $comment_id = isset($arg['comment_id']) && $arg['comment_id'] !== '' ? '_'.$arg['comment_id'] : '';
        $type = $arg['type'];

        self::delete_cache($user_id,$type);
        self::delete_cache($post_id,$type);
        self::delete_cache($comment_id,$type);

        self::delete_cache($user_id.$post_id,$type);
        self::delete_cache($user_id.$comment_id,$type);
        self::delete_cache($post_id.$comment_id,$type);
        self::delete_cache($user_id.$post_id.$comment_id,$type);
     }

    public static function update_data($new_data,$where = array()){
       
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_post_relationships';

        $arr = array(
            'type'=>'%s',
            'user_id'=>'%d',
            'post_id'=>'%d',
            'comment_id'=>'%d',
            'k'=>'%s',
            'v'=>'%s'
        );

        $format_new_data = array();
        foreach ($new_data as $k => $v) {
            $format_new_data[] = $arr[$k];
        }

        if(empty($where)){
            
            if($wpdb->insert($table_name,$new_data,$format_new_data)){
                if(isset($new_data['type'])){
                    self::flash_cache($new_data);
                }
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
                if(isset($where['type'])){
                    self::flash_cache($where);
                }

                return true;
            }
            return false;

        }
    }

    public static function get_data($arg){

        if(isset($arg['type'])){
            $cache_key = self::get_cache_key($arg);

            $cache = wp_cache_get( $cache_key, 'b2_relate_data_'.$arg['type']);

            if($cache){
                return $cache;
            }
        }

        $where = '';

        global $wpdb;

        if(isset($arg['type']) && $arg['type']){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',(int)$arg['post_id']);
        }

        if(isset($arg['comment_id']) && $arg['comment_id'] !== ''){
            $where .= $wpdb->prepare(' AND `comment_id`=%d',(int)$arg['comment_id']);
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $where .= $wpdb->prepare(' AND `user_id`=%d',(int)$arg['user_id']);
        }

        if(isset($arg['k']) && $arg['k'] !== ''){
            $where .= $wpdb->prepare(' AND `k`=%s',$arg['k']);
        }

        if(isset($arg['v']) && $arg['v'] !== ''){
            $where .= $wpdb->prepare(' AND `v`=%s',$arg['v']);
        }

        if(isset($arg['count'])){
            $arg['count'] = (int)$arg['count'];
            if($arg['count'] > 10) $arg['count'] = 1;
            $where .= ' ORDER BY id DESC LIMIT '.$arg['count'];
        }else{
            $where .= ' ORDER BY id DESC LIMIT 1';
        }

        if(!$where) return array();

        $where = substr($where,4);

        
        $table_name = $wpdb->prefix . 'b2_post_relationships';

        $res = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE $where "
        ,ARRAY_A);

        if(isset($arg['type'])){
            wp_cache_set($cache_key,$res,'b2_relate_data_'.$arg['type'],10 * MINUTE_IN_SECONDS);
        }

        return $res;
    }

    public static function delete_data($arg){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_post_relationships';

        $arr = array();

        if(isset($arg['id'])){
            $arr['id'] = (int)$arg['id'];
        }

        if(isset($arg['type']) && $arg['type'] !== ''){
            $arr['type'] = $arg['type'];
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $arr['post_id'] = (int)$arg['post_id'];
        }

        if(isset($arg['comment_id']) && $arg['comment_id'] !== ''){
            $arr['comment_id'] = (int)$arg['comment_id'];
        }

        if(isset($arg['user_id']) && $arg['user_id'] !== ''){
            $arr['user_id'] = (int)$arg['user_id'];
        }

        if(isset($arg['k']) && $arg['v'] !== ''){
            $arr['k'] = $arg['k'];
        }

        if(isset($arg['k']) && $arg['k'] !== ''){
            $arr['v'] = $arg['v'];
        }

        if(isset($arr['type'])){
            self::flash_cache($arr);
        }

        return $wpdb->delete( $table_name, $arr );
    }
}
