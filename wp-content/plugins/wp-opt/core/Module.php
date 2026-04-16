<?php

namespace wp_opt;

class Module
{
    static $water_font_path = '';

    static function init()
    {
        self::$water_font_path = Config::$plugin_dir . '/static/font/dingdingjinbu.ttf';
        self::watermarkModule();
    }

    static function watermarkModule()
    {
        global $wpopt_set;
        if ($wpopt_set['module_watermark_open']) {
            add_filter('wp_handle_upload', [static::class, 'watermarkModuleHandle'], 15);
        }
    }

    static function watermarkModuleHandle($upload)
    {
        global $wpopt_set;
        $file_path = $upload['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name);
        $file_ext = $file_type['ext'];
        $img_list = ['jpeg', 'jpg', 'bmp', 'png', 'webp'];
        if (in_array($file_ext, $img_list)) {
            $max_width = $wpopt_set['module_watermark_max_width'];
            $max_height = $wpopt_set['module_watermark_max_width'];
            $img_info = getimagesize($file_path);
            $img_width = $img_info[0];
            $img_height = $img_info[1];
            if ($max_width > 0 && $img_width < $max_width) {
                return $upload;
            }
            if ($max_height > 0 && $img_height < $max_height) {
                return $upload;
            }
            $watermark_position = $wpopt_set['module_watermark_position'];
            $position_type = $wpopt_set['module_watermark_position_type'];

            if ($wpopt_set['module_watermark_type'] == 'text') {
                $font_size = $wpopt_set['module_watermark_text_size'];
                $watermark_angle = $wpopt_set['module_watermark_angle'];
                $watermark_text = $wpopt_set['module_watermark_text'];
                $img_file = self::createTextWatermark($file_path, $file_ext, $watermark_text, $watermark_position, $position_type, $watermark_angle, $font_size);
                if ($img_file === false) {
                    return $upload;
                }
                file_put_contents($file_path, base64_decode($img_file));
            } else {
                $watermark_img = $wpopt_set['module_watermark_img'];
                $img_file = self::createImgWatermark($file_path, $watermark_img, $watermark_position, $position_type);
                if ($img_file === false) {
                    return $upload;
                }
                file_put_contents($file_path, base64_decode($img_file));
                return $upload;
            }
        }
        return $upload;
    }

    /**
     * @param $img_url
     * @param $watermark_img
     * @param $img_position
     * @param $position_type
     * @param $watermark_angle
     * @return false|string
     */
    static function createImgWatermark($img_url, $watermark_img, $img_position, $position_type)
    {
        if (!file_exists($img_url)) {
            return false;
        }
        $img_ext = Tools::getFileExtension($img_url);
        $img_canvas = Tools::getGDImageCanvas($img_url, $img_ext);
        if ($img_canvas === false) {
            return false;
        }

        $img_info = getimagesize($img_url);
        if ($img_info === false) {
            return false;
        }
        // 获取原始图片的宽和高
        $img_width = imagesx($img_canvas);
        $img_height = imagesy($img_canvas);
        $watermark_img_ext = Tools::getFileExtension($watermark_img);
        $img_watermark_canvas = Tools::getGDImageCanvas($watermark_img, $watermark_img_ext);
        if ($img_watermark_canvas === false) {
            return false;
        }

        $watermark_img_width = imagesx($img_watermark_canvas);
        $watermark_img_height = imagesy($img_watermark_canvas);
        $position_list = ['left_top', 'top', 'right_top', 'left_center', 'center',
            'right_center', 'left_bottom', 'bottom', 'right_bottom'
        ];
        if ($position_type == 'designate') {
            $_p_info = self::computeWatermarkImgPosition($img_height, $img_width, $img_position, $watermark_img_width, $watermark_img_height);
            $x = $_p_info[0];
            $y = $_p_info[1];
            imagecopy($img_canvas, $img_watermark_canvas, $x, $y, 0, 0, $watermark_img_width, $watermark_img_height);
        } elseif ($position_type == 'random') {
            $random_key = array_rand($position_list);
            $random_position = $position_list[$random_key];
            $_p_info = self::computeWatermarkImgPosition($img_height, $img_width, $random_position, $watermark_img_width, $watermark_img_height);
            $x = $_p_info[0];
            $y = $_p_info[1];
            imagecopy($img_canvas, $img_watermark_canvas, $x, $y, 0, 0, $watermark_img_width, $watermark_img_height);

        } elseif ($position_type == 'full') {
            foreach ($position_list as $item) {
                $_p_info = self::computeWatermarkImgPosition($img_height, $img_width, $item, $watermark_img_width, $watermark_img_height);
                $x = $_p_info[0];
                $y = $_p_info[1];
                imagecopy($img_canvas, $img_watermark_canvas, $x, $y, 0, 0, $watermark_img_width, $watermark_img_height);
            }
        }

        ob_start();
        imagepng($img_canvas);
        $img_content = ob_get_contents();
        $img_base64 = base64_encode($img_content);
        ob_end_clean();
        imagedestroy($img_canvas);
        imagedestroy($img_watermark_canvas);
        return $img_base64;
    }

    /**
     * @param $img_h int 画布
     * @param $img_w
     * @param $position
     * @param $p_w
     * @param $p_h
     * @return array
     */
    static function computeWatermarkImgPosition($img_h, $img_w, $position, $p_w, $p_h)
    {
        if ($position == 'left_top') {
            $x = 5;
            $y = 5;
        } elseif ($position == 'top') {
            $x = floor($img_w / 2) - floor($p_w / 2);
            $y = 5;
        } elseif ($position == 'right_top') {
            $x = $img_w - $p_w - 5;
            $y = 5;
        } elseif ($position == 'left_center') {
            $x = 5;
            $y = floor($img_h / 2) - floor($p_h / 2);
        } elseif ($position == 'center') {
            $x = floor($img_w / 2) - floor($p_w / 2);
            $y = floor($img_h / 2) - floor($p_h / 2);
        } elseif ($position == 'right_center') {
            $x = $img_w - $p_w - 5;
            $y = floor($img_h / 2) - floor($p_h / 2);
        } elseif ($position == 'left_bottom') {
            $x = 5;
            $y = $img_h - $p_h - 5;
        } elseif ($position == 'bottom') {
            $x = floor($img_w / 2) - floor($p_w / 2);
            $y = $img_h - $p_h - 5;
        } elseif ($position == 'right_bottom') {
            $x = $img_w - $p_w - 5;
            $y = $img_h - $p_h - 5;
        }
        return [$x, $y];
    }

    static function createTextWatermark($img_url, $img_type, $watermark_text, $text_position, $position_type, $watermark_angle, $font_size = 16)
    {
        if (!file_exists($img_url)) {
            return false;
        }
        $font_path = self::$water_font_path;
        $img_canvas = Tools::getGDImageCanvas($img_url, $img_type);
        if ($img_canvas === false) {
            return false;
        }

        $img_info = getimagesize($img_url);
        if ($img_info === false) {
            return false;
        }
        $img_width = $img_info[0];
        $img_height = $img_info[1];


        $text_color = imagecolorallocate($img_canvas, 255, 255, 255); // 白色
        $size = Tools::computeGDTextSize($font_size, $watermark_angle, $font_path, $watermark_text);
        $text_width = $size[0];
        $text_height = $size[1];

        $position_list = ['left_top', 'top', 'right_top', 'left_center', 'center',
            'right_center', 'left_bottom', 'bottom', 'right_bottom'
        ];
        if ($position_type == 'designate') {
            $_p_info = self::computeWatermarkPosition($img_height, $img_width, $text_position, $text_width, $text_height);
            $x = $_p_info[0];
            $y = $_p_info[1];
            imagettftext($img_canvas, $font_size, $watermark_angle, $x, $y, $text_color, $font_path, $watermark_text);
        } elseif ($position_type == 'random') {

            $random_key = array_rand($position_list);
            $random_position = $position_list[$random_key];
            $_p_info = self::computeWatermarkPosition($img_height, $img_width, $random_position, $text_width, $text_height);
            $x = $_p_info[0];
            $y = $_p_info[1];
            imagettftext($img_canvas, $font_size, $watermark_angle, $x, $y, $text_color, $font_path, $watermark_text);

        } elseif ($position_type == 'full') {
            foreach ($position_list as $item) {
                $_p_info = self::computeWatermarkPosition($img_height, $img_width, $item, $text_width, $text_height);
                $x = $_p_info[0];
                $y = $_p_info[1];
                imagettftext($img_canvas, $font_size, 35, $x, $y, $text_color, $font_path, $watermark_text);
            }
        }
        ob_start();
        imagepng($img_canvas);
        $img_content = ob_get_contents();
        $img_base64 = base64_encode($img_content);
        ob_end_clean();
        imagedestroy($img_canvas);
        return $img_base64;
    }

    static function computeWatermarkPosition($img_h, $img_w, $position, $p_w, $p_h)
    {
        $x = 1;
        $y = 2;
        if ($position == 'left_top') {
            $x = 5;
            $y = 5 + $p_h;
        } elseif ($position == 'top') {
            $x = floor($img_w / 2) - floor($p_w / 2);
            $y = 5 + $p_h;
        } elseif ($position == 'right_top') {
            $x = $img_w - $p_w - 5;
            $y = 5 + $p_h;
        } elseif ($position == 'left_center') {
            $x = 5;
            $y = floor($img_h / 2) + floor($p_h / 2);
        } elseif ($position == 'center') {
            $x = floor($img_w / 2) - floor($p_w / 2);
            $y = floor($img_h / 2) + floor($p_h / 2);
        } elseif ($position == 'right_center') {
            $x = $img_w - $p_w - 5;
            $y = floor($img_h / 2) + floor($p_h / 2);
        } elseif ($position == 'left_bottom') {
            $x = 5;
            $y = $img_h - $p_h;
        } elseif ($position == 'bottom') {
            $x = floor($img_w / 2) - floor($p_w / 2);
            $y = $img_h - $p_h;
        } elseif ($position == 'right_bottom') {
            $x = $img_w - $p_w - 5;
            $y = $img_h - $p_h;
        }
        return [$x, $y];
    }
}