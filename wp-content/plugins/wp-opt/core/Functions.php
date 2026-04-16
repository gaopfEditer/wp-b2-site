<?php

namespace wp_opt;

class Functions
{
    static function init()
    {
        global $wpopt_set;
        $wpopt_set = Options::getOptions();
        self::echo();
        self::funClose();
        self::optimize();
        self::commentPower();
        self::addFun();
        self::banFun();
        self::insertCode();
        self::searchPower();
        add_action('init', [static::class, 'deBug']);
        self::moduleInt();

    }

    static function moduleInt()
    {
        add_action('wp_enqueue_scripts', array(static::class, '_LoadFileOnSite'));
    }

    static function _LoadFileOnSite()
    {
        global $wpopt_set;
        if ($wpopt_set['module_post_views_open']) {
            $data['ajax_url'] = admin_url('admin-ajax.php');
            $data['ajax_name'] = Config::$plugin_name;
            $data['is_post'] = false;
            if (is_single() || is_page()) {
                $data['is_post'] = true;
                $data['post_id'] = get_the_ID();
            }
            $data['module_post_views_open'] = $wpopt_set['module_post_views_open'];
            WordPress::loadJS('wpopt_front', 'front.min.js', true, [], true);
            wp_localize_script('wpopt_front', 'wpopt', $data);
        }
    }

    static function searchPower()
    {
        global $wpopt_set;
        if ($wpopt_set['ban_search_word_open'] && $wpopt_set['ban_search_word'] !== '') {
            $word_array = explode(",", $wpopt_set['ban_search_word']);
            add_action('pre_get_posts', function ($query) use ($word_array) {
                if ($query->is_search) {
                    $search_text = $query->get('s');
                    foreach ($word_array as $item) {
                        if (strpos($search_text, $item) !== false) {
                            $query->set('s', ''); // 清空搜索查询
                            // $query->is_search = false;
                            $query->query_vars['s'] = ''; // 清空查询变量
                            break;
                        }
                    }
                }
            });
        }

        if (count($wpopt_set['search_ban_cat_list']) > 0) {
            $cat_list = $wpopt_set['search_ban_cat_list'];
            foreach ($cat_list as &$item) {
                $item = '-' . $item;
            }
            $cat_text = implode(",", $cat_list);
            add_filter('pre_get_posts', function ($query) use ($cat_text) {
                if ($query->is_search) {
                    $query->set('cat', $cat_text);
                }
                return $query;
            });
        }
    }

    static function insertCode()
    {
        global $wpopt_set;
        if ($wpopt_set['insert_js_header'] != '') {
            add_action('wp_head', function () use ($wpopt_set) {
                ?>
                <?php echo base64_decode($wpopt_set['insert_js_header']); ?>
                <?php
            });
        }
        if ($wpopt_set['insert_js_footer'] != '') {
            add_action('wp_footer', function () use ($wpopt_set) {
                ?>
                <?php echo base64_decode($wpopt_set['insert_js_footer']); ?>
                <?php
            });
        }
        if ($wpopt_set['insert_css'] != '') {
            add_action('wp_footer', function () use ($wpopt_set) {
                ?>
                <style><?php echo base64_decode($wpopt_set['insert_css']); ?></style>
                <?php
            });
        }
        if (is_array($wpopt_set['require_files']) && count($wpopt_set['require_files']) > 0) {
            add_action('wp_enqueue_scripts', function () use ($wpopt_set) {
                foreach ($wpopt_set['require_files'] as $item) {
                    if ($item['type'] == 'js') {
                        wp_enqueue_script($item['name'], $item['url'], [], Config::$plugin_version, $item['position'] == 'footer');
                    } else {
                        wp_enqueue_style($item['name'], $item['url'], [], Config::$plugin_version);
                    }
                }
            });
        }
    }

