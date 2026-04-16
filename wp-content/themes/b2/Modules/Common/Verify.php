<?php namespace B2\Modules\Common;

class Verify{

    //获取公众号二维码
    public static function get_verify_info(){

        $allow = b2_get_option('verify_main','verify_allow');

        if(!$allow) return array('error'=>__('认证已关闭','b2'));

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $status = 0;

        //获取当前用户的认证数据
        $data = self::get_verify_data($user_id);

        $qrcode = self::get_qrcode();

        return array(
            'data'=>$data,
            'qrcode'=>$qrcode,
            'is_weixin'=>b2_is_weixin()
        );

    }

    //获取二维码
    public static function get_qrcode(){

        $allow = b2_get_option('verify_main','verify_allow');

        if(!$allow) return array('error'=>__('认证已关闭','b2'));

        $settings = b2_get_option('verify_main','verify_type');
	
		if(!in_array('2',$settings)) return '';

        $user_id = b2_get_current_user_id();

        $ticket = Wecatmp::get_ticket('verify');
        
        try {

            $wechat = new \WeChat\Qrcode(Wecatmp::get_wecat_option());
            return $wechat->url($ticket['ticket']);
            
        } catch (\Exception $e){
            return array('error'=>$e->getMessage());
        }
    }

    //检查用户是否已经关注
    public static function check_subscribe(){
        $user_id = b2_get_current_user_id();
        $data = self::get_verify_data($user_id);

        if((int)$data['mp'] !== 1){
            return false;
        }

        if((int)$data['status'] === 3){
            return false;
        }

        return true;
    }

