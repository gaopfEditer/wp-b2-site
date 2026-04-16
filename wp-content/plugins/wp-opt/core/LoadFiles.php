<?php

namespace wp_opt;

class LoadFiles
{
    static function init()
    {
        $core_dir = Config::$plugin_dir . '/core';
        require_once "$core_dir/Options.php";
        require_once "$core_dir/Plugin.php";
        require_once "$core_dir/WordPress.php";
        require_once "$core_dir/Ajax.php";
        require_once "$core_dir/Functions.php";
        require_once "$core_dir/Tools.php";
        require_once "$core_dir/fun/funEnhance.php";
        require_once "$core_dir/Module.php";

        global $wpopt_set;
        $wpopt_set = Options::getOptions();
        Ajax::init();
        Plugin::init();
        Functions::init();
        Module::init();
        add_action('admin_enqueue_scripts', [static::class, '_loadFileOnAdmin']);
    }

    static function _loadFileOnAdmin($hook)
    {
        WordPress::loadCss('wpopt-admin', 'admin.css');
        wp_localize_script('jquery', 'wpopt', Options::getAdminSet($hook));
        WordPress::loadJS('wpopt-admin', 'wpopt-admin.js', true, [], true);
        if ($hook == 'toplevel_page_wpopt_set') {
            WordPress::loadJS('wpopt-set', 'main.min.js', true, [], true);
            WordPress::loadCss('wpopt-set', 'main.css');
        }
        if ($hook == 'post.php' || $hook == 'post-new.php') {
            WordPress::loadJS('wpopt-editor-power', 'editor-power.min.js', true, [], true);
        }
    }
}