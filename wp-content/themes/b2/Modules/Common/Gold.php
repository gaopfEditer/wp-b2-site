<?php namespace B2\Modules\Common;
use B2\Modules\Common\User;
use B2\Modules\Common\Credit;

class Gold{

    public static function get_count($arg){

        global $wpdb;
        $where = '';

        if(isset($arg['to']) && $arg['to']){
            $where .= $wpdb->prepare(' AND `to`="%d"',$arg['to']);
        }

        if(isset($arg['gold_type']) && $arg['gold_type']){
            $where .= $wpdb->prepare(' AND `gold_type`="%d"',$arg['gold_type']);
        }

        if(isset($arg['type']) && $arg['type']){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',$arg['post_id']);
        }

        if(isset($arg['read']) && $arg['read'] !== ''){
            $where .= $wpdb->prepare(' AND `read`=%d',$arg['read']);
        }

        if(!$where) return 0;

        $where = substr($where,4);

        
        $table_name = $wpdb->prefix . 'b2_gold';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");

        return apply_filters('b2_gold_get_count',(int)$count,$arg);;
    }

    public static function update_data($new_data){

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_gold';

        if(!isset($new_data['gold_type'])) return false;

        if(!isset($new_data['total'])){
            if($new_data['gold_type'] === 1){
                $new_data['total'] = User::money_change($new_data['to'],$new_data['no']);
    
                $new_data['no'] = $new_data['no'];
                $new_data['total'] = $new_data['total'];
            }else{
                $new_data['total'] = Credit::credit_change($new_data['to'],$new_data['no']);
            }

            if($new_data['total'] < 0) return false;
        }

        if($new_data['gold_type'] === 1){
            $new_data['no'] = $new_data['no'] * 100;
            $new_data['total'] = $new_data['total'] * 100;
        }

        if(!isset($new_data['count'])){
            $new_data['count'] = 1;
        }

        if(!isset($new_data['read'])){
            $new_data['read'] = 0;
        }

        $where = [];

        $gold = false;

        if(isset($new_data['to']) && isset($new_data['post_id']) && isset($new_data['type']) && isset($new_data['old_row'])){

            $where = [
                'gold_type'=>$new_data['gold_type'],
                'to'=>$new_data['to'],
                'post_id'=>$new_data['post_id'],
                'type'=>$new_data['type'],
                'read'=>0
            ];

            $gold = self::get_data($where);

            if(count($gold['data']) > 0){
                $new_data['count'] = $gold['data'][0]['count'] + 1;
            }else{
                $gold = false;
            }
        }

        unset($new_data['old_row']);

        if(isset($new_data['to'])){
            delete_user_meta($new_data['to'],'b2_user_unread_gold');
        }

        $arr = array(
            'id'=>'%d',
            'date'=>'%s',
            'from'=>'%s',
            'to'=>'%d',
            'gold_type'=>'%d',
            'no'=>'%d',
            'total'=>'%d',
            'type'=>'%s',
            'type_text'=>'%s',
            'post_id'=>'%d',
            'count'=>'%d',
            'read'=>'%d',
            'msg'=>'%s',
            'key'=>'%s',
            'value'=>'%s'
        );

        $format_new_data = array();
        foreach ($new_data as $k => $v) {
            if(isset($arr[$k])){
                $format_new_data[] = $arr[$k];
            }
        }

        if(!$gold){

            if($wpdb->insert($table_name,$new_data,$format_new_data)){
                do_action('b2_gold_update_data_insert',$new_data);
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
                do_action('b2_gold_update_data_update',$new_data);
                return true;
            }
            return false;

        }
    }

    public static function get_gold_list($arg){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return ['error'=>__('请先登录','b2')];

        if(!user_can($user_id, 'administrator' )){
            $arg['to'] = $user_id;
        }elseif(!isset($arg['to']) || !$arg['to']){
            $arg['to'] = $user_id;
        }

        $arg['pre_count'] = isset($arg['count']) ? $arg['count'] : 20;

        unset($arg['count']);

        $arg['show_pages'] = 1;

        $list = self::get_data($arg);

        // if(empty($list['data'])) return [];

        $list['data'] = self::get_gold_data_map($list['data']);

        $list['unread'] = self::get_user_unread_msg_count($arg['to']);

        if($list['unread'] > 0 && isset($arg['read_clean']) && $user_id == $arg['to']){
            self::msg_unread_clean($user_id);
        }

        return apply_filters('b2_gold_get_msg_list',$list,$arg);
    }

