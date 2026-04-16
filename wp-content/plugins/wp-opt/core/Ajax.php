<?php

namespace wp_opt;

class Ajax
{
    static function init()
    {
        add_action('wp_ajax_' . Config::$plugin_name, array(static::class, 'ajax'));
        add_action('wp_ajax_nopriv_' . Config::$plugin_name, array(static::class, 'ajax'));
    }

    static function ajax()
    {
        $fun_name = $_POST['fun'];
        if (!isset($fun_name)) {
            $fun_name = $_GET['fun'];
            if (!isset($fun_name)) {
                $fun_name = 'error';
            }
        }
        call_user_func(array(static::class, $fun_name));
    }

    static private function setAjaxDataAndDie($code, $msg = '', $data = null)
    {
        $_data['code'] = $code;
        $_data['msg'] = $msg;
        if ($data != null) {
            $_data['data'] = $data;
        }
        wp_die(json_encode($_data, JSON_UNESCAPED_UNICODE));
    }

    static private function validationParameters($arr)
    {
        $parameter = [];
        foreach ($arr as $item) {
            if ($item[1] == 1) {
                if (!isset($_POST[$item[0]])) {
                    self::setAjaxDataAndDie(500, '参数错误');
                }
                $parameter[$item[0]] = $_POST[$item[0]];
            } else {
                if (!isset($_GET[$item[0]])) {
                    self::setAjaxDataAndDie(500, '参数错误');
                }
                $parameter[$_GET[0]] = $_GET[$item[0]];
            }
        }

        return $parameter;
    }

    static private function needLogin($user_type = 'user')
    {
        if (!is_user_logged_in()) {
            self::setAjaxDataAndDie(500, '无权限访问');
        }
        if ($user_type == 'admin') {
            if (!WordPress::isAdmin()) {
                self::setAjaxDataAndDie(500, '权限不足');
            }
        }
    }

    static private function needNotLogin()
    {
        if (WordPress::isLogin()) {
            self::setAjaxDataAndDie(500, '您已经登录');
        }
    }

    static function saveSet()
    {
        self::needLogin('admin');
        $arr = [['data', 1]];
        $arr = self::validationParameters($arr);
        $re = Options::saveSet($arr['data']);
        $code = 500;
        if ($re) {
            $code = 200;
            global $wpopt_set;
            $wpopt_set = Options::getOptions();
            if ($wpopt_set['page_add_html'] === true) {
                flush_rewrite_rules();
            }
        }

        self::setAjaxDataAndDie($code);
    }

    static function checkUpdateOnSet()
    {
        global $wpopt_set;
        self::needLogin('admin');
        $data = Plugin::getServePluginInfo();
        $wpopt_set['last_check_time'] = time();
        $re_data['version'] = '';
        $re_data['can_update'] = false;
        $wpopt_set['wx_password_remote'] = $data['password'];
        Options::saveSet(base64_encode(json_encode($wpopt_set)));
        if ($data !== false) {
            $re_data['version_name'] = $data['version_name'];
            if ($data['version'] > Config::$plugin_version) {
                $wpopt_set['need_update'] = true;
                $re_data['can_update'] = true;
            }
            self::setAjaxDataAndDie(200, 'success', $re_data);
        } else {
            self::setAjaxDataAndDie(500, '检测失败');
        }
    }

