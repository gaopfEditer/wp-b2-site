<?php namespace B2\Modules\Common;

class Credit{
    public static function credit_change($user_id,$_credit){

        global $user_current_credit;
        //获取当前用户的积分
        $credit = get_user_meta($user_id,'zrz_credit_total',true);
        $credit = $credit ? (int)$credit : 0;

        $user_current_credit = $credit;
        //积分增减
        $credit = (int)$_credit + $credit;

        if($credit < 0){
            return false;
        }

        //更新积分
        update_user_meta($user_id,'zrz_credit_total',(int)$credit);

        //重新计算等级
        User::rebuild_user_lv($user_id);

        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
        
        return apply_filters('b2_change_credit',$credit,$user_id);
    }
}