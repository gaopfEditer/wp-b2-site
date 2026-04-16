<?php namespace B2\Modules\Templates\Modules;

use B2\Modules\Common\Post;

class Sliders{

    public function init($data,$i){
        $type = str_replace('-','_',$data['slider_type']);
        $data['slider_gap'] = isset($data['slider_gap']) ? $data['slider_gap'] : B2_GAP;

        return self::$type($data,$i);
    }

    /**
     * 幻灯模块1
     *
     * @param array $data 设置项参数
     *
     * @return string 幻灯的html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function slider_1($data,$i){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);
        
        $post_data = self::slider_data($data);

        if(!$post_data) return;

        $html = '';

        foreach ($post_data as $k => $v) {
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$data['width'],
                'height'=>$data['slider_height'],
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);

            $html .= '<div class="slider-1-carousel slider-height" style="'.($data['slider_width'] === '0' ? 'width:100%' : 'width:'.$data['width'].'px').';max-width:100%;">
                    <div class="slider-in slider-info b2-radius" style="height:0;padding-top:'.round($data['slider_height']/$data['width']*100,6).'%;max-width:100%">
                        '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).'
                        <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                        '.($data['slider_show_title'] ? $cat : '').'
                    </div>
            </div>';
        }

        //幻灯的设置项
        $settings = apply_filters('b2_slider_1_settings',array(
            'wrapAround'=>true,
            'fullscreen'=>true,
            'autoPlay'=> (int)$data['slider_speed'],
            "imagesLoaded"=> true,
            "freeScroll"=>false,
            "prevNextButtons"=>$data['slider_width'] === '1' ? false : true,
            'pageDots'=> $data['slider_width'] === '1' ? true : false
            //'groupCells'=> true,
            //'groupCells'=> 4
        ));

        $settings = json_encode($settings,true);

        $slider_setting = "data-flickity='".$settings."'";

        return '<div class="slider-1 carousel slider-height box b2-radius '.($data['slider_width'] === '0' ? 'slider-type-width' : '').' '.($data['slider_show_title'] ? 'slider-show-title' : 'show-title-none').'" '.$slider_setting.' style="'.((int)$data['slider_width'] == 1 || $data['show_widget'] ? 'width:'.$data['width'].'px' : 'width:100%').';max-width:100%">
            '.$html.'
        </div>';
    }

    /**
     * 幻灯模块2
     *
     * @param array $data 设置项参数
     *
     * @return string 幻灯的html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function slider_2($data,$i){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $post_data = self::slider_data($data);

        if(!$post_data) return;
    
        $widget_width = round(($data['width'] -$data['slider_gap']) *0.3,6);

        $c_left = $widget_width*2;
        $c_right = $widget_width;

        $last_arr = array_slice($post_data,-2,2);

        //幻灯的设置项
        $settings = apply_filters('b2_slider_2_settings',array(
            'wrapAround'=>true,
            'fullscreen'=>true,
            'autoPlay'=> (int)$data['slider_speed'],
            "imagesLoaded"=> true,
            "prevNextButtons"=>false,
            'pageDots'=> true
            //'groupCells'=> true,
            //'groupCells'=> 4
        ));

        $settings = json_encode($settings,true);

        $slider_setting = "data-flickity='".$settings."'";

        $css = '
        <style>
        .slider-2-bottom-'.$i.' > div + div{
            margin-top:'.$data['slider_gap'].'px;
        }
        </style>
        ';
        $html_slider = $css.'<div class="slider-in-out box b2-radius" style="width:'.$c_left.'%;margin-right:'.$data['slider_gap'].'px;"><div class="slider-in-out-row" style="height:100%;width:100%;"><div class="slider-in carousel b2-radius" '.$slider_setting.' style="width:100%">';

        //获取前几篇文章
        $_i = 0;
        $count_post = count($post_data);
        foreach($post_data as $k => $v){
            $_i++;
            if($_i > ($count_post - 2) ) break;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round($c_left,0),
                'height'=>$data['slider_height'],
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);

            $html_slider .= '
            <div class="slider-2-carousel slider-height" style="max-width:100%;height:100%">
                <div class="slider-info b2-radius">
                    <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                    '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? 
                    $cat
                     : '').'
                </div>
            </div>';
        }
        
        $html_slider .= '</div></div></div>';

        //获取后2篇文章
        $html_last = '<div class="slider-2-bottom slider-2-bottom-'.$i.'" style="width:'.$c_right.'%">';
        $h = round(($data['slider_height']-$data['slider_gap'])/2,6);
        foreach($last_arr as $k => $v){
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round($widget_width,0),
                'height'=>round($h,0),
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_last .= '
            <div class="slider-in b2-radius box">
                <div class="slider-2-right-item slider-height" style="height:0;padding-top:'.round($h/$widget_width*100,6).'%">
                    <div class="slider-info b2-radius">
                        <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                        '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                    </div>
                </div>
            </div>';
        }
        $html_last .= '</div>';

        return '<div class="slider-2  '.($data['slider_show_title'] ? 'slider-show-title' : 'show-title-none').'" style="width:'.$data['width'].'px;max-width:100%">'.$html_slider.$html_last.'</div>';

    }

    /**
     * 幻灯模块3
     *
     * @param array $data 设置项参数
     *
     * @return string 幻灯的html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function slider_3($data,$i){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $post_data = self::slider_data($data);

        if(!$post_data) return;

        $last_arr = array_slice($post_data,-4,4);

        //幻灯的设置项
        $settings = apply_filters('b2_slider_3_settings',array(
            'wrapAround'=>true,
            'fullscreen'=>true,
            'autoPlay'=> (int)$data['slider_speed'],
            "imagesLoaded"=> true,
            "prevNextButtons"=>false,
            'pageDots'=> true
            //'groupCells'=> true,
            //'groupCells'=> 4
        ));

        $settings = json_encode($settings,true);

        $slider_setting = "data-flickity='".$settings."'";

        $css = '<style>
        .slider-3-'.$i.' .slider-3-bottom > .slider-3-item + .slider-3-item{
            margin-left:'.$data['slider_gap'].'px;
        }</style>';

        $html_slider = $css.'<div class="carousel slider-height b2-radius box" '.$slider_setting.' style="height:0;padding-top:'.round($data['slider_height']/$data['width']*100,6).'%;max-width:100%;margin-bottom:'.$data['slider_gap'].'px;">';

        //获取前几篇文章
        $_i = 0;
        $count_post = count($post_data);
        foreach($post_data as $k => $v){
            $_i++;
            if($_i > ($count_post - 4) ) break;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$data['width'],
                'height'=>$data['slider_height'],
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_slider .= '
            <div class="slider-3-carousel slider-height">
                <div class="slider-info slider-in b2-radius">
                    <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                    '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                </div>
            </div>';
        }
        
        $html_slider .= '</div>';

        $c_width = (($data['width'] - $data['slider_gap']*3)/4)/($data['width']-$data['slider_gap']*3)*100;

        //获取后4篇文章
        $html_last = '<div class="slider-3-bottom">';
        $h = round(($data['slider_height']-$data['slider_gap'])/2.6,0);
        $_i = 0;
        foreach($last_arr as $k => $v){
            $_i++;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round(($data['width']-$data['slider_gap']*3)/4,0),
                'height'=>$h,
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_last .= '
            <div class="slider-3-box'.$_i.' slider-3-item slider-height b2-radius box" style="width:'.$c_width.'%">
                <div class="slider-in" style="width:100%;height:0;padding-top:'.round($h/(($data['width'] - $data['slider_gap']*3)/4)*100,6).'%">
                    <div class="slider-info slider-in b2-radius">
                        <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                        '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                    </div>
                </div>
            </div>';
        }
        $html_last .= '</div>';

        return '<div class="slider-3 slider-3-'.$i.'  '.($data['slider_show_title'] ? 'slider-show-title' : 'show-title-none').'">'.$html_slider.$html_last.'</div>';
    }

    /**
     * 幻灯模块4
     *
     * @param array $data 设置项参数
     *
     * @return string 幻灯的html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function slider_4($data,$i){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $post_data = self::slider_data($data);

        if(!$post_data) return;
        
        $last_arr = array_slice($post_data,-5,5);

        //幻灯的设置项
        $settings = apply_filters('b2_slider_4_settings',array(
            'wrapAround'=>true,
            'fullscreen'=>true,
            'autoPlay'=> (int)$data['slider_speed'],
            'imagesLoaded'=> true,
            "prevNextButtons"=>false,
            'pageDots'=> true
            //'groupCells'=> true,
            //'groupCells'=> 4
        ));

        $settings = json_encode($settings,true);

        $slider_setting = "data-flickity='".$settings."'";

        $s_width = round($data['width']/4,10);
        $first_width = ($data['width']-$s_width)/$data['width'];
        $second_width = 0.25;

        $css = '
            <style>
            .slider-4-'.$i.' .slider-row-2 .slider-4-item + .slider-4-item .slider-in,.slider-4-'.$i.' .slider-row-1 .slider-4-item .slider-in{
                    margin-left:'.$data['slider_gap'].'px;
                }
            </style>
        ';

        $html_slider = $css.'<div class="slider-row-1"><div class="slider-height slider-row-1-in" style="width:'.(round($first_width,6)*100).'%;">
        <div class="slider-in b2-radius" style="height:0;padding-top:'.round(($data['slider_height']/(($s_width*3)+($data['slider_gap']*2)))*100,6).'%">
        <div class="slider-4-row carousel b2-radius box" '.$slider_setting.'>';

        //获取前几篇文章
        $_i = 0;
        $count_post = count($post_data);
        foreach($post_data as $k => $v){
            $_i++;
            if($_i > ($count_post - 5) ) break;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round((($s_width*3)+($data['slider_gap']*2)),0),
                'height'=>$data['slider_height'],
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_slider .= '
            <div class="slider-4-carousel slider-height">
                <div class="slider-info b2-radius">
                    <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                    '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                </div>
            </div>';
        }
        
        $html_slider .= '</div></div></div>';

        //获取后4篇文章
        $html_last = '';
        $h = ($data['slider_height']-$data['slider_gap'])/3;
        $_i = 0;
        $count = count($last_arr);
        $_w = '';
        foreach($last_arr as $k => $v){
            $_i++;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round($s_width,0),
                'height'=>$_i == 1 ? $data['slider_height'] : round($h,0),
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_last .= '
            <div class="slider-4-box'.$_i.' slider-4-item slider-height" style="width:'.(round($second_width,6)*100).'%">
                <div class="slider-in b2-radius box" style="'.($_i == 1 ? '' : 'height:0;padding-top:'.round(($h/$s_width*100),6).'%').'">
                    <div class="slider-info b2-radius">
                        <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                        '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                    </div>
                </div>
            </div>';
            if($_i == 1){
                $html_last .= '</div><div class="slider-row-2" style="margin-top:'.$data['slider_gap'].'px;">';
            }
            if($count === $_i){
                $html_last .= '</div>';
            }
        }

        return '<div class="slider-4 slider-4-'.$i.'  '.($data['slider_show_title'] ? 'slider-show-title' : 'show-title-none').'" style="width:'.$data['width'].'px">'.$html_slider.$html_last.'</div>';
    }

    /**
     * 幻灯模块5
     *
     * @param array $data 设置项参数
     *
     * @return string 幻灯的html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function slider_5($data,$i){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $post_data = self::slider_data($data);

        if(!$post_data) return;
    
        $widget_width = round($data['width']*0.3,0);

        $c_left = round(($data['width']-$widget_width*2)/$data['width']*100,6);
        $c_right = round($widget_width*2/$data['width']*100,6);

        $last_arr = array_slice($post_data,-4,4);

        //幻灯的设置项
        $settings = apply_filters('b2_slider_5_settings',array(
            'wrapAround'=>true,
            'fullscreen'=>true,
            'autoPlay'=> (int)$data['slider_speed'],
            "imagesLoaded"=> true,
            "prevNextButtons"=>false,
            'pageDots'=> true
            //'groupCells'=> true,
            //'groupCells'=> 4
        ));

        $settings = json_encode($settings,true);

        $slider_setting = "data-flickity='".$settings."'";

        $css = '
        <style>
        .slider-5-'.$i.' .slider-5-bottom .slider-in:nth-child(1),.slider-5-'.$i.' .slider-5-bottom .slider-in:nth-child(2){
            margin-bottom:'.$data['slider_gap'].'px;
        }
        .slider-5-'.$i.' .slider-5-bottom .slider-height{
            margin-right:'.$data['slider_gap'].'px;
        }
        .slider-5-'.$i.' .slider-5-bottom{
            margin-right:-'.$data['slider_gap'].'px;
        }
        </style>
        ';
        $html_slider = $css.'<div class="slider-in-out" style="width:'.$c_left.'%;margin-right:'.$data['slider_gap'].'px;"><div class="slider-in-out-row" style="height:100%;width:100%;"><div class="slider-in carousel b2-radius box" '.$slider_setting.' style="width:100%">';

        //获取前几篇文章
        $_i = 0;
        $count_post = count($post_data);
        foreach($post_data as $k => $v){
            $_i++;
            if($_i > ($count_post - 4) ) break;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round($data['width'] - $widget_width - $data['slider_gap'],0),
                'height'=>$data['slider_height'],
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_slider .= '
            <div class="slider-5-carousel slider-height" style="max-width:100%;height:100%">
                <div class="slider-info b2-radius">
                    <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                    '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? 
                    $cat
                     : '').'
                </div>
            </div>';
        }
        
        $html_slider .= '</div></div></div>';

        //获取后4篇文章
        $html_last = '<div class="slider-5-bottom" style="width:'.$c_right.'%">';
        $h = round(($data['slider_height']-$data['slider_gap'])/2,0);
        foreach($last_arr as $k => $v){
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$widget_width,
                'height'=>$h,
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_last .= '
            <div class="slider-in b2-radius">
                <div class="slider-5-right-item slider-height" style="height:0;padding-top:'.round($h/$widget_width*100,6).'%">
                    <div class="slider-info b2-radius box">
                        <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                        '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                    </div>
                </div>
            </div>';
        }
        $html_last .= '</div>';

        return '<div class="slider-5 slider-5-'.$i.'  '.($data['slider_show_title'] ? 'slider-show-title' : 'show-title-none').'" style="width:'.$data['width'].'px;max-width:100%">'.$html_slider.$html_last.'</div>';
    }

    /**
     * 幻灯模块6
     *
     * @param array $data 设置项参数
     *
     * @return string 幻灯的html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function slider_6($data,$i){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $post_data = self::slider_data($data);

        $last_arr = array_slice($post_data,-2,2);
        $first_arr = array_slice($post_data,0,2);

        //幻灯的设置项
        $settings = apply_filters('b2_slider_6_settings',array(
            'wrapAround'=>true,
            'fullscreen'=>true,
            'autoPlay'=> (int)$data['slider_speed'],
            'imagesLoaded'=> true,
            "prevNextButtons"=>false,
            'pageDots'=> true
            //'groupCells'=> true,
            //'groupCells'=> 4
        ));

        $h = ($data['slider_height']-$data['slider_gap'])/2;
        $w = ($data['width']-($data['slider_gap']*2))/4;

        //获取前两篇篇文章
        $html_first = '<div class="slider-6-left" style="width:'.round($w/$data['width']*100,6).'%">';
        $_i = 0;
        foreach($first_arr as $k => $v){
            $_i++;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round($w,0),
                'height'=>round($h,0),
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_first .= '
            <div class="slider-6-box'.$_i.' slider-6-item b2-radius box slider-in" style="height:0;padding-top:'.round($h/$w*100,6).'%">
                <div class="slider-info b2-radius">
                    <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                    '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                </div>
            </div>';
        }

        $html_first .= '</div>';

        //获中间几篇文章
        $settings = json_encode($settings,true);

        $slider_setting = "data-flickity='".$settings."'";

        $html_slider = '<div class="slider-6-middle" style="width:'.round(($w*2)/$data['width']*100,6).'%"><div class="slider-6-in b2-radius box" style="width:100%;height:100%"><div class="carousel" '.$slider_setting.'>';

        $_i = 0;
        $count_post = count($post_data);
        foreach($post_data as $k => $v){
            $_i++;
            if($_i > 2 && $_i <= $count_post -2 ){
                $thumb = b2_get_thumb(array(
                    'thumb'=>$v['thumb'],
                    'width'=>round($w*2,0),
                    'height'=>$data['slider_height'],
                    'ratio'=>1.4
                ));

                $cat = self::get_cat_info($v);
                $html_slider .= '
                <div class="slider-6-carousel">
                    <div class="slider-info slider-in b2-radius">
                        <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                        '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                    </div>
                </div>';
            }
        }
        
        $html_slider .= '</div></div></div>';

        //获取后2篇文章
        $html_last = '<div class="slider-6-right" style="width:'.round($w/$data['width']*100,6).'%">';
        $_i = 0;
        foreach($last_arr as $k => $v){
            $_i++;
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>round($w,0),
                'height'=>round($h,0),
                'ratio'=>1.4
            ));

            $cat = self::get_cat_info($v);
            $html_last .= '<div class="slider-6-box'.$_i.' slider-6-item b2-radius box slider-in" style="height:0;padding-top:'.round($h/$w*100,6).'%">
                <div class="slider-info b2-radius">
                    <a class="link-block" '.(!$data['slider_new_window'] ? 'target="_blank"' : '').'  href="'.($v['link'] ? $v['link'] : 'javascript:void(0)').'"></a>
                    '.b2_get_img(array('src'=>$thumb,'class'=>array('slider-img','b2-radius'))).($data['slider_show_title'] ? $cat : '').'
                </div>
            </div>';
        }
        $html_last .= '</div>';

        $css = '
            <style>
                .slider-6-'.$i.' .slider-6-middle{
                    margin:0 '.$data['slider_gap'].'px;
                }
                .slider-6-'.$i.' .slider-6-item + .slider-6-item{
                    margin-top:'.$data['slider_gap'].'px;
                }
            </style>
        ';

        return  $css.'<div class="slider-6 slider-6-'.$i.'  '.($data['slider_show_title'] ? 'slider-show-title' : 'show-title-none').'" >'.$html_first.$html_slider.$html_last.'</div>';
    }

    public static function get_cat_info($data){

        $cat = $data['cat']['title'] ? '
            <div class="slider-cat">
                <span class="b2-radius"><b style="border-left:4px solid '.$data['cat']['color'].'"></b>'.$data['cat']['title'].'</span>
            </div>
        ' : '';

        $user = $data['display_name'] ? '
            <div class="slider-user">
                '.b2_get_img(array('src'=>$data['avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$data['display_name'])).'<span>'.$data['display_name'].'</span><span>'.$data['date'].'</span>
            </div>
        ' : '';

        return '<div class="slider-info-box">
            '.$cat.'
            <h2>'.$data['title'].'</h2>
            '.$user.'
        </div>';
    }

    public static function get_width($data){

        return b2_get_page_width($data['show_widget']);
    }

    public static function slider_data($data){

        if(!isset($data['slider_list'])) return false;

        $list_data = self::list_array($data['slider_list']);

        $arg = array();

        foreach ($list_data as $k => $v) {

            $arr = array(
                'link'=>'',
                'title'=>'',
                'thumb'=>'',
                'des'=>'',
                'avatar'=>'',
                'date'=>'',
                'display_name'=>'',
                'id'=>'',
                'cat'=>array(
                    'title'=>'',
                    'link'=>'',
                    'color'=>''
                )
            );

            if(is_numeric($v['id'])){
                
                //作者信息
                $user_id = get_post_field('post_author',$v['id']);
                $user_info = get_userdata($user_id);
                if(!isset($user_info->display_name)) continue;
                //文章信息
                $arr['link'] = get_permalink($v['id']);
                $arr['title'] = get_the_title($v['id']);
                
                $arr['des'] = self::get_des($v['id'],200);
                $arr['date'] = get_the_date( 'Y-n-j',$v['id'] );

                $arr['display_name'] = $user_info->display_name;
                $arr['avatar'] = get_avatar_url($user_id,array('size'=>50));

                $arr['thumb'] = $v['img'] === '0' ? Post::get_post_thumb($v['id']) : $v['img'];

                $cats = get_the_category($v['id']);
                if(!empty($cats)){
                    $arr['cat'] = array(
                        'title'=>$cats[0]->name,
                        'link'=>get_category_link( $cats[0]->term_id ),
                        'color'=>get_term_meta($cats[0]->term_id,'b2_tax_color',true)
                    );
                }

            }else{
                $arr['thumb'] = $v['img'];
                $arr['link'] = $v['id'];
                $arr['title'] = $v['title'];
            }

            $arr['id']=$v['id'];

            $arg[] = $arr;

        }

        return $arg;
    }

    /**
     * 获取文章描述
     *
     * @param int $post_id 文章ID
     * @param string $post_id 文章ID
     *
     * @return string 文章描述
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_des($post_id,$size,$content = ''){
        if($content){
            $content = strip_shortcodes($content);
        }else{
            $content = get_post_field('post_excerpt',$post_id);
            $content = $content ? $content : strip_shortcodes(get_post_field('post_content',$post_id));
        }

        $content = esc_attr(wp_strip_all_tags($content));

        $content = wp_trim_words($content,$size);
        // $content = mb_strimwidth($content,0,$size,'.','utf8');

        return str_replace(array('{{','}}'),'',$content);
    }

    public static function StringToText($string,$num){
        if($string){
            //把一些预定义的 HTML 实体转换为字符
            $html_string = htmlspecialchars_decode($string);
            //将空格替换成空
            $content = str_replace(" ", "", $html_string);
            //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
            $contents = strip_tags($content);
            //返回字符串中的前$num字符串长度的字符
            return mb_strlen($contents,'utf-8') > $num ? mb_substr($contents, 0, $num, "utf-8").'....' : mb_substr($contents, 0, $num, "utf-8");
        }else{
            return $string;
        }
    }

    /**
     * 字符串转数组
     *
     * @param string $str 幻灯字符串
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function list_array($str){
        $str = trim($str, " \t\n\r\0\x0B\xC2\xA0");
        $str = explode(PHP_EOL, $str );
        $arg = array();

        foreach ($str as $k => $v) {
            $_v = explode('|', $v);
            $arg[] = array(
                'id'=>isset($_v[0]) ? trim($_v[0]) : '',
                'img'=>isset($_v[1]) ? trim($_v[1]) : '',
                'title'=>isset($_v[2]) ? trim($_v[2]) : '',
            );
        }

        return $arg;
    }
}