    static function startUpdate()
    {
        /*$dir = WP_PLUGIN_DIR . '/wpopt_tmp';
        var_dump(scandir($dir));
        self::setAjaxDataAndDie(500);*/
        if (Config::$is_development) {
            self::setAjaxDataAndDie(500, '当前为开发模式');
        }
        $plugin_info = Plugin::getServePluginInfo();
        if ($plugin_info !== false) {
            if ($plugin_info['version'] > Config::$plugin_version) {
                //准备更新
                if (isset($plugin_info['down_url'])) {
                    $url = $plugin_info['down_url'];
                    $dir = WP_PLUGIN_DIR . '/wpopt_tmp';
                    if (!is_dir($dir)) {
                        mkdir($dir);
                    }
                    $remote_file = fopen($url, 'r');
                    $file_name = time() . '.zip';
                    $fh = fopen($dir . "/{$file_name}", 'w');
                    while (!feof($remote_file)) {
                        $output = fread($remote_file, 8192);
                        fwrite($fh, $output);
                    }
                    fclose($remote_file);
                    $file_path = $dir . '/' . $file_name;
                    if (strtoupper(md5_file($file_path)) != $plugin_info['file_md5']) {
                        Tools::delDir($dir);
                        self::setAjaxDataAndDie(500, '更新失败，文件校验失败，请手动更新');
                    }
                    $zip = new \ZipArchive();
                    if ($zip->open($file_path) === true) {
                        $zip->extractTo(WP_PLUGIN_DIR);
                        $zip->close();
                    } else {
                        Tools::delDir($dir);
                        self::setAjaxDataAndDie(500, '解压失败，可能没有权限');
                    }
                    global $wpopt_set;
                    $wpopt_set = Options::getOptions();
                    $wpopt_set['need_update'] = false;
                    $wpopt_set['need_modify_cache'] = true;
                    Options::saveSet($wpopt_set, true);
                    self::setAjaxDataAndDie(200, '更新成功，请刷新！');
                } else {
                    self::setAjaxDataAndDie(500, '更新失败，未获取到下载地址');
                }
            }
        }
    }

    static function uploadImage()
    {
        self::needLogin();
        if (empty($_FILES['file'])) {
            self::setAjaxDataAndDie(500);
        }

        $file = $_FILES['file'];
        if (!in_array($file['type'], array('image/jpeg', 'image/webp', 'image/png', 'image/gif', 'image/bmp'))) {
            wp_send_json_error('上传的文件不是图片');
        }
        $attachment_id = media_handle_upload('file', 0);
        // 返回上传结果
        if (is_wp_error($attachment_id)) {
            self::setAjaxDataAndDie(500, $attachment_id->get_error_message());
        } else {
            self::setAjaxDataAndDie(200, '上传成功', wp_get_attachment_url($attachment_id));
        }
    }

    static function checkUpdate()
    {
        global $wpopt_set;

        $wpopt_set = Options::getOptions();

        $time = time();
        $last_time = $wpopt_set['last_check_time'];
        if ($time - $last_time < 3600) {
            self::setAjaxDataAndDie(200, 'time not');
        }
        $plugin_info = Plugin::getServePluginInfo();
        $wpopt_set['last_check_time'] = $time;
        if ($plugin_info !== false) {
            if ($plugin_info['version'] > Config::$plugin_version) {
                $wpopt_set['need_update'] = true;
            } else {
                $wpopt_set['need_update'] = false;
            }
        }
        $wpopt_set['wx_password_remote'] = $plugin_info['password'];
        Options::saveSet($wpopt_set, true);
        self::setAjaxDataAndDie(200, 'check success');
    }

    static function flushRewriteRules()
    {
        self::needLogin();
        flush_rewrite_rules();
        self::setAjaxDataAndDie(200, 'success');
    }

    static function getUserCount()
    {
        self::needLogin('admin');
        $par = [['type', 1]];
        $par = self::validationParameters($par);
        $data['count'] = Plugin::countUsers($par['type']);
        self::setAjaxDataAndDie(200, 'success', $data);
    }

    static function getAllUserInfoCount()
    {
        self::needLogin('admin');
        $data['subscriber_count'] = 0;
        if (Plugin::countUsers('subscriber') !== null) {
            $data['subscriber_count'] = Plugin::countUsers('subscriber');
        }
        $data['admin_count'] = Plugin::countUsers('administrator');
        self::setAjaxDataAndDie(200, 'success', $data);

    }

