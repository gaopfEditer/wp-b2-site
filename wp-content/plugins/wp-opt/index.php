<?php
/*
Plugin Name: WPOPT
Plugin URI: https://www.lovestu.com/wpopt.html
Description: WPOPT-WordPress高级优化插件
Version: 2.0.8
Requires at least: 5.7
Tested up to: 5.8
Requires PHP: 5.6
Author: applek
Author URI: https://www.lovestu.com/
*/


require_once WP_PLUGIN_DIR . '/wp-opt/core/Config.php';

function wpopt_active()
{
    return true;
}


global $wpopt_set;
if ($wpopt_set['module_post_views_open']) {
        if (!function_exists('the_views') && !file_exists(WP_PLUGIN_DIR . '/wp-postviews/wp-postviews.php')) {
        function the_views($display = true, $prefix = '', $postfix = '', $always = true)
        {
            $post_views = (int)get_post_meta(get_the_ID(), 'views', true);
            if ($always) {
                $output = $post_views;
                if ($display) {
                    echo apply_filters('the_views', $output);
                } else {
                    return apply_filters('the_views', $output);
                }
            } elseif (!$display) {
                return $post_views;
            }
            return $post_views;
        }

        /** 格式化阅读量
         * @return mixed|string
         */
        function wpopt_postviews_to_string()
        {
            $views = the_views(false);
            return wp_opt\Tools::postViewsRoundNumber($views);
        }
    }
}
