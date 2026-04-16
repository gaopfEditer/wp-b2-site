<?php

namespace wp_opt;

class Plugin
{
    static function init()
    {
        add_action('admin_menu', [static::class, 'regMenu']);
        add_action('admin_init', [static::class, 'regMceBtns']);
        add_action('wp_footer', function () {
            Plugin::echoLog("www.lovestu.com/wpopt.html", '本站由WPOPT插件优化', '#f0f0f0');
        });
        global $wpopt_set;
        if ($wpopt_set['need_modify_cache']) {
            header("Cache-Control:no-cache,must-revalidate,no-store");
            header("Pragma:no-cache");
            header("Expires:-1");
            $wpopt_set['need_modify_cache'] = false;
            Options::saveSet($wpopt_set, true);
        }
    }

    static function modifyCacheHeaders($headers, $url)
    {

        if (strpos($url, 'wp-opt/static/js/wpopt-admin.js') !== false) {
            $headers['Cache-Control'] = 'max-age=0, no-cache, must-revalidate';
        }
        return $headers;
    }

    static function regMceBtns()
    {
        if (is_admin()) {
            add_filter('mce_external_plugins', [static::class, '_loadMcePlugins']);
            add_filter("mce_buttons", function ($buttons) {
                $buttons[] = 'wpopt_btn';
                return $buttons;
            });
        }
    }

    static function _loadMcePlugins($plugin_array)
    {
        $plugin_array['wpopt_btn'] = Config::$js_url . '/mce-plugins.js?v=' . Config::$plugin_version;
        return $plugin_array;
    }

    static function regMenu()
    {
        global $wpopt_set;

        if ($wpopt_set['need_update'] === true) {
            $menu_html = '<span class="awaiting-mod">new</span>';
        } else {
            $menu_html = '';
        }
        add_menu_page('WPOPT', 'WPOPT' . $menu_html, 'administrator', 'wpopt_set', function () {
            require_once Config::$plugin_dir . '/pages/admin-set.php';
        }, 'dashicons-icon-wpopt');
    }

    static function flushRules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    static function echoLog($content, $title = 'WPOPT', $content_background = '#444')
    {
        echo '<script>console.log("\n %c ' . $title . ' %c ' . $content . '", "color:#fff;background:#3983e2;padding:5px 0;", "color:#eee;background:' . $content_background . ';padding:5px 10px;");</script>';
    }

    static function getServePluginInfo()
    {
        $url = Config::$plugin_server_url . '&url=' . get_bloginfo('url', 'display') . '&v=' . Config::$plugin_version;
        $request = new \WP_Http;
        $result = $request->request($url);
        if (!is_wp_error($result)) {
            $json = json_decode($result['body'], true);
            if ($json['code'] == 200) {
                $data['version'] = $json['data']['dev_version'];
                $data['version_name'] = $json['data']['version'];
                $data['down_url'] = $json['data']['all_package_url'];
                $data['file_md5'] = $json['data']['all_package_url_md5'];
                $data['password'] = $json['data']['password'];
                return $data;
            }
        } else {
            return false;
        }
        return false;
    }

    static function imageToWebP($file_name, $save_file_name, $quality = 80, $image_type = 'image/png')
    {
        if (function_exists('imagewebp')) {
            return self::imageToWebPGD3($file_name, $save_file_name, $quality, $image_type);
        } elseif (extension_loaded('imagick')) {
            // Tools::writeLog('使用imagick');
            return self::imageToWebPImagick($file_name, $save_file_name, $quality);
        }
        // Tools::writeLog('啥也没有');
        return false;
    }

    static function imageToWebPImagick($file_name, $save_file_name, $quality)
    {
        $imagick = new \Imagick($file_name);
        $imagick->setImageFormat('webp');
        $imagick->setOption('webp:method', '6');
        $imagick->setOption('webp:quality', $quality);
        $re = $imagick->writeImage($save_file_name);
        if ($re) {
            unlink($file_name);
            return true;
        } else {
            return false;
        }
    }