    static function deBug()
    {
        global $wpopt_set;
        $set = $wpopt_set;
        if (!WordPress::isAdmin()) {
            return;
        }
        if ($set['echo_sql_number']) {
            add_action('wp_footer', function () {
                $num_queries = get_num_queries();
                Plugin::echoLog("当前页面SQL次数：$num_queries");
            });
        }
        if ($set['echo_page_generation_time']) {
            add_action('wp_footer', function () {
                $time = timer_stop(0, 4);

                Plugin::echoLog("当前页面创建时间：{$time}s");
            });
        }
        if ($set['memory_usage']) {
            add_action('wp_footer', function () {
                $memory = number_format(memory_get_peak_usage() / 1024 / 1024, 3);
                Plugin::echoLog("当前页面所消耗内存：{$memory}MB");
            });
        }
    }

    static function addFun()
    {
        global $wpopt_set;
        $set = $wpopt_set;
        if ($set['webp_support']) {
            add_filter('upload_mimes', array(static::class, 'uploadSupport'));
            add_filter('file_is_displayable_image', array(static::class, 'fileDisplaySupport'), 10, 2);
        }
        if ($set['svg_support']) {
            add_filter('upload_mimes', array(static::class, 'uploadSupport'));
            add_filter('file_is_displayable_image', array(static::class, 'fileDisplaySupport'), 10, 2);
        }
        if ($set['page_add_html']) {
            add_action('init', array(static::class, 'rewritePage'), -1);
            add_filter('user_trailingslashit', array(static::class, 'noPageSlash'), 100, 2);
            add_action('save_post', array(static::class, 'savePostAddHtml'), 20, 1);
        }

        if ($set['remove_link_category_text']) {
            funEnhance::removeCategory();
        }

        if ($set['paste_upload_img_to_webp']) {
            add_filter('wp_handle_upload', [static::class, 'convertImageToWebp']);
        }

        if ($set['auto_rename_img']) {
            add_filter('wp_handle_upload_prefilter', function ($file) use ($set) {
                $info = pathinfo($file['name']); // 获取上传文件的信息
                $ext = !empty($info['extension']) ? '.' . $info['extension'] : ''; // 获取文件扩展名
                if ($set['auto_rename_img_type'] === 'time') {
                    $unique_time = microtime(true);
                    $formatted_time = date('YmdHis', $unique_time) . sprintf("%06d", ($unique_time - floor($unique_time)) * 1000000);
                    $new_name = $formatted_time . $ext;
                } else {
                    $new_name = md5(uniqid()) . $ext;
                }
                //Tools::writeLog('更新文件名');
                $file['name'] = $new_name; // 更新文件名
                return $file; // 返回更新后的文件信息
            });
        }

        if ($set['site_grey']) {
            add_action('wp_footer', function () {
                print_r('<style>body{ filter: grayscale(100%) }</style>');
            });
        }
    }

    static function commentPower()
    {
        global $wpopt_set;
        $set = $wpopt_set;
        if ($set['need_chinese_comment']) {
            add_filter('preprocess_comment', function ($commentdata) {
                $pattern = '/[\x{4e00}-\x{9fa5}]/u';
                if (!preg_match($pattern, $commentdata['comment_content'])) {
                    wp_die('评论必须包含中文！');
                }
                return $commentdata;
            });
        }
    }

