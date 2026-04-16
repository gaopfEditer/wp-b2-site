<?php namespace B2\Modules\Common;

class Cache{

    public function init(){
        add_action('b2_user_social_login', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_save_cover', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_save_avatar', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_lv', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_vip', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_money', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_open', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_sex', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_desc', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_name', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_url', array(__CLASS__,'clean_user_public_data'));
        add_action('b2_user_rebuild_title', array(__CLASS__,'clean_user_public_data'));
    }

    public static function clean_index_module_cache(){
        $count = get_option('b2_template_index');
        if($count){
            $count = isset($count['index_group']) ? count($count['index_group']) : 0;

            if($count){
                $key = md5(B2_HOME_URI);
                for ($i=0; $i < $count+1; $i++) { 
                    delete_transient($key.'_b2_index_module_'.$i);
                }
            }
        }
    }

    public static function clean_user_public_data($user_id){
        delete_transient('b2_user_'.$user_id);
    }

}