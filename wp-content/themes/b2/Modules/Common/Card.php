<?php namespace B2\Modules\Common;

class Card{
    public static function card_pay($number,$password){
        $user_id = b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_card';

        $res = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE card_key=%s
                AND card_value= %s
                AND card_status=%d
                ",
                trim($number, " \t\n\r\0\x0B\xC2\xA0"), trim($password, " \t\n\r\0\x0B\xC2\xA0"),0
        ),ARRAY_A);

        if(empty($res) || !isset($res['card_rmb']) || !$res){
            apply_filters('b2_check_repo_after', $user_id,$public_count);
            return array('error'=>__('充值卡错误或已被使用','b2'));
        }

        if($wpdb->update(
            $table_name, 
            array( 
                'card_status' => 1,
                'card_user' => $user_id
            ), 
            array( 'id' => $res['id'] ),
            array( 
                '%d',
                '%d'
            ), 
            array( '%d' ) 
        )){
            if($res['card_rmb'] <=0) return array('error'=>__('金额错误','b2'));
            // $total = User::money_change($user_id,$res['card_rmb']);

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$user_id,
                'gold_type'=>1,
                'no'=>$res['card_rmb'],
                'msg'=>sprintf(__('您充值了面额为%s的卡密，卡号为：%s'),B2_MONEY_SYMBOL.$res['card_rmb'],$res['card_key']),
                'type'=>'card',
                'type_text'=>__('卡密充值','b2')
            ]);

            // Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>55,
            //     'msg_read'=>1,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>'',
            //     'msg_credit'=>$res['card_rmb'],
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>$res['id'],
            //     'msg_value'=>$res['card_key']
            // ));

            return 'success';
        }

    }

}