    //获取认证数据
    public static function get_verify_data($user_id){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_verify';

        $res = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE user_id=%d
                ",
                $user_id
        ),ARRAY_A);

        if(!$res){
            return array(
                'date' => current_time('mysql'),
                'user_id' => 0,
                'verified' => 0,
                'name' => '',
                'identification' => '',
                'card' => '',
                'mp'=>0,
                'money'=>0,
                'title'=>'',
                'status'=>0
            );
        }

        return $res;
    }

    public static function add_verify_data($data){

        $allow = b2_get_option('verify_main','verify_allow');

        if(!$allow) return array('error'=>__('认证已关闭','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_verify';

        //检查之前是否有相同数据
        $res = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE user_id = %d
                ",
                $data['user_id']
            )
        ,ARRAY_A);
        
        if($res){
            
            $old = $res[0];

            $data = array_merge($old,$data);

            $where = array('id' => $old['id']);

            $wpdb->update(
                $table_name, 
                $data, 
                array( 'id' => $old['id']), 
                array(
                    '%d',//id
                    '%s',//date
                    '%d',//user_id
                    '%d',//verified
                    '%s',//name
                    '%s',//identification
                    '%s',//card
                    '%d',//mp
                    '%f',//money
                    '%s',//title
                    '%d'//status
                ),
                array('%d')
            );
        }else{

            $_data = array(
                'date' => current_time('mysql'),
                'user_id' => 0,
                'verified' => 0,
                'name' => '',
                'identification' => '',
                'card' => '',
                'mp'=>0,
                'money'=>0,
                'title'=>'',
                'status'=>0
            );

            $data = array_merge($_data,$data);

            $wpdb->insert(
                $table_name, 
                $data,
                array(
                    '%s',//date
                    '%d',//user_id
                    '%d',//verified
                    '%s',//name
                    '%s',//identification
                    '%s',//card
                    '%d',//mp
                    '%f',//money
                    '%s',//title
                    '%d'//status
                )
            );
        }

        return $data;
    }

    public static function submit_verify($data){

        $data = apply_filters('b2_submit_verify_before', $data);
        if(isset($data['error'])) return array('error'=>$data['error']);

        $allow = b2_get_option('verify_main','verify_allow');

        if(!$allow) return array('error'=>__('认证已关闭','b2'));

        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $settings = b2_get_option('verify_main','verify_type');

        $censor = apply_filters('b2_text_censor', $data['name'].$data['title']);
        if(isset($censor['error'])) return $censor;

        //检查实名信息
        if(in_array('1',$settings)){
            if(!self::validation_filter_id_card($data['identification'])){
                return array('error'=>__('身份证号码错误','b2'));
            }

            if(mb_strlen($data['name'], 'UTF-8') > 6){
                return array('error'=>__('姓名超出长度','b2'));
            }

            if(empty($data['card'])){
                return array('error'=>__('请上传证件照','b2'));
            }

            $data['name'] = str_replace(array('{{','}}'),'',sanitize_text_field($data['name']));
            $data['name'] = sanitize_textarea_field($data['name']);
        }

        if(empty($data['title'])) return array('error'=>__('请填写称号','b2'));

        $_data = self::get_verify_data($user_id);

        if(in_array('2',$settings)){
            if(!isset($_data['mp']) || (int)$_data['mp'] !== 1){
                return array('error'=>__('请先关注公众号','b2'));
            }
        }

        if(in_array('3',$settings)){
            if(!isset($_data['money']) || (float)$_data['money'] == 0){
                return array('error'=>__('请先支付费用','b2'));
            }
        }

        if(isset($_data['status']) && $_data['status'] === 3){
            return array('error'=>__('已拉黑，无法继续认证','b2'));
        }

        if(mb_strlen($data['title'], 'UTF-8') > 30){
            return array('error'=>__('称号超出长度','b2'));
        }

        $data['title'] = str_replace(array('{{','}}'),'',wp_strip_all_tags($data['title']));
        $data['title'] = sanitize_textarea_field(wp_unslash($data['title']));
        
        $data['card'] = esc_url($data['card']);

        $check = (int)b2_get_option('verify_main','verify_check');

        $arg = array(
            'date'=>current_time('mysql'),
            'user_id'=>$user_id,
            'verified'=>0,
            'name'=>$data['name'],
            'identification'=>$data['identification'],
            'card'=>$data['card'],
            'title'=>$data['title'],
            'status'=>$check === 0 ? 4 : 2
        );

        $data = self::add_verify_data($arg);

        if($check === 1){

            $task_check = get_user_meta($user_id,'b2_task_check',true);
            if($task_check === ''){
                $credit = b2_get_option('normal_task','task_user_verify');
                if((int)$credit !== 0){
                    // $total = Credit::credit_change($user_id,$credit);

                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$user_id,
                        'gold_type'=>0,
                        'post_id'=>0,
                        'no'=>$credit,
                        'msg'=>__('认证任务完成奖励','b2'),
                        'type'=>'user_verify',
                        'type_text'=>__('认证完成','b2')
                    ]);
    
                    //积分记录
                    // Message::add_message(array(
                    //     'user_id'=>$user_id,
                    //     'msg_type'=>60,
                    //     'msg_read'=>0,
                    //     'msg_date'=>current_time('mysql'),
                    //     'msg_users'=>'',
                    //     'msg_credit'=>$credit,
                    //     'msg_credit_total'=>$total,
                    //     'msg_key'=>'',
                    //     'msg_value'=>''
                    // ));

                    Message::update_data([
                        'date'=>current_time('mysql'),
                        'from'=>0,
                        'to'=>$user_id,
                        'post_id'=>0,
                        'msg'=>__('您已经完成了认证任务','b2'),
                        'type'=>'user_verify',
                        'type_text'=>__('认证成功','b2')
                    ]);

                    update_user_meta($user_id,'b2_task_check',1);
                }
                
            }
            
            update_user_meta($user_id,'b2_title',$data['title']);
            wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
            
            do_action('b2_submit_verify_after',$data);
            do_action('b2_user_rebuild_title', $user_id);

            return 'success';
        }else{
            do_action('b2_submit_verify_after',$data);

            return 'success';
        }

        return array('error'=>__('数据存储失败','b2'));
    }

    public static function validation_filter_id_card($id){
        // return true;
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        
        $arr_split = [];
        if(!preg_match($regx, $id)){
            return false;
        }
        
        if(15==strlen($id)){
            // 检查15位
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

            @preg_match($regx, $id, $arr_split);
            // 检查生日日期是否正确
            $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            
            if(!wp_strtotime($dtm_birth)){
                
                return false;
            }else{
                return true;
            }
        }else{
            // 检查18位
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            
            //检查生日日期是否正确
            if(!wp_strtotime($dtm_birth)) {
                return false;
            }else{
                
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
                $arr_ch = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
                $sign = 0;
                
                for ( $i = 0; $i < 17; $i++ ){
                    $b = (int) $id[$i];
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n = $sign % 11;
                $val_num = $arr_ch[$n];
                
                if ($val_num != substr($id,17, 1)){
                    return false;
                }else{
                    return true;
                }
            }
        }
    }
}