    static function getAllTableInfo()
    {
        global $wpdb;
        $tables = $wpdb->get_col('SHOW TABLES');
        $table_info = [];
        $self_arr = [];
        $other_arr = [];
        $db_size = 0;
        foreach ($tables as $table) {

            $info = $wpdb->get_row("SELECT table_rows,Engine, TABLE_COLLATION, data_length + index_length AS size FROM information_schema.TABLES WHERE table_name = '$table' AND table_schema = '{$wpdb->dbname}'", ARRAY_A);

            $_temp['name'] = $table;
            $_temp['size'] = number_format($info['size'] / 1048576, 2);
            $db_size += $info['size'];
            $_temp['row'] = $info['table_rows'];
            $_temp['db_use_type'] = Tools::getTableUseType($table);
            $_temp['engine'] = $info['Engine'];
            $_temp['character'] = $info['TABLE_COLLATION'];

            if (Tools::isInDefaultTable($table)) {
                $_temp['is_self'] = true;
                $self_arr[] = $_temp;
            } else {
                $_temp['is_self'] = false;
                $other_arr[] = $_temp;
            }
        }

        $data['db_table_list_info'] = array_merge($self_arr, $other_arr);
        $data['db_size'] = number_format($db_size / 1048576, 2);

        self::setAjaxDataAndDie(200, 'success', $data);
    }

    static function optimizeTable()
    {
        self::needLogin('admin');
        $arr = [['name', 1]];
        $arr = self::validationParameters($arr);
        $result = Plugin::optimizeTable($arr['name']);
        if ($result === false) {
            self::setAjaxDataAndDie(500, '优化失败');
        } else {
            self::setAjaxDataAndDie(200, '优化成功');
        }
    }

    static function optimizeAllTable()
    {
        self::needLogin('admin');
        Plugin::optimizeAllTable();
        self::setAjaxDataAndDie(200, '优化完成');
    }

    static function getDbGarbageInfo()
    {
        self::needLogin('admin');
        $data['post_revision'] = Plugin::getRevisionCount();
        $data['post_draft'] = Plugin::getDraftCount();
        $data['auto_draft'] = Plugin::getAutoDraftCount();
        $data['spam_comment'] = Plugin::getSpamCommentCount();
        $data['trash_comment'] = Plugin::getTrashCommentCount();
        $data['orphan_post_meta'] = Plugin::getOrphanPostMetaCount();
        $data['orphan_comment_meta'] = Plugin::getOrphanCommentMetaCount();
        $data['orphan_relationships'] = Plugin::getOrphanRelationshipsCount();
        $data['transient_feed'] = Plugin::getTransientFeedCount();

        $list = [
            ['name' => 'post_revision',
                'cn_name' => '修订文章',
                'count' => $data['post_revision'],
                'note' => '帖子修订版本内容。WordPress自动保存下的修订内容。推荐删除'
            ],
            ['name' => 'post_draft',
                'cn_name' => '草稿文章',
                'count' => $data['post_draft'],
                'note' => '文章草稿'
            ],
            ['name' => 'auto_draft',
                'cn_name' => '自动草稿',
                'count' => $data['auto_draft'],
                'note' => '写文章的时候自动保存的草稿，推荐删除'
            ],
            ['name' => 'spam_comment',
                'cn_name' => '垃圾评论',
                'count' => $data['spam_comment'],
                'note' => '垃圾评论，跟评论管理页面垃圾评论数量一致。推荐删除'
            ],
            ['name' => 'trash_comment',
                'cn_name' => '回收站评论',
                'count' => $data['trash_comment'],
                'note' => '回收站评论，跟评论管理页面回收站评论数量一致。推荐删除'
            ],
            ['name' => 'orphan_post_meta',
                'cn_name' => '孤立文章字段',
                'count' => $data['orphan_post_meta'],
                'note' => '删除文章的时候，残留的字段内容。推荐删除。'
            ],
            ['name' => 'orphan_comment_meta',
                'cn_name' => '孤立评论字段',
                'count' => $data['orphan_comment_meta'],
                'note' => '删除评论的时候，残留的字段内容。推荐删除。'
            ],
            ['name' => 'orphan_relationships',
                'cn_name' => '孤立关系字段',
                'count' => $data['orphan_relationships'],
                'note' => '删除文章后残留的文章关联标签、分类等信息。推荐删除。'
            ],
            ['name' => 'transient_feed',
                'cn_name' => '过期缓存',
                'count' => $data['transient_feed'],
                'note' => 'WP自带缓存功能，留下的没有被自动清理的过期缓存。推荐删除。'
            ],
        ];

        self::setAjaxDataAndDie(200, '', $list);
    }

    static function delGarbage()
    {
        self::needLogin('admin');
        $arr = [['name', 1]];
        $arr = self::validationParameters($arr);
        Plugin::delGarbage($arr['name']);
        self::setAjaxDataAndDie(200, '');
    }