    public static function get_gold_data_map($list){
        foreach ($list as $k => $v) {
            if($list[$k]['post_id']){
                if($list[$k]['type'] == 'comment_down' || $list[$k]['type'] == 'comment_up'){
                    $comment = get_comment($list[$k]['post_id']);
                    if(isset($comment->comment_post_ID)){
                        $list[$k]['post'] = [
                            'title'=>get_the_title($comment->comment_post_ID),
                            'link'=>get_permalink($comment->comment_post_ID),
                            'post_type'=>get_post_type($comment->comment_post_ID)
                        ];
                    }
                }elseif($list[$k]['type'] == 'author_circle_join'){
                    $term = get_term($list[$k]['post_id'],'circle_tags');
                    $circle_slug = b2_get_option('normal_custom','custom_circle_link');
                    $list[$k]['post'] = [
                        'title'=>$term->name,
                        'link'=>B2_HOME_URI.'/'.$circle_slug.'/'.$term->slug,
                        'post_type'=>'circle_tags'
                    ];
                }else{
                    $list[$k]['post'] = [
                        'title'=>get_the_title($list[$k]['post_id']),
                        'link'=>get_permalink($list[$k]['post_id']),
                        'post_type'=>get_post_type($list[$k]['post_id'])
                    ];
                }

                // $list['data'][$k]['post_type'] = get_post_type($list['data'][$k]['post_id']);
            }

            if($v['gold_type'] == 1){
                $list[$k]['no'] = bcdiv($list[$k]['no'],100,2);
                $list[$k]['total'] = bcdiv($list[$k]['total'],100,2);
            }

            if($v['value']){
                if(strpos($v['value'],'/') !== false){
                    $arg = explode('/',$v['value']);
                    $list[$k]['value'] = [
                        'type'=>isset($arg[1]) ? $arg[1] : '',
                        'money'=>isset($arg[1]) ? $arg[1] : '',
                        'ratio'=>isset($arg[2]) ? $arg[2] : ''
                    ];
                }
            }

            if($v['from']){
                
                if(is_numeric($v['from']) && (int)$v['from'] !== 0){
                    $list[$k]['from'] = User::get_user_normal_data($v['from']);
                }else{
                    $list[$k]['from'] = __('游客','b2');
                }
                
            }

            $list[$k]['date'] = b2_timeago($list[$k]['date'],true);

        }

        return $list;
    }

    public static function msg_unread_clean($user_id){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_gold';

        $wpdb->update(
            $table_name,
            array( 
                'read' => 1,
            ), 
            array( 'read' => 0,'to'=> $user_id),
            array( 
                '%d'
            ), 
            array( '%d','%d' ) 
        );

        delete_user_meta($user_id,'b2_user_unread_gold');
    }

    public static function get_user_unread_msg_count($user_id){

        $unread = get_user_meta($user_id,'b2_user_unread_gold',true);
        if($unread !== '') return $unread;

        $count = self::get_count(['to'=>$user_id,'read'=>0]);

        update_user_meta($user_id,'b2_user_unread_gold',$count);

        return $count;
        
    }

    public static function get_data($arg){

        $where = '';

        global $wpdb;

        if(isset($arg['id']) && $arg['id']){
            $where .= $wpdb->prepare(' AND `id`="%d"',$arg['id']);
        }

        if(isset($arg['date']) && $arg['date'] !== ''){
            $where .= $wpdb->prepare(' AND `date`=%s',$arg['date']);
        }

        // if(isset($arg['from']) && $arg['from'] !== ''){
        //     $where .= $wpdb->prepare(' AND `from`=%s',$arg['from']);
        // }

        if(isset($arg['to']) && $arg['to'] !== ''){
            $where .= $wpdb->prepare(' AND `to`=%d',$arg['to']);
        }

        if(isset($arg['gold_type']) && $arg['gold_type'] !== ''){
            $where .= $wpdb->prepare(' AND `gold_type`=%d',$arg['gold_type']);
        }

        if(isset($arg['no']) && $arg['no'] !== ''){
            $where .= $wpdb->prepare(' AND `no`=%d',$arg['no']);
        }

        if(isset($arg['total']) && $arg['total'] !== ''){
            $where .= $wpdb->prepare(' AND `total`=%d',$arg['total']);
        }

        if(isset($arg['type']) && $arg['type'] !== ''){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['type_text']) && $arg['type_text'] !== ''){
            $where .= $wpdb->prepare(' AND `type_text`=%s',$arg['type_text']);
        }

        if(isset($arg['key']) && $arg['key'] !== ''){
            $where .= $wpdb->prepare(' AND `key`=%s',$arg['key']);
        }

        if(isset($arg['value']) && $arg['value'] !== ''){
            $where .= $wpdb->prepare(' AND `value`=%s',$arg['value']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',$arg['post_id']);
        }

        if(isset($arg['read']) && $arg['read'] !== ''){
            $where .= $wpdb->prepare(' AND `read`=%d',$arg['read']);
        }

        if(isset($arg['pre_count'])){

            $offset = '';

            if(isset($arg['paged']) && $arg['paged']){
                $offset = (((int)$arg['paged'] - 1) * $arg['pre_count']).',';
            }

            $arg['pre_count'] = (int)$arg['pre_count'];
            if($arg['pre_count'] > 50) $arg['pre_count'] = 1;
            $where .= ' ORDER BY date DESC LIMIT '.$offset.''.$arg['pre_count'];
        }else{
            $where .= ' ORDER BY date DESC LIMIT 1';
        }

        $pages = 0;

        if(!$where) return [
            'pages'=>$pages,
            'data'=>[]
        ];

        $where = substr($where,4);

        $table_name = $wpdb->prefix . 'b2_gold';

        $res = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE $where "
        ,ARRAY_A);

        if(isset($arg['show_pages']) && $arg['show_pages'] && isset($arg['pre_count'])){

            $index = strpos($where," ORDER BY");
            $where = substr_replace($where,"",$index);

            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");

            $pages = ceil($count/$arg['pre_count']);
        }

        return [
            'pages'=>$pages,
            'data'=>$res
        ];

    }

}