<?php

namespace wp_opt;
class Config
{
    static $plugin_dir;
    static $plugin_url;
    static $plugin_name;
    static $plugin_version;
    static $plugin_version_name;
    static $static_url;
    static $js_url;
    static $css_url;
    static $img_url;
    static $lib_url;
    static $set_name;
    static $plugin_server_url;
    static $is_development;

    static function init()
    {
        self::$plugin_name = 'wp_opt';
        //self::$plugin_server_url = 'https://interface.lovestu.com/api?n=wpopt';
        self::$plugin_server_url = 'https://v.lovestu.com/getNewestVersion?key=wpopt';
        self::$plugin_dir = WP_PLUGIN_DIR . '/wp-opt';
        self::$plugin_url = WP_PLUGIN_URL . '/wp-opt';
        if (is_ssl()) {
            self::$plugin_url = str_replace('http://', 'https://', self::$plugin_url);
        }
        self::$static_url = self::$plugin_url . '/static';
        self::$js_url = self::$static_url . '/js';
        self::$css_url = self::$static_url . '/css';
        self::$lib_url = self::$static_url . '/lib';
        self::$img_url = self::$static_url . '/img';
        self::$plugin_version = 26;
        self::$plugin_version_name = '2.3.1';
        self::$set_name = 'wp_opt_set';
        self::$is_development = false;
        require_once 'LoadFiles.php';

        LoadFiles::init();
    }
}

Config::init();
