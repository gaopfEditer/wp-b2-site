<?php

namespace wp_opt;

class Options
{

    static function getAdminSet($hook = '')
    {
        global $wpopt_set;
        $data['ajax_url'] = admin_url('admin-ajax.php');
        $data['ajax_name'] = Config::$plugin_name;
        $data['version_name'] = Config::$plugin_version_name;
        $data['img_url'] = Config::$img_url;
        $data['user_url'] = admin_url('users.php');
        $data['plugin_url'] = admin_url('plugins.php');
        $data['post_url'] = admin_url('edit.php');
        $data['comment_url'] = admin_url('edit-comments.php');
        $data['logo'] = Config::$img_url . '/logo_new.svg';
        $data['stu_logo'] = Config::$img_url . '/stulogo.png';
        $data['ghxi_logo'] = Config::$img_url . '/ghxilogo.png';
        $data['vite_logo'] = Config::$img_url . '/vite-logo.svg';
        $data['element_plus_logo'] = Config::$img_url . '/element-plus-logo.svg';
        $data['vue_logo'] = Config::$img_url . '/vue-logo.svg';
        global $wpdb;
        if ($hook === 'toplevel_page_wpopt_set') {
            $data['info']['php_version'] = phpversion();
            $data['info']['total_users'] = Plugin::countAllUsers();
            $data['info']['theme_count'] = count(wp_get_themes());
            $data['info']['plugin_count'] = count(get_plugins());
            $data['info']['active_plugin_count'] = count(get_option('active_plugins'));
            $post_count = wp_count_posts();
            $total_posts = $post_count->publish + $post_count->draft + $post_count->pending + $post_count->trash;
            $data['info']['post_count'] = $total_posts;
            $data['info']['post_publish'] = $post_count->publish;
            $data['info']['post_draft'] = $post_count->draft;
            $comment_count = wp_count_comments();
            $total_comments = $comment_count->approved + $comment_count->moderated + $comment_count->spam;
            $data['info']['comment_count'] = $total_comments;
            $data['info']['comment_approved'] = $comment_count->approved;
            $data['info']['comment_moderated'] = $comment_count->moderated;
            $data['info']['mysql_version'] = $wpdb->db_version();
            $data['info']['wordpress_version'] = get_bloginfo('version');
        }
        $data['set'] = $wpopt_set;
        $data['cat_list'] = WordPress::getCatList();
        $data['can_convert_webp'] = Tools::canConvertWebP();

        return $data;
    }


    static function saveSet($data, $is_array = false)
    {
        if ($is_array === true) {
            return update_option(Config::$set_name, base64_encode(json_encode($data)));
        }
        return update_option(Config::$set_name, $data);
    }

    static function getOptions()
    {
        $default = self::getDefaultOptions();
        $set = get_option(Config::$set_name, false);
        if ($set === false) {
            return $default;
        }

        if (!is_string($set)) {
            return $default;
        }
        $set_obj = json_decode(base64_decode($set), true);
        if ($set_obj === false) {
            return $default;
        } else {
            return self::updateOptions($set_obj, $default);
        }
    }

    private static function getDefaultOptions()
    {
        $data['last_check_time'] = time();
        $data['need_update'] = false;
        $data['remove_version'] = false;
        $data['remove_file_version'] = false;
        $data['remove_dns_prefetch'] = false;
        $data['remove_json_url'] = false;
        $data['remove_post_meta'] = false;
        $data['remove_post_feed'] = false;
        $data['remove_wp_block_library_css'] = false;
        $data['remove_dashicons'] = false;
        $data['remove_rsd'] = false;
        $data['remove_wlwmanifest'] = false;
        $data['remove_short_link'] = false;

        $data['ban_translations_api'] = false;
        $data['ban_wp_check_php_version'] = false;
        $data['ban_wp_check_browser_version'] = false;
        $data['ban_current_screen'] = false;

        $data['remove_login_logo'] = false;
        $data['remove_dashboard_icon'] = false;

        $data['close_rest_api'] = false;
        $data['close_pingback'] = false;
        $data['close_xml_rpc'] = false;

        $data['close_emoji'] = false;
        $data['close_admin_bar'] = false;
        $data['close_login_translate'] = false;

        $data['close_revision'] = false;
        $data['close_auto_save_post'] = false;
        $data['close_image_height_limit'] = false;
        $data['close_image_creat_size'] = false;
        $data['close_image_srcset'] = false;
        $data['close_image_scaled'] = false;
        $data['close_image_attributes'] = false;

        $data['close_transcoding'] = false;
        $data['close_auto_embeds'] = false;
        $data['close_post_embeds'] = false;
        $data['restore_gutenberg'] = false;
        $data['restore_widget_gutenberg'] = false;


        $data['close_core_update'] = false;
        $data['close_theme_update'] = false;
        $data['close_plugin_update'] = false;

        $data['close_mail_update_user_info_note'] = false;
        $data['close_mail_register_note'] = false;
        $data['close_email_check'] = false;

        $data['gravatar'] = 'no';
        $data['custom_gravatar_url'] = '';

        $data['need_chinese_comment'] = false;

        $data['webp_support'] = false;
        $data['svg_support'] = false;
        $data['page_add_html'] = false;
        $data['remove_link_category_text'] = false;

        $data['site_grey'] = false;


        $data['paste_upload_img'] = false;
        $data['paste_upload_img_to_webp'] = false;
        $data['auto_rename_img'] = false;
        $data['auto_rename_img_type'] = 'time';


        $data['insert_css'] = '';
        $data['insert_js_header'] = '';
        $data['insert_js_footer'] = '';
        $data['require_files'] = [];
        //search power
        $data['ban_search_word_open'] = false;
        $data['ban_search_word'] = '';
        $data['search_ban_cat_list'] = [];

        //debug
        $data['echo_sql_number'] = false;
        $data['echo_page_generation_time'] = false;
        $data['memory_usage'] = false;

        $data['wx_password_remote'] = '';
        $data['wx_password_set'] = '';

        $data['need_modify_cache'] = true;

        $data['module_watermark_open'] = false;
        $data['module_watermark_type'] = 'text';
        $data['module_watermark_text'] = 'WPOPT';
        $data['module_watermark_img'] = Config::$img_url . '/stulogo.png';
        $data['module_watermark_text_size'] = 16;
        $data['module_watermark_angle'] = 0;

        $data['module_watermark_position_type'] = 'designate';
        $data['module_watermark_position'] = 'center';
        $data['module_watermark_max_width'] = 300;
        $data['module_watermark_max_height'] = 300;

        $data['module_seo_open'] = false;
        $data['module_seo_push_api_baidu_pt'] = '';

        $data['module_post_views_open'] = false;
        $data['module_post_views_add_number'] = 1;
        $data['module_post_views_need_login'] = false;
        return $data;
    }

    private static function updateOptions($set, $default_set)
    {
        foreach ($default_set as $key => &$item) {
            if (isset($set[$key])) {
                $item = $set[$key];
            }
        }
        return $default_set;
    }
}