    static function isAddWeChat()
    {
        global $wpopt_set;
        if ($wpopt_set['wx_password_remote'] == $wpopt_set['wx_password_set'] && $wpopt_set['wx_password_remote'] != '') {
            self::setAjaxDataAndDie(200, 'yes');
        } else {
            self::setAjaxDataAndDie(200, 'no');
        }
    }

    static function updatePass()
    {
        global $wpopt_set;
        $arr = [['pass', 1]];
        $arr = self::validationParameters($arr);
        $plugin_info = Plugin::getServePluginInfo();
        if ($plugin_info === false) {
            self::setAjaxDataAndDie(500, '无法连接远程服务器，数据获取失败');
        }
        if ($plugin_info['password'] === $arr['pass']) {
            $wpopt_set['wx_password_set'] = $arr['pass'];
            $wpopt_set['wx_password_remote'] = $arr['pass'];
            Options::saveSet($wpopt_set, true);
            self::setAjaxDataAndDie(200, 'success');
        } else {
            self::setAjaxDataAndDie(500, '密码验证错误');
        }
    }

    static function waterTest()
    {
        $arr = [['position', 1], ['text', 1], ['watermark_img', 1], ['font_size', 1], ['position_type', 1], ['watermark_angle', 1], ['watermark_type', 1]];
        $arr = self::validationParameters($arr);
        $img_url = Config::$plugin_dir . '/static/img/watermark-test.webp';
        if ($arr['watermark_type'] == 'text') {
            $img_base64 = Module::createTextWatermark($img_url, 'webp', $arr['text'], $arr['position'], $arr['position_type'], $arr['watermark_angle'], $arr['font_size']);
        } else {
            $img_base64 = Module::createImgWatermark($img_url, $arr['watermark_img'], $arr['position'], $arr['position_type']);
        }
        if ($img_base64 === false) {
            self::setAjaxDataAndDie(500, '加水印失败');
        } else {
            self::setAjaxDataAndDie(200, '获取成功，请自行查看图片是否有水印', $img_base64);
        }
    }

    static function hasDataTable()
    {
        global $wpdb;
        $arr = [['name', 1]];
        $arr = self::validationParameters($arr);
        $table_name = $wpdb->prefix . $arr['name'];
        $re = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($re != $table_name) {

            self::setAjaxDataAndDie(500);
        } else {
            self::setAjaxDataAndDie(200);
        }
    }

    static function createSeoDb()
    {
        self::needLogin('admin');
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpopt_seo';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (id mediumint(9) NOT NULL AUTO_INCREMENT,post_id bigint NOT NULL,status tinytext NOT NULL,time DATETIME NOT NULL,PRIMARY KEY  (id)) $charset_collate;";
        $re = $wpdb->query($sql);
        if ($re) {
            self::setAjaxDataAndDie(200);
        } else {
            self::setAjaxDataAndDie(500, '创建数据库失败');
        }
    }

    static function delSeoTable()
    {
        self::needLogin('admin');
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpopt_seo';
        $sql = "DROP TABLE $table_name";
        $re = $wpdb->query($sql);
        if ($re) {
            self::setAjaxDataAndDie(200);
        } else {
            self::setAjaxDataAndDie(500, '删除失败');
        }
    }

    static function postViews()
    {
        $arr = [['post_id', 1]];
        $arr = self::validationParameters($arr);
        global $wpopt_set;
        if (!$wpopt_set['module_post_views_open']) {
            self::setAjaxDataAndDie(500, '未开启功能');
        }
        if ($wpopt_set['module_post_views_need_login']) {
            if (get_current_user_id() == 0) {
                self::setAjaxDataAndDie(500, '登录后才计数');
            }
        }
        $post_id = $arr['post_id'];
        $number = $wpopt_set['module_post_views_add_number'];
        Plugin::updatePostViews($post_id, $number);
        self::setAjaxDataAndDie(200, 'success', $post_id);
    }

    static function hasWpPostViews()
    {
        self::setAjaxDataAndDie(200, '', file_exists(WP_PLUGIN_DIR . '/wp-postviews/wp-postviews.php'));
    }
}