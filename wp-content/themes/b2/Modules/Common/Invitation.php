<?php namespace B2\Modules\Common;

class Invitation{
    public function init(){

    }
    public static function invitationCheck($code){

        if(!$code) return array('error'=>__('请输入邀请码','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_invitation';

        $res = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE invitation_status= %d
                AND invitation_nub=%s
                ",
                0,$code
        ),ARRAY_A);

        if(!$res){
            return array('error'=>__('邀请码错误或不存在！','b2'));
        }

        return $res;
    }

    /**
     * 邀请码的使用
     *
     * @param int $user_id 使用者的用户ID
     * @param string $inv_id 邀请码
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function useInv($user_id,$inv_id){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_invitation';

        $wpdb->update(
            $table_name, 
            array( 
                'invitation_status' => '1',
                'invitation_user' => $user_id,
            ), 
            array( 'id' => $inv_id ), 
            array( 
                '%s',
                '%s'
            ), 
            array( '%s' ) 
        );
    }

    /**
     * 获取某个用户的邀请码
     *
     * @param [type] $user_id
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_user_inv_list($user_id){

        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('您没有权限进行此项操作','b2'));
        
        if((int)$user_id !== $current_user_id && !user_can($current_user_id, 'administrator' )) return array('error'=>__('非法操作','b2')); 

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_invitation';

        $res = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE invitation_owner=%s
                LIMIT 20
            ",$user_id)
        ,ARRAY_A);

        $data = array();

        if($res){
            foreach ($res as $k => $v) {
                $user = $v['invitation_user'];
                if((int)$user !== 0){
                    $user = array(
                        'link'=>get_author_posts_url($user),
                        'name'=>get_the_author_meta('display_name',$user)
                    );
                }
                $data[] = array(
                    'inv_code'=>$v['invitation_nub'],
                    'credit'=>$v['invitation_credit'],
                    'status'=>$v['invitation_status'],
                    'user'=>$user
                );
            }
        }

        return $data;
    }
}