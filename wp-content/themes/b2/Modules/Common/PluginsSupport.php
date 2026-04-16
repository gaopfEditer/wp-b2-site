<?php namespace B2\Modules\Common;

class PluginsSupport{

    public $upload_dir;
    public $type;
    public static $allow_crop;

    public function init(){
        add_action('init', array($this,'loader'), 1);
        $this->upload_dir = apply_filters('b2_upload_path_arg',wp_upload_dir());
        self::$allow_crop = b2_get_option('normal_write','write_image_crop');
    }

    public function loader(){
        //是否使用了OSS_UPLOAD插件
        if(defined('OSS_ACCESS_ID') || class_exists('WPOSS')){
            add_filter('b2_thumb_no_local',array($this,'b2_oss_thumb'),10,2);
            add_action( 'wp_print_scripts', array($this,'wpdocs_dequeue_script'), 100 );

            //不使用插件自带的裁剪方法
            remove_filter('wp_get_attachment_url', 'oss_upload_attachment_url',9999,2);
            remove_filter('wp_calculate_image_srcset', 'oss_upload_image_srcset', 9999, 5);

            //add_filter('b2_upload_path_arg', array($this,'wp_upload_dir'),1,1);

        }

        if(defined('COS_BASENAME') || defined('WPCOS_BASENAME')){
            add_filter('b2_thumb_no_local',array($this,'b2_cos_thumb'),10,2);
        }

        if(defined('WPUpYun_VERSION')){
            add_filter('b2_thumb_no_local',array($this,'b2_upyun_thumb'),10,2);
            add_filter('b2_poster_url',array($this,'b2_poster_url'),10,1);
        }

        if(class_exists('WPQiNiu')){
            add_filter('b2_thumb_no_local',array($this,'b2_qiniu_thumb'),10,2);
            add_filter('b2_poster_url',array($this,'b2_qiniu_url'),10,1);
        }

    }

    public function b2_wposs_thumb($thumb,$r){

    }

    public function b2_qiniu_thumb($thumb,$r){
        $qiniu_url = get_option('upload_url_path');
        if(!$qiniu_url) return $thumb;

        if(strpos($thumb,'?') !== false){
            return $thumb;
        }

        //如果是本地图片替换成OSS图片
        if(strpos($thumb,$this->upload_dir['baseurl']) !== false){
            $thumb = str_replace($this->upload_dir['baseurl'],$qiniu_url,$thumb);
        }

        if(strpos($thumb,$qiniu_url) === false){
            return $thumb;
        }

        if(!self::$allow_crop) {
            return $thumb;
        }

        //如果不裁剪，返回原图
        if($r['type'] == 'default') return $thumb;
                
        $process = '';

        //裁剪规则
        switch ($r['type']) {
            case 'fill':
                $process .= '?imageMogr2/gravity/Center/thumbnail/'.$r['width'].'x'.$r['height'];
                break;
            case 'exactW':
                $process .= '?imageMogr2/gravity/Center/thumbnail/'.$r['width'].'x';
                break;
            case 'exactH':
                $process .= '?imageMogr2/gravity/Center/thumbnail/x'.$r['height'];
                break;
            default:
                $process .= '?imageMogr2/gravity/Center/thumbnail/'.$r['width'].'x'.$r['height'];
                break;
        }

        return $thumb.$process;
    }

    //右排云封面地址重写
    public function b2_poster_url($url){
		$url = str_replace($this->upload_dir['baseurl'],B2_HOME_URI.'/wp-content/uploads',$url);
		return $url;
	}

    //兼容wpupyun插件
    public function b2_upyun_thumb($thumb,$r){
    	
    	$upyun_url = get_option('upload_url_path');

        if(!$upyun_url) return $thumb;

        if(strpos($thumb,'?') !== false){
            return $thumb;
        }
    	
    	//如果是本地图片替换成OSS图片
        if(strpos($thumb,$this->upload_dir['baseurl']) !== false){
            $thumb = str_replace($this->upload_dir['baseurl'],$upyun_url,$thumb);
        }

        if(strpos($thumb,$upyun_url) === false){
            return $thumb;
        }

        if(!self::$allow_crop) {
            return $thumb;
        }

        //如果不裁剪，返回原图
        if($r['type'] == 'default') return $thumb;
        
        $process = '';

        //裁剪规则
        switch ($r['type']) {
            case 'fill':
                $process .= '!/both/'.$r['width'].'x'.$r['height'];
                break;
            case 'exactW':
                $process .= '!/fw/'.$r['width'];
                break;
            case 'exactH':
                $process .= '!/fw/'.$r['height'];
                break;
            default:
                $process .= '!/both/'.$r['width'].'x'.$r['height'];
                break;
        }
        
        return $thumb.$process;
    }