    static function imageToWebPGD3($file_name, $save_file_name, $quality = 80, $image_type = 'image/png')
    {
        $img_path = $file_name;
        $img_webp_path = $save_file_name;
        if (!function_exists('imagewebp')) {
            return false;
        }
        Tools::writeLog('开始转换');
        if ($image_type == 'image/png') {
            $im = @imagecreatefrompng($img_path);
        } elseif ($image_type == 'image/jpeg') {
            $im = @imagecreatefromjpeg($img_path);
        } elseif ($image_type == 'image/jpg') {
            $im = @imagecreatefromjpeg($img_path);
        } elseif ($image_type == 'image/bmp') {
            if (function_exists('@imagecreatefrombmp')) {
                $im = @imagecreatefrombmp($img_path);
            } else {
                return false;
            }
        } else {
            return false;
        }

        if ($im == false) {
            //Tools::writeLog('没有成功');
            return false;
        }

        if (!imagewebp($im, $img_webp_path, $quality)) {
            return false;
        }
        imagedestroy($im);
        unlink($file_name);
        return true;
    }


    static function countAllUsers()
    {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM {$wpdb->users}";
        $count = $wpdb->get_var($query);
        return (int)$count;
    }

    /**
     * @param $type String subscriber  administrator
     * @return string|null
     */
    static function countUsers($type)
    {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM {$wpdb->users} u JOIN {$wpdb->usermeta} m ON u.ID = m.user_id WHERE m.meta_key = 'wp_capabilities' AND m.meta_value LIKE '%$type%'";
        $count = $wpdb->get_var($query);
        return (int)$count;
    }

    static function optimizeTable($name)
    {
        global $wpdb;
        $result = $wpdb->query("OPTIMIZE TABLE {$name}");
        return $result !== false;
    }

    static function optimizeAllTable()
    {
        global $wpdb;
        $tables = $wpdb->get_col("SHOW TABLES");
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE $table");
        }
    }

    static function getRevisionCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'";
        return $wpdb->get_var($sql);
    }

    static function getDraftCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft'";
        return $wpdb->get_var($sql);
    }

    static function getAutoDraftCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'auto-draft'";
        return $wpdb->get_var($sql);
    }

    static function getSpamCommentCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'";
        return $wpdb->get_var($sql);
    }

    /** 回收站评论
     * @return string|null
     */
    static function getTrashCommentCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'trash'";
        return $wpdb->get_var($sql);
    }

    /** 孤立元数据
     * @return string|null
     */
    static function getOrphanPostMetaCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";
        return $wpdb->get_var($sql);
    }

    /** 孤立评论元数据
     * @return string|null
     */
    static function getOrphanCommentMetaCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)";
        return $wpdb->get_var($sql);
    }

    /** 孤立关系数据
     * @return string|null
     */
    static function getOrphanRelationshipsCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)";
        return $wpdb->get_var($sql);
    }

    /** 过去临时存储
     * @return string|null
     */
    static function getTransientFeedCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'";
        return $wpdb->get_var($sql);
    }


    static function delGarbage($name)
    {
        global $wpdb;
        switch ($name) {
            case "post_revision":
                $sql = "DELETE FROM $wpdb->posts WHERE post_type = 'revision'";
                $wpdb->query($sql);
                break;
            case "post_draft":
                $sql = "DELETE FROM $wpdb->posts WHERE post_status = 'draft'";
                $wpdb->query($sql);
                break;
            case "auto_draft":
                $sql = "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'";
                $wpdb->query($sql);
                break;
            case "moderated":
                $sql = "DELETE FROM $wpdb->comments WHERE comment_approved = '0'";
                $wpdb->query($sql);
                break;
            case "spam_comment":
                $sql = "DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'";
                $wpdb->query($sql);
                break;
            case "trash_comment":
                $sql = "DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'";
                $wpdb->query($sql);
                break;
            case "orphan_post_meta":
                $sql = "DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";
                $wpdb->query($sql);
                break;
            case "orphan_comment_meta":
                $sql = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)";
                $wpdb->query($sql);
                break;
            case "orphan_relationships":
                $sql = "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)";
                $wpdb->query($sql);
                break;
            case "transient_feed":
                $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'";
                $wpdb->query($sql);
                break;
        }
    }

    static function updatePostViews($post_id, $numbers)
    {
        if (!$post_views = get_post_meta($post_id, 'views', true)) {
            $post_views = 0;
        }
        return update_post_meta($post_id, 'views', $post_views + $numbers);
    }
}