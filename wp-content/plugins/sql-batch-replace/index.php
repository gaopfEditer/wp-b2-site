<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2023-10-12 12:07:22
 * @LastEditTime: 2023-11-06 21:50:09
 * Plugin Name: 数据库批量替换
 * Version: 1.3
 * Description: 数据一键批量替换小工具，适用于网站换域名、加证书后网址改为https、更换云储存后批量更换媒体链接等操作
 * Plugin URI: https://www.zibll.com
 * Author: 子比主题老唐-Qinver
 * Author URI: https://www.zibll.com
 * Text Domain: sql-batch-replace
 * Domain Path: /languages
 * @Read me : 本工具为免费开源工具，您可以自由使用和分享，如您需要分享或二次开发，请务必保留并在显眼处体现作者名，以及www.zibll.com的网站
 * @Read me : 不要用于任何商业用途，否则将依法追究相关责任，谢谢合作！
 */

// 禁止直接访问
if (!defined('ABSPATH')) {
    exit;
} 

// 判断是否为后台
if (is_admin()) {
    define('SQL_REPLACE_TEXT_DOMAIN', 'sql-batch-replace');
    define('SQL_REPLACE_PLUGIN_BASENAME', plugin_basename(__FILE__));
    define('SQL_REPLACE_PATH', dirname(__FILE__));
    define('SQL_REPLACE_URL', plugins_url('', __FILE__));
    define('SQL_REPLACE_VERSION', '1.0');

    // 加载语言包
    load_plugin_textdomain(SQL_REPLACE_TEXT_DOMAIN, false, (dirname(plugin_basename(__FILE__))) . '/languages/');

    require_once SQL_REPLACE_PATH . '/func.php';
    new zib\sql\SqlReplace();
}