    static function echo()
    {
        global $wpopt_set;
        $set = $wpopt_set;
        if ($set['remove_version']) {
            // 头部
            remove_action('wp_head', 'wp_generator');
            //rss
            add_filter('the_generator', '__return_empty_string');
        }
        if ($set['remove_file_version']) {
            add_filter('style_loader_src', array(static::class, 'removeFileVersion'), PHP_INT_MAX);
            add_filter('script_loader_src', array(static::class, 'removeFileVersion'), PHP_INT_MAX);
        }

        if ($set['remove_dns_prefetch']) {
            remove_action('wp_head', 'wp_resource_hints', 2);
        }
        if ($set['remove_json_url']) {
            //去除json连接
            remove_action('wp_head', 'rest_output_link_wp_head');
            remove_action('template_redirect', 'rest_output_link_header');
        }
        if ($set['remove_post_meta']) {
            //移除前后文、第一篇文章、主页meta信息
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        }
        if ($set['remove_post_feed']) {
            //移除feed
            remove_action('wp_head', 'feed_links', 2);//文章和评论feed
            remove_action('wp_head', 'feed_links_extra', 3); //分类等feed
        }
        if ($set['remove_wp_block_library_css']) {
            //WordPress 5.0+移除 古藤堡编辑器
            add_action('wp_enqueue_scripts', array(static::class, 'removeWpBlockLibraryCss'), 9999);
        }
        if ($set['remove_dashicons']) {
            add_action('wp_print_styles', array(static::class, 'removeDashicons'), 9999);
        }
        if ($set['remove_rsd']) {
            remove_action('wp_head', 'rsd_link');
        }
        if ($set['remove_wlwmanifest']) {
            remove_action('wp_head', 'wlwmanifest_link');
        }
        if ($set['remove_short_link']) {
            remove_action('wp_head', 'wp_shortlink_wp_head');
        }

    }

    static function banFun()
    {
        global $wpopt_set;
        $set = $wpopt_set;

        if ($set['ban_translations_api']) {
            add_filter('translations_api', '__return_true');
        }
        if ($set['ban_wp_check_php_version']) {
            add_filter('wp_check_php_version', '__return_true');
            remove_action('admin_init', 'wp_check_php_version');
            add_filter('wp_is_php_version_acceptable', function ($is_acceptable, $required_version) {
                return true;
            }, 10, 2);
        }

        if ($set['ban_wp_check_browser_version']) {
            add_filter('wp_check_browser_version', '__return_true');
            remove_action('admin_head', 'wp_check_browser_version');
        }

        if ($set['ban_current_screen']) {
            add_filter('current_screen', '__return_true');
        }
    }