    /**
     * 禁止OSS UPLOAD插件自动加载lazyload的js
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function wpdocs_dequeue_script(){
        wp_dequeue_script( 'jquery.lazyload' );
    }

    public function b2_cos_thumb($thumb,$r){

        if(strpos($thumb,'?') !== false){
            return $thumb;
        }

        if(strpos($r['thumb'],$this->upload_dir['baseurl']) !== false){
            $thumb = str_replace($this->upload_dir['baseurl'],get_option('upload_url_path'),$r['thumb']);
        }

        if(!self::$allow_crop) {
            return $thumb;
        }

        $process = '?imageMogr2/';

        //裁剪规则
        switch ($r['type']) {
            case 'fill':
                $process .= 'crop/'.$r['width'].'x'.$r['height'].'/gravity/center';
                break;
            case 'fit':
                $process .= 'crop/'.$r['width'].'x'.$r['height'].'/gravity/center';
                break;
            case 'exact':
                $process .= 'crop/'.$r['width'].'x'.$r['height'].'/gravity/center';
                break;
            case 'exactW':
                $process .= 'thumbnail/'.$r['width'].'x';
                break;
            case 'exactH':
                $process .= 'thumbnail/x'.$r['height'];
                break;
            default:
                $process .= 'crop/'.$r['width'].'x'.$r['height'].'/gravity/center';
                break;
        }
        
        //图片压缩质量
        // $quality = ouops('oss_quality') ? intval(ouops('oss_quality')) : '75';
        // $process .= '/quality,q_'.$quality;

        //是否支持webp
        // if(oss_upload_webp() && (!defined('WP_CACHE') || (defined('WP_CACHE') && WP_CACHE === false))){
        //     $process .= '/format,webp';
        // }

        return $thumb.$process;
    }

    /**
     * 当使用OSS UPLOAD插件时，兼容主题所支持的裁剪参数
     *
     * @param string $thumb 图片地址
     * @param array $r 裁剪参数
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function b2_oss_thumb($thumb,$r){

        if(strpos($thumb,'?') !== false){
            return $thumb;
        }

        if(!class_exists('WPOSS')){
            $oss_url = ouops('oss_url');
        }else{
            $oss_url = get_option('upload_url_path');
        }

        if(!$oss_url) return $thumb;

        //如果是本地图片替换成OSS图片
        if(strpos($thumb,$this->upload_dir['baseurl']) !== false){
            $thumb = str_replace($this->upload_dir['baseurl'],$oss_url,$thumb);
        }

        if(strpos($thumb,$oss_url) === false){
            return $thumb;
        }

        if(!self::$allow_crop) {
            return $thumb;
        }

        //如果不裁剪，返回原图
        if($r['type'] == 'default') return $thumb;
        
        $process = '?x-oss-process=image/resize,';

        if(isset($r['height'])){
            if($r['height'] >= 4096){
                $r['height'] = 4000;
                $r['width'] = round(($r['width']*4000)/4096);
            }    
        }
       
        //裁剪规则
        switch ($r['type']) {
            case 'fill':
                $process .= 'm_fill,h_'.$r['height'].',w_'.$r['width'];
                break;
            case 'fit':
                $process .= 'm_mfit,h_'.$r['height'].',w_'.$r['width'];
                break;
            case 'exact':
                $process .= 'm_fixed,h_'.$r['height'].',w_'.$r['width'];
                break;
            case 'exactW':
                $process .= 'w_'.$r['width'];
                break;
            case 'exactH':
                $process .= 'h_'.$r['height'];
                break;
            default:
                $process .= 'm_fill,h_'.$r['height'].',w_'.$r['width'];
                break;
        }
        
        //图片压缩质量
        // $quality = ouops('oss_quality') ? intval(ouops('oss_quality')) : '75';
        // $process .= '/quality,q_'.$quality;

        //是否支持webp
        // if(oss_upload_webp() && $r['webp']){
        //     $process .= '/format,webp';
        // }

        return apply_filters('b2_get_oss_upload_url', $thumb.$process.'/sharpen,120');
    }

    public function get_video_poster(){

    }
}