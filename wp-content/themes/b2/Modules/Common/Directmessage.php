<?php namespace B2\Modules\Common;

class Directmessage{

    //获得私信用户列表
    public static function get_user_directmessage_list($paged){

        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));

        $number = 10;
        $offset = ($paged-1)*$number;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $from = "(SELECT * FROM $table_name WHERE `from`=%d OR `to`=%d)";

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $from as b GROUP BY b.mark ORDER BY `date` DESC,`status` ASC LIMIT $offset,$number",$current_user_id,$current_user_id),
            ARRAY_A
        );

        $data = array();
        if($res && !empty($res)){
            foreach ($res as $k => $v) {
                if($v['from'] == $current_user_id){
                    $type = 'from';
                }else{
                    $type = 'to';
                }
                $data[] = array(
                    'id'=>$v['id'],
                    'from'=>User::get_user_public_data($v['from'],true),
                    'to'=>User::get_user_public_data($v['to'],true),
                    'date'=>b2_timeago($v['date']),
                    '_date'=>$v['date'],
                    'content'=>wpautop($v['content']),
                    'type'=>$type,
                    'status'=>$v['status']
                );
            }
        }

        $count = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) FROM $from as b GROUP BY b.mark",$current_user_id,$current_user_id));
        $count = count($count);

        $data = apply_filters('b2_dmsg_list', array(
            'count'=>ceil($count/$number),
            'data'=>$data
        ));

        return $data;
    }

    public static function get_dmsg_unread_count($user_id = 0){

        if(!$user_id){
            $user_id = b2_get_current_user_id();
        }
        
        $count = get_user_meta($user_id,'b2_dmsg_unread',true);
        
        if($count != '') return $count;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';
        $from =  $wpdb->prepare("(SELECT * FROM $table_name WHERE `status`=0 AND `to`= %d LIMIT 100)",$user_id);
        
        // return $from;
        // '$table_name WHERE `status`=0 AND `to`=%d GROUP BY `mark`';
        $count = $wpdb->get_results("SELECT COUNT(id) FROM $from as b GROUP BY b.mark");

        update_user_meta($user_id,'b2_dmsg_unread',count($count));

        return count($count);
    }

    public static function send_directmessage($user_id,$content){

        if((int)$user_id == 0){
            return array('error'=>__('收件人不可为空','b2'));
        }

        $current_user_id = b2_get_current_user_id();

        //检查私信发送权限
        $role = User::check_user_role($current_user_id,'message');
        if(!$role) return array('error'=>__('您没有发送私信的权限','b2'));

        if(!$current_user_id) return array('error'=>__('请先登录','b2'));

        //检查3小时内发布总数
        $post_count_3 = User::check_post($current_user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        $public_count = apply_filters('b2_check_repo_before', $current_user_id);
        if(isset($public_count['error'])) return $public_count;

        $censor = apply_filters('b2_text_censor', $content);
        if(isset($censor['error'])) return $censor;
        
        if((int)$current_user_id === (int)$user_id){
            return array('error'=>__('不能给自己发私信','b2'));
        }

        $content = str_replace(array('{{','}}'),'',$content);

        $content = sanitize_textarea_field($content);

        if(b2getStrLen($content) > 300) return array('error'=>__('最多只能发送300个字','b2'));

        if($content == ''){
            return array('error'=>__('消息不可为空','b2'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $mark = (int)$user_id.'+'.$current_user_id;

        if($user_id > $current_user_id){
            $mark = $current_user_id.'+'.(int)$user_id;
        }

        $msg_data = array(
            'mark'=>$mark,
            'from'=> (int)$current_user_id,
            'to'=> (int)$user_id,
            'date'=> current_time('mysql'),
            'status'=> 0,
            'content'=> $content,
        );

        $res = $wpdb->insert($table_name,$msg_data,array( '%s', '%d','%d','%s','%s','%s' ));

        delete_user_meta($user_id,'b2_dmsg_unread');

        User::save_check_post_count($current_user_id);
        apply_filters('b2_check_repo_after', $current_user_id,$public_count);

        //积分记录
        // Message::add_message(array(
        //     'user_id'=>$user_id,
        //     'msg_type'=>12,
        //     'msg_read'=>0,
        //     'msg_date'=>current_time('mysql'),
        //     'msg_users'=>$current_user_id,
        //     'msg_credit'=>0,
        //     'msg_credit_total'=>0,
        //     'msg_key'=>'',
        //     'msg_value'=>''
        // ));

        if($res){
            do_action('b2_send_dmsg_action',$msg_data);
            return true;
        }
    	
    	return false;
    }

    //私信对话列表
    public static function get_my_directmessage_list($user_id,$paged){

        $current_user_id = b2_get_current_user_id();
        if(!$current_user_id) return array('error'=>__('请先登录','b2'));
        
        $number = 20;
        $offset = ($paged-1)*$number;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $mark = (string)$user_id.'+'.(string)$current_user_id;

        if($user_id > $current_user_id){
            $mark = (string)$current_user_id.'+'.(string)$user_id;
        }

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `mark`=%s ORDER BY `date` DESC LIMIT $offset,$number",$mark),
            ARRAY_A
        );

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `mark`=%s",$mark));

        $data = array();
        if(!empty($res)){
            $res = array_reverse($res);
            foreach ($res as $k => $v) {
                if($v['from'] == $current_user_id){
                    $type = 'from';
                }else{
                    $type = 'to';
                }
                $data[] = array(
                    'id'=>$v['id'],
                    'from'=>User::get_user_normal_data($v['from']),
                    'to'=>User::get_user_normal_data($v['to']),
                    'date'=>b2_timeago($v['date']),
                    '_date'=>$v['date'],
                    'content'=>wpautop($v['content']),
                    'type'=>$type,
                    'status'=>$v['status'],
                    'key'=>$v['key'],
                    'value'=>$v['value']
                );

                if($v['status'] == 0 && $v['from'] != $current_user_id){
                    $wpdb->update( 
                        $table_name, 
                        array( 
                            'status' => 1
                        ), 
                        array( 'id' => $v['id'] ), 
                        array( 
                            '%d'
                        ), 
                        array( '%d' ) 
                    );
                }
            }

            delete_user_meta($current_user_id,'b2_dmsg_unread');
        }

        return array(
            'pages'=>ceil($count/$number),
            'data'=>$data,
            'count'=>$count
        );
    }

    public static function get_new_dmsg(){
        $current_user_id = b2_get_current_user_id();
        if(!$current_user_id) return array('error'=>__('请先登录','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `status`=%d AND `to`=%d ORDER BY `date` DESC LIMIT 5",0,$current_user_id),
            ARRAY_A
        );

        $data = array();

        if($res && !empty($res)){
            foreach ($res as $k => $v) {
                $data[] = array(
                    'id'=>$v['id'],
                    'from'=>User::get_user_normal_data($v['from']),
                    'to'=>User::get_user_normal_data($v['to']),
                    'date'=>$v['date'],
                    'content'=>b2_get_des(1,50,$v['content']),
                    'status'=>$v['status']
                );
            }
        }

        return array(
            'dmsg'=>array(
                'data'=>$data,
                'count'=>count($data),
                'show'=>false
            )
        );
    }
}