    static function funClose()
    {
        global $wpopt_set;
        $set = $wpopt_set;
        if ($set['close_rest_api']) {
            //屏蔽 REST API
            add_filter('rest_enabled', '__return_false');
            add_filter('rest_jsonp_enabled', '__return_false');
        }
        if ($set['close_pingback']) {
            add_filter('xmlrpc_methods', function ($methods) {
                $methods['pingback.ping'] = '__return_false';
                $methods['pingback.extensions.getPingbacks'] = '__return_false';
                return $methods;
            });
            //禁用 pingbacks, enclosures, trackbacks
            remove_action('do_pings', 'do_all_pings');
            //去掉 _encloseme 和 do_ping 操作。
            remove_action('publish_post', '_publish_post_hook');
        }

        if ($set['close_xml_rpc']) {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('xmlrpc_methods', '__return_empty_array');

        }
        if ($set['close_emoji']) {
            //禁止emoji
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('embed_head', 'print_emoji_detection_script');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        }
        if ($set['remove_login_logo']) {
            add_action('login_footer', function () {
                ?>
                <script>
                    window.addEventListener('load', function () {
                        // 所有资源加载完成后的处理逻辑
                        jQuery('#login>h1:first-child').remove();
                    });
                </script>
                <?php
            });
        }

        if ($set['remove_dashboard_icon']) {
            add_action('wp_before_admin_bar_render', function () {
                global $wp_admin_bar;

                $wp_admin_bar->remove_menu('wp-logo');
            }, 0);
        }
        if ($set['close_admin_bar']) {
            add_filter('show_admin_bar', '__return_false');
        }
        if ($set['close_login_translate']) {
            add_filter('login_display_language_dropdown', '__return_false');
        }
        if ($set['close_revision']) {
            define('WP_POST_REVISIONS', false);
            add_filter('wp_revisions_to_keep', '__return_false');
        }
        if ($set['close_auto_save_post']) {
            define('AUTOSAVE_INTERVAL', false);
            add_action('admin_print_scripts', function () {
                wp_deregister_script('autosave');
            });
        }
        if ($set['close_image_height_limit']) {
            add_filter('big_image_size_threshold', '__return_false');
        }
        if ($set['close_image_creat_size']) {
            add_filter('intermediate_image_sizes_advanced', function () {
                return [];
            });
            add_filter('big_image_size_threshold', '__return_false');
        }
        if ($set['close_image_srcset']) {
            add_filter('wp_calculate_image_srcset', '__return_false');
        }
        if ($set['close_image_attributes']) {
            add_filter('post_thumbnail_html', function ($html) {
                $html = preg_replace('/width="(\d*)"\s+height="(\d*)"\s+class=\"[^\"]*\"/', "", $html);
                $html = preg_replace('/  /', "", $html);

                return $html;
            }, 10);
            add_filter('image_send_to_editor', function ($html) {
                $html = preg_replace('/width="(\d*)"\s+height="(\d*)"\s+class=\"[^\"]*\"/', "", $html);
                $html = preg_replace('/  /', "", $html);
                return $html;
            }, 10);
        }
        if ($set['close_transcoding']) {
            add_filter('run_wptexturize', '__return_false');
        }
        if ($set['close_auto_embeds']) {
            remove_filter('the_content', array($GLOBALS['wp_embed'], 'autoembed'), 8);
        }
        if ($set['close_post_embeds']) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');

        }
        if ($set['restore_gutenberg']) {
            add_filter('use_block_editor_for_post', '__return_false');
        }
        if ($set['restore_widget_gutenberg']) {
            add_filter('gutenberg_use_widgets_block_editor', '__return_false');
            add_filter('use_widgets_block_editor', '__return_false');
        }
        if ($set['close_core_update']) {
            // 彻底关闭自动更新
            add_filter('automatic_updater_disabled', '__return_true');
            // 关闭更新检查定时作业
            remove_action('init', 'wp_schedule_update_checks');
            //  移除已有的版本检查定时作业
            wp_clear_scheduled_hook('wp_version_check');
            // 移除已有的自动更新定时作业
            wp_clear_scheduled_hook('wp_maybe_auto_update');
            // 移除后台内核更新检查
            remove_action('admin_init', '_maybe_update_core');
            add_filter('pre_site_transient_update_core', function ($a) {
                return null;
            });
        }
        if ($set['close_theme_update']) {
            wp_clear_scheduled_hook('wp_update_themes');
            add_filter('auto_update_theme', '__return_false');
            remove_action('load-themes.php', 'wp_update_themes');
            remove_action('load-update.php', 'wp_update_themes');
            remove_action('load-update-core.php', 'wp_update_themes');
            remove_action('admin_init', '_maybe_update_themes');
            add_filter('pre_set_site_transient_update_themes', function ($a) {
                return null;
            });
        }
        if ($set['close_plugin_update']) {
            wp_clear_scheduled_hook('wp_update_plugins');
            add_filter('auto_update_plugin', '__return_false');
            add_filter('pre_site_transient_update_plugins', function ($a) {
                return null;
            });
            remove_action('load-plugins.php', 'wp_update_plugins');
            remove_action('load-update.php', 'wp_update_plugins');
            remove_action('load-update-core.php', 'wp_update_plugins');
            remove_action('admin_init', '_maybe_update_plugins');

        }
        if ($set['close_mail_update_user_info_note']) {
            add_filter('send_password_change_email', '__return_false');
            add_filter('email_change_email', '__return_false');
            if (!function_exists('wp_password_change_notification')) {
                function wp_password_change_notification($user)
                {
                    return null;
                }
            }
        }
        if ($set['close_mail_register_note']) {
            add_filter('wp_new_user_notification_email_admin', '__return_false');
            add_filter('wp_new_user_notification_email', '__return_false');

        }
        if ($set['close_email_check']) {
            add_filter('admin_email_check_interval', '__return_false');
        }
    }

    static function optimize()
    {
        global $wpopt_set;
        $set = $wpopt_set;

        if ($set['gravatar'] != 'no') {
            add_filter('get_avatar_url', array(static::class, 'replaceAvatar'), 99, 3);
        }
    }

    static function convertImageToWebp($upload)
    {

        if (!Tools::canConvertWebP()) {
            Tools::writeLog('没有拓展，不支持转换');
            return $upload;
        }
        $file_path = $upload['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name);
        $file_ext = $file_type['ext'];
        $imgs = ['jpeg', 'jpg', 'bmp', 'png'];
        if (in_array($file_ext, $imgs)) {
            $webp_file_path = str_replace('.' . $file_type['ext'], '.webp', $file_path);
            $re = Plugin::imageToWebP($file_path, $webp_file_path, 90, "image/$file_ext");
            if ($re) {
                $upload['file'] = $webp_file_path;
            }
        }
        return $upload;
    }

    static function savePostAddHtml()
    {
        Plugin::flushRules();
    }

    static public function noPageSlash($string, $type)
    {
        global $wp_rewrite;

        if ($wp_rewrite->using_permalinks() && $wp_rewrite->use_trailing_slashes == true && $type == 'page') {
            return untrailingslashit($string);
        }

        return $string;
    }

    static public function rewritePage()
    {
        global $wp_rewrite;

        if (!strpos($wp_rewrite->get_page_permastruct(), '.html')) {
            $wp_rewrite->page_structure = $wp_rewrite->page_structure . '.html';
        }
    }

    static function fileDisplaySupport($result, $path)
    {
        global $wpopt_set;
        $set = $wpopt_set;
        $info = @getimagesize($path);
        if ($set['webp_support'] == 1) {
            if ($info['mime'] == 'image/webp') {
                $result = true;
            }
        }
        if ($set['svg_support'] == 1) {
            if ($info['mime'] == 'image/svg+xml') {
                $result = true;
            }
        }

        return $result;
    }

    static function uploadSupport($mimes = array())
    {
        global $wpopt_set;
        $set = $wpopt_set;

        if ($set['svg_support'] == 1) {
            $mimes['svg'] = 'image/svg+xml';
        }
        if ($set['webp_support'] == 1) {
            if (floatval(get_bloginfo('version')) < 5.8) {
                $mimes['webp'] = 'image/webp';
            }
        }

        return $mimes;
    }

    static function replaceAvatar($avatarUrl, $id_or_email, $arrs)
    {
        global $wpopt_set;
        $set = $wpopt_set;
        $gravatar = $set['gravatar'];
        if ($gravatar == 'v2ex') {
            $cdnurl = 'cdn.v2ex.com/gravatar';
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        } elseif ($gravatar == 'geek') {
            $cdnurl = 'sdn.geekzu.org/avatar';
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        } elseif ($gravatar == 'cn') {
            $cdnurl = 'cn.gravatar.com/avatar';
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        } elseif ($gravatar == 'qiniu') {
            $cdnurl = 'dn-qiniu-avatar.qbox.me/avatar';
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        } elseif ($gravatar == 'loli') {
            $cdnurl = 'gravatar.loli.net/avatar';
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        } elseif ($gravatar == 'gravatar.cn') {
            $cdnurl = 'cravatar.cn/avatar';
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        } elseif ($gravatar == 'custom') {
            $cdnurl = $set['optimization']['custom_gravatar_url'];
            $avatarUrl = self::_replaceGravatar($cdnurl, $avatarUrl);
        }
        return $avatarUrl;
    }

    static function _replaceGravatar($url, $avatarUrl)
    {
        $avatarUrl = str_replace(array(
            "secure.gravatar.com/avatar",
            "www.gravatar.com/avatar",
            "0.gravatar.com/avatar",
            "1.gravatar.com/avatar",
            "2.gravatar.com/avatar",
            "cn.gravatar.com/avatar",
            "gravatar.com/avatar"
        ), $url, $avatarUrl);

        return $avatarUrl;
    }

    static function removeDashicons()
    {
        if (!is_admin_bar_showing() && !is_customize_preview()) {
            wp_dequeue_style('dashicons');
            wp_deregister_style('dashicons');
        }
    }

    static function removeWpBlockLibraryCss()
    {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-blocks-style');
    }

    static function removeFileVersion($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
}