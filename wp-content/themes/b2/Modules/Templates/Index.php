<?php namespace B2\Modules\Templates;

use B2\Modules\Templates\Modules\Sliders;

class Index{
    public function init(){
        //add_action('b2_index_before',array($this,'index_slider'),10);
        add_action('b2_index',array($this,'modules_loader'),10);
    }

    public function index_slider(){
        //首页幻灯
        $index_slider = b2_get_option('template_index','index_slider');

        if(!empty($index_slider) && isset($index_slider[0]['index_slider_open']) && $index_slider[0]['index_slider_open']== 1){
            $slider = new Sliders();

            echo '
                <div class="index-slider-top mg-b '.($index_slider[0]['slider_width'] == 0 && $index_slider[0]['slider_type'] == 'slider-1' ? 'slider-full-width' : ($index_slider[0]['slider_width'] == 0 ? ' mg-r mg-l' : '')).'">'.$slider->init($index_slider[0],0).'</div>
            ';
        }
    }

    public function modules_loader(){

        //首页模块
        $index_settings = b2_get_option('template_index','index_group');
        if(!$index_settings){
            echo '<div class="none-index">
                '.sprintf(__('请先前往后台对首页布局进行设置：%s','b2'),'<a style="color:red;border-bottom:1px solid red" href="'.admin_url('/admin.php?page=b2_template_index').'">首页布局设置</a>').'
            </div>';
            return ;
        }
        $i = 0;
        
        $cache_key = md5(B2_HOME_URI);

        $is_mobile = wp_is_mobile();

        foreach ($index_settings as $k => $v) {
            $i++;
            if(isset($v['module_type']) && $v['module_type']){
                $namespace = 'B2\Modules\Templates\Modules\\'.ucfirst($v['module_type']);

                if($v['module_type'] === 'ads') continue;

                $modules =  new $namespace;

                $show_widget = isset($v['show_widget']) && (int)$v['show_widget'] === 1  ? true : false;
                $v['show_widget'] = $show_widget;

                $v['width'] = b2_get_page_width($v['show_widget']);

                $bg_color = isset($v['module_bg_color']) ? $v['module_bg_color'] : '';
                if($v['slider_type'] === 'slider-1' && (string)$v['slider_width'] === '0' && $i == 1){
                    $bg_color = '';
                }

                $bg_img = isset($v['module_bg_img']) ? $v['module_bg_img'] : '';

                $mobile_show = isset($v['module_mobile_show']) ? (int)$v['module_mobile_show'] : 1;

                if($mobile_show === 3) continue;

                if(!defined('WP_CACHE') || (defined('WP_CACHE') && WP_CACHE === false)){
                    if($mobile_show === 0 && $is_mobile){
                        continue;
                    }
    
                    if($mobile_show === 2 && !$is_mobile){
                        continue;
                    }
                }
                
                if($this->is_no_cache($v)){
                    
                    $html = $modules->init($v,$i);
                }else{
                    $cache = get_transient($cache_key.'_b2_index_module_'.$i);

                    if($cache){
                        $html = $cache;
                    }else{
                        
                        $html = $modules->init($v,$i);
                        set_transient($cache_key.'_b2_index_module_'.$i,$html, 60*30);
                    }
                }

                do_action('b2_index_modules_before_'.$i);
                do_action('b2_index_modules_before_'.$v['key']);

                echo '<div id="home-row-'.$v['key'].'" class="'.($show_widget ? 'have-widget' : '').' '.($mobile_show === 0 ? 'mobile-hidden' : ($mobile_show === 2 ? 'pc-hidden' : '')).' home_row home_row_'.$k.' '.((string)$v['slider_width'] === '0' && $v['slider_type'] === 'slider-1' ? 'homw-row-full' : '').' module-'.$v['module_type'].' '.($bg_color ? 'home_row_bg' : '').' '.($bg_img ? 'home_row_bg_img' : '').'" style="background-color:'.$bg_color.';'.($bg_img ? 'background-image:url('.b2_get_thumb(array('thumb'=>$bg_img,'width'=>1800,'height'=>'100%')).');' : '').'">
                    <div class="'.$this->modules_width($v).'>';
                    
                    echo '<div class="home-row-left content-area '.($v['module_type'] === 'posts' ? '' : '').'">'
                        .$html.
                    '</div>';

                    if($show_widget){
                        $affixed = isset($v['widget_ffixed']) && (int)$v['widget_ffixed'] === 1 ? 'widget-ffixed' : '';
                        echo '<div class="widget-area"><div class="sidebar"><div class="sidebar-innter '.$affixed.'">';
                            dynamic_sidebar(isset($v['key']) ? $v['key'] : 'index_widget_'.$k);
                        echo '</div></div></div>';
                    }
                    
                echo '</div></div>';

                do_action('b2_index_modules_after_'.$i);
                do_action('b2_index_modules_after_'.$v['key']);

            }
        }

    }

    public function is_no_cache($data){
        if(!B2_OPEN_CACHE) return true;

        if($data['module_type'] === 'posts' && $data['post_order'] === 'random') return true;

        if($data['module_type'] === 'circle') return true;

        return false;
    }

    public function modules_width($v){

        if($v['module_type'] === 'sliders'){
            if((string)$v['slider_width'] === '0' && $v['slider_type'] === 'slider-1' && !$v['show_widget']){
                return '" style="width:100%"';
            }
        }

        if($v['module_type'] === 'html' && isset($v['html_width'])){
            if((string)$v['html_width'] === '0' && !$v['show_widget']){
                return '" style="width:100%"';
            }
        }

        if($v['module_type'] === 'search'){
            if((string)$v['search_width'] === '0' && !$v['show_widget']){
                return '" style="width:100%"';
            }
        }

        return 'wrapper"';
    }

    public function modules_left_width($v){

        if($v['module_type'] === 'html' && isset($v['html_width'])){
            if((string)$v['html_width'] === '0' && !$v['show_widget']){
                return 'style="width:100%"';
            }
        }

        if($v['module_type'] === 'search'){
            if((string)$v['search_width'] === '0' && !$v['show_widget']){
                return 'style="width:100%"';
            }
        }

        if($v['module_type'] === 'sliders'){
            if((string)$v['slider_width'] === '0' && $v['slider_type'] === 'slider-1' && !$v['show_widget']){
                return 'style="width:100%"';
            }
        }

        return 'style="width:'.$v['width'].'px"';
    }
}