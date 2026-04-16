<?php

namespace wp_opt;

class Tools
{
    static function delDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                $del_re = self::delDir("$dir/$file");
            } else {
                $del_re = unlink("$dir/$file");
            }
        }

        return rmdir($dir);
    }

    static function canConvertWebP()
    {
        if (extension_loaded('imagick') || function_exists('imagewebp')) {
            return true;
        }
        return false;
    }

    static function writeLog($content)
    {
        if (Config::$is_development) {
            $file = Config::$plugin_dir . "/log/log.txt";
            $content = file_get_contents($file) . "\n" . $content;
            file_put_contents($file, $content);
        }
    }

    static function isInDefaultTable($name)
    {
        // 获取WordPress数据表前缀
        global $wpdb;
        $prefix = $wpdb->prefix;
        // 创建默认数据表数组
        $defaultTables = array(
            $prefix . 'posts',
            $prefix . 'comments',
            $prefix . 'commentmeta',
            $prefix . 'links',
            $prefix . 'options',
            $prefix . 'postmeta',
            $prefix . 'terms',
            $prefix . 'termmeta',
            $prefix . 'term_relationships',
            $prefix . 'term_taxonomy',
            $prefix . 'usermeta',
            $prefix . 'users',
        );
        // 判断传入的数据表名称是否在默认数据表数组中
        return in_array($name, $defaultTables);
    }

    static function getTableUseType($name)
    {
        // 获取WordPress数据表前缀
        global $wpdb;
        $prefix = $wpdb->prefix;
        // 创建默认数据表数组
        $defaultTables = array(
            $prefix . 'posts' => '文章表',
            $prefix . 'comments' => '评论表',
            $prefix . 'commentmeta' => '评论额外信息',
            $prefix . 'links' => '链接',
            $prefix . 'options' => '配置项',
            $prefix . 'postmeta' => '文章额外信息',
            $prefix . 'terms' => '分类信息',
            $prefix . 'termmeta' => '分类额外信息',
            $prefix . 'term_relationships' => '文章、链接、分类关系信息',
            $prefix . 'term_taxonomy' => '目录、标签分类',
            $prefix . 'usermeta' => '用户额外信息',
            $prefix . 'users' => '用户表',
        );
        // 判断传入的数据表名称是否在默认数据表数组中
        if (isset($defaultTables[$name])) {
            return $defaultTables[$name];
        }
        return '';
    }

    static function getFileExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /** 创建画布
     * @param $img_url
     * @param $img_type
     * @return false|\GdImage|resource
     */
    static function getGDImageCanvas($img_url, $img_type)
    {
        if ($img_type === 'webp') {
            $img_canvas = imagecreatefromwebp($img_url);
        } elseif ($img_type === 'jpg' || $img_type === 'jpeg') {
            $img_canvas = imagecreatefromjpeg($img_url);
        } elseif ($img_type === 'png') {
            $img_canvas = imagecreatefrompng($img_url);
        } elseif ($img_type === 'bmp') {
            if (!function_exists('imagecreatefrombmp')) {
                return false;
            }
            $img_canvas = imagecreatefrombmp($img_url);
        } else {
            return false;
        }
        return $img_canvas;
    }

    /** 计算文字内容宽高
     * @param $font_size
     * @param $angle
     * @param $font_path
     * @param $text
     * @return array
     */
    static function computeGDTextSize($font_size, $angle, $font_path, $text)
    {
        $text_size = imagettfbbox($font_size, $angle, $font_path, $text);
        $text_width = $text_size[2] - $text_size[1];
        $text_height = $text_size[1] - $text_size[7];
        return [$text_width, $text_height];
    }


   static function postViewsRoundNumber($num)
    {
        if ($num >= 100000) {
            $num = round($num / 10000) . 'W+';
        } else if ($num >= 10000) {
            $num = round($num / 10000, 1) . 'W+';
        } else if ($num >= 1000) {
            $num = round($num / 1000, 1) . 'K+';
        }
        return $num;
    }
}