<?php namespace B2\Modules\Common;
use B2\Modules\Common\Poster;
use B2\Modules\Common\Distribution;
use Grafika\Gd\Editor;
use Grafika\Color;
use B2\Modules\Common\IntCode;
use B2\Modules\Common\Post;

class FileUpload{

    public static $editor;
    public static $upload_dir;
    public static $allow_crop;
    public static $allow_webp;

    public function init(){
        //add_filter('sanitize_file_name', array($this,'rename_filename'),10);

        //本地裁剪
        self::$upload_dir = apply_filters('b2_upload_path_arg',wp_upload_dir());
        self::$editor = new Editor();
        self::$allow_crop = b2_get_option('normal_write','write_image_crop');
        self::$allow_webp = b2_get_option('normal_write','write_image_webp');
        
    }

    public static function create_code($url){
        $url=crc32($url);
        $result=sprintf("%u",$url);
        return self::code62($result);
    }

    public static function code62($x){
        $show='';
        while($x>0){
            $s=$x % 62;
            if ($s>35){
                $s=chr($s+61);
            }elseif($s>9&&$s<=35){
                $s=chr($s+55);
            }
            $show.=$s;
            $x=floor($x/62);
        }
        return $show;
    }

    /**
     * 上传文件重命名
     *
     * @param string $filename 文件名
     *
     * @return string 文件名
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function rename_filename($filename,$type,$post_id){
        $info = pathinfo($filename);
        $ext = empty($info['extension']) ? '' : '.' . $info['extension'];
        return $info['filename'].'_'.$post_id.'_'.$type.'_'.self::create_code($filename).rand(1,9999).$ext;
    }


    public static function url_to_base64($url){
        if($url){
            $file_contents = wp_remote_get($url,array(
                'httpversion' => '1.0',
				'timeout' => 20,
				'redirection' => 20,
				'sslverify' => FALSE,
				'user-agent' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; MALC)'
            ));

            if(is_wp_error($file_contents)){
                return array('error'=>$file_contents->get_error_message());
            }
        
            $img_base64 = base64_encode($file_contents['body']);
            if($img_base64){
                return 'data:image/jpeg;base64,'.$img_base64;
            }
        }

        return '';
    }

    public static function webpConvert2($file, $compression_quality = 80){
        // check if file exists
        if (!file_exists($file)) {
            return false;
        }
        $file_type = exif_imagetype($file);
        //https://www.php.net/manual/en/function.exif-imagetype.php
        //exif_imagetype($file);
        // 1    IMAGETYPE_GIF
        // 2    IMAGETYPE_JPEG
        // 3    IMAGETYPE_PNG
        // 6    IMAGETYPE_BMP
        // 15   IMAGETYPE_WBMP
        // 16   IMAGETYPE_XBM
        // $output_file =  $file . '.webp';
        $output_file = str_replace(substr(strrchr($file, '.'), 1),'webp',$file);
        if (file_exists($output_file)) {
            return $output_file;
        }
        if (function_exists('imagewebp')) {
            switch ($file_type) {
                case '1': //IMAGETYPE_GIF
                    $image = imagecreatefromgif($file);
                    break;
                case '2': //IMAGETYPE_JPEG
                    $image = imagecreatefromjpeg($file);
                    break;
                case '3': //IMAGETYPE_PNG
                        $image = imagecreatefrompng($file);
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                        break;
                case '6': // IMAGETYPE_BMP
                    $image = imagecreatefrombmp($file);
                    break;
                case '15': //IMAGETYPE_Webp
                return false;
                    break;
                case '16': //IMAGETYPE_XBM
                    $image = imagecreatefromxbm($file);
                    break;
                default:
                    return false;
            }
            // Save the image
            $result = imagewebp($image, $output_file, $compression_quality);
            if (false === $result) {
                return false;
            }
            // Free up memory
            imagedestroy($image);
            return $output_file;
        } elseif (class_exists('Imagick')) {
            $image = new \Imagick();
            $image->readImage($file);
            if ($file_type === "3") {
                $image->setImageFormat('webp');
                $image->setImageCompressionQuality($compression_quality);
                $image->setOption('webp:lossless', 'true');
            }
            $image->writeImage($output_file);
            return $output_file;
        }
        return false;
    }

    /**
     * 图片裁剪，并储存到本地
     *
     * @param array $arg 
     *
     * @return string 图片URL
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function thumb($arg){
        
        //thumb:图片路径,
        //type:编辑形式,
        //fill:固定长宽剧中裁剪，
        //fit:等比缩放，
        //exact:固定尺寸（可能造成变形），
        //exactW:等宽缩放（宽度固定，高度自动），
        //exactH:等高缩放（高度固定，宽度自动）
        //smart:智能裁剪，
        //gif 是否移除gif动画效果
        $r = apply_filters('b2_thumb_arg',wp_parse_args($arg,array(
            'thumb'=>'',
            'type'=>'fill',
            'width'=>'500',
            'height'=>'500',
            'gif'=>0,
            'webp'=>false,
            'ratio'=>B2_IMG_RATIO,
            'custom'=>false //留给站长DIY的参数，为true的时候，可以使用下面 b2_thumb_custom 钩子，返回图片地址
        )));

        $r['thumb'] = Post::img_replace($r['thumb']);

        if($r['custom']){
            return apply_filters('b2_thumb_custom',$r['thumb'],$r);
        }

        if($r['height'] === '100%'){
            $r['type'] = 'exactW';
            unset($r['height']);
        }

        if($r['width'] === '100%'){
            $r['type'] = 'exactH';
            unset($r['width']);
        }

        if(defined('B2_IMG_RATIO')){
            if(isset($r['width'])){
                $r['width'] = ceil($r['width']*$r['ratio']);
            }
            if(isset($r['height'])){
                $r['height'] = ceil($r['height']*$r['ratio']);
            }
        }
        
        //检查图片是为空
        if(empty($r['thumb'])){
            return apply_filters('b2_thumb_default_image',b2_get_default_img(),$r);
        }
        
        //如果不是本地文件，直接返回
        if(strpos($r['thumb'],B2_HOME_URI) === false){

            //如果使用的是相对地址
            if(strpos($r['thumb'],'//') === false){

                // return $r['thumb'];

                $r['thumb'] = self::$upload_dir['baseurl'].'/'.$r['thumb'];
            }

            return apply_filters('b2_thumb_no_local',$r['thumb'],$r);
        }

        if(!self::$allow_crop) {
            return $r['thumb'];
        }
        
        //检查是否为裁剪过的图片
        if(strpos($r['thumb'],'_mark_') !== false){
            return $r['thumb'];
        }

        // if(strpos($r['thumb'],'.gif') !== false){
        //     return $r['thumb'];
        // }
        
        //如果不裁剪，返回原图
        if($r['type'] == 'default') return $r['thumb'];

        //获取原始图片的物理地址
        $rel_file_path = str_replace(self::$upload_dir['baseurl'],'',$r['thumb']);
        $rel_file_path = str_replace(array('/','\\'),B2_DS,$rel_file_path);

        $basedir = str_replace(array('/','\\'),B2_DS,self::$upload_dir['basedir']);

		$rel_file_path = $basedir.$rel_file_path;
		
        if(!is_file($rel_file_path)){
            return $r['thumb'];
        }
        
        list($width, $height, $type, $attr) = getimagesize($rel_file_path);

        if((isset($r['width']) && $width < $r['width']) || (isset($r['height']) && $height < $r['height'])) return $r['thumb'];
        
        $basename = basename($rel_file_path);

        $rel_file = str_replace($basedir.B2_DS,'',$rel_file_path);

        $r['height'] = isset($r['height']) ? $r['height'] : null;

		$file_path = str_replace($basename,'',$rel_file);
		
        $thumb_dir = $basedir.B2_DS.'thumb'.B2_DS.$file_path.$r['type'].'_w'.$r['width'].'_h'.$r['height'].'_g'.$r['gif'].'_mark_'.$basename;

        //如果存在直接返回
        if(is_file($thumb_dir)){
            $basedir = str_replace(array('/','\\'),'/',$basedir);
            $thumb_dir = str_replace(array('/','\\'),'/',$thumb_dir);
            return apply_filters('b2_get_thumb',str_replace($basedir,self::$upload_dir['baseurl'],$thumb_dir));
        }

        try {
            self::$editor->open($image , $rel_file_path);

            switch ($r['type']) {
                case 'fit':
                    self::$editor->resizeFit($image , $r['width'] , $r['height']);
                    break;
                case 'exact':
                    self::$editor->resizeExact($image , $r['width'] , $r['height']);
                    break;
                case 'exactW':
                    self::$editor->resizeExactWidth($image , $r['width']);
                    break;
                case 'exactH':
                    self::$editor->resizeExactHeight($image , $r['height']);
                    break;
                case 'smart':
                    self::$editor->crop( $image, $r['width'], $r['height'], 'smart' );
                    break;
                default:
                    self::$editor->resizeFill($image , $r['width'],$r['height']);
                    break;
            }

            if($r['gif']){
                self::$editor->flatten( $image );
            }
            
            if(self::$editor->save($image , $thumb_dir,null,85,true)){
                
                if(self::$allow_webp){
                   // $thumb = self::webpConvert2($thumb_dir);
                    $thumb = str_replace(substr(strrchr($thumb_dir, '.'), 1),'webp',$thumb_dir);
                    self::$editor->save($image , $thumb,null,85,true);
                }

                $basedir = str_replace(array('/','\\'),'/',$basedir);
                $thumb_dir = str_replace(array('/','\\'),'/',$thumb_dir);
                return apply_filters('b2_get_thumb',str_replace($basedir,self::$upload_dir['baseurl'],$thumb_dir));
            }

            return apply_filters('b2_thumb_default_image',b2_get_default_img(),$r);
        } catch (\Throwable $th) {
            return $r['thumb'];
        }
        
        return $r['thumb'];
    }

    public static function auto_webp($url,$webp){

        if(!self::$allow_webp){
            return $url;
        }

        if(strpos($url,self::$upload_dir['baseurl']) === false) return $url;
        
        $rel_file_path = str_replace(self::$upload_dir['baseurl'],'',$url);
        $rel_file_path = str_replace(array('/','\\'),B2_DS,$rel_file_path);

        $basedir = str_replace(array('/','\\'),B2_DS,self::$upload_dir['basedir']);

		$rel_file_path = $basedir.$rel_file_path;
		
        if(is_file($rel_file_path)){
            $thumb = str_replace(substr(strrchr($rel_file_path, '.'), 1),'webp',$rel_file_path);
            if(!is_file($thumb)){
                try {
                    self::$editor->open($image , $rel_file_path);

                    self::$editor->save($image , $thumb,null,85,true);
                } catch (\Throwable $th) {
                    return $url;
                }
            }else{
                return $webp;
            }

            return apply_filters('b2_get_thumb_webp',$webp ? $webp : str_replace($basedir,self::$upload_dir['baseurl'],$thumb));
        }

        return $url;
    }

    public static function get_poster_data($post_id){

        if(!$post_id) return [];

        //默认设置项
        $default_poster_settings = get_option('b2_template_single');
        if(!isset($default_poster_settings['single_poster_group'][0])){
            $default_poster_settings = array(
                'single_poster_default_img'=>b2_get_option('template_single','single_poster_default_img'),
                'single_poster_default_logo'=>b2_get_option('template_single','single_poster_default_logo'),
                'single_poster_default_text'=>html_entity_decode(esc_attr(b2_get_option('template_single','single_poster_default_text') )),
                'single_poster_default_desc'=>html_entity_decode(esc_attr(b2_get_option('template_single','single_poster_default_desc') )),
                'single_poster_dl'=>esc_attr(b2_get_option('template_single','single_poster_dl') ),
            );
        }else{
            $default_poster_settings = $default_poster_settings['single_poster_group'][0];
        }

        $post_year = get_the_date('Y',$post_id);
        $post_month = get_the_date('m',$post_id);
        $post_day = get_the_date('d',$post_id);

        //文章标题
        $title = esc_attr(get_the_title($post_id));

        //文章描述
        $text = b2_get_excerpt($post_id,60);

        $thumb_url = get_post_meta($post_id,'b2_post_poster',true);

        if(!$thumb_url){
            //获取特色图
            $thumb_id = get_post_thumbnail_id($post_id);
            if($thumb_id){
                $thumb_url = wp_get_attachment_url($thumb_id);
            }else{
                $thumb_url = b2_get_first_img(get_post_field('post_content',$post_id));
            }

            if(!$thumb_url){
                $thumb_url =  $default_poster_settings['single_poster_default_img'];
            }
        }

        $thumb_url = b2_get_thumb(array('thumb'=>$thumb_url,'height'=>600,'width'=>400));

        $ref = '';

        // $author = get_post_field('post_author',$post_id);
        $author = b2_get_current_user_id();
        if($author){
            if(Distribution::user_can_distribution($author)){
                $c = new IntCode();
                $ref = '?ref='.$c->encode($author);
            }
        }

        $logo = $default_poster_settings['single_poster_default_logo'];

        $link = get_permalink($post_id);

        $thumb_url = B2_HOME_URI.'/get-image?token='.md5(AUTH_KEY.$thumb_url.$post_id).'&id='.$post_id.'&url='.$thumb_url;

        $logo = B2_HOME_URI.'/get-image?token='.md5(AUTH_KEY.$logo.$post_id).'&id='.$post_id.'&url='.$logo;
        
        return array(
            'title'=>$title,
            'content'=>$text,
            //isset($default_poster_settings['single_poster_dl']) && $default_poster_settings['single_poster_dl'] ? '//images.weserv.nl/?url='.$thumb_url.'&w=344&dpr=2' : 
            'thumb'=> $thumb_url,
            'logo'=> $logo,
            'desc'=>$default_poster_settings['single_poster_default_desc'],
            'date'=>array(
                'year'=>$post_year,
                'month'=>$post_month,
                'day'=>$post_day
            ),
            'ref'=>$ref,
            'link'=>$link.($ref ? $ref : '')
        );
    }

    public static function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
     
        if(strlen($hex) == 3) {
           $r = hexdec(substr($hex,0,1).substr($hex,0,1));
           $g = hexdec(substr($hex,1,1).substr($hex,1,1));
           $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
           $r = hexdec(substr($hex,0,2));
           $g = hexdec(substr($hex,2,2));
           $b = hexdec(substr($hex,4,2));
        }
     
        return array($r, $g, $b);
     }

    public static function get_post_poster($post_id){

        if(is_file(self::$upload_dir['basedir'].B2_DS.'posters'.B2_DS.$post_id.'.jpg')) return apply_filters('b2_poster_url',self::$upload_dir['baseurl'].'/posters/'.$post_id.'.jpg');

        $data = self::get_poster_data($post_id);

        $im = imagecreatetruecolor(760, 1225);

        //填充画布背景色
        $color = imagecolorallocate($im, 250, 250, 250);

        //裁剪画布
        imagefill($im, 0, 0, $color);

        //文字颜色
        $font_color = ImageColorAllocate ($im, 51, 51, 51);

        //文字路径
        $font_file = B2_THEME_DIR.B2_DS.'Assets'.B2_DS.'fonts'.B2_DS.'avatar.ttf';

        //摘要文字
        $content = Poster::autoLineSplit($data['content'],$font_file,19,'utf8',680,2);
        //标题文字
        $theTitle = Poster::autoLineSplit($data['title'],$font_file,19,'utf8',470,2);

        $img_h = 810;
        $title_h = 875;
        $desc_h = 970;
        $date_h = 775;

        if(count($theTitle) == 1 && count($content) == 1){
            $img_h = $img_h + 90;
            $title_h = $title_h + 90;
            $desc_h = $desc_h + 40;
            $date_h = $date_h + 90;
        }else{
            if(count($theTitle) == 1){
                $img_h = $img_h + 50;
                $title_h = $title_h + 50;
                $date_h = $date_h + 50;
            }elseif(count($content) == 1){
                $img_h = $img_h + 30;
                $title_h = $title_h + 30;
                $desc_h = $desc_h + 30;
                $date_h = $date_h + 30;
            }
        }

        //放置缩略图
        list($g_w,$g_h) = getimagesize($data['thumb']);
        $goodImg = imagecreatefromstring(file_get_contents($data['thumb']));
        $dims = image_resize_dimensions( $g_w, $g_h, 760, $img_h,true);
        imagecopyresized($im, $goodImg, 0, 0, $dims[2], $dims[3],$dims[4], $dims[5], $dims[6],$dims[7]);

        //日期文字颜色
        $date_color = ImageColorAllocate ($im, 255, 255, 255);
        //日期字体
        $date_font_file = B2_THEME_DIR.B2_DS.'Assets'.B2_DS.'fonts'.B2_DS.'9.ttf';
        //放置日期
        imagettftext($im, 26,0, 35, $date_h, $date_color ,$date_font_file, $data['date']['year']);
        imagettftext($im, 26,0, 133, $date_h, $date_color ,$date_font_file, $data['date']['month']);
        imagettftext($im, 26,0, 111, $date_h, $date_color ,$date_font_file, '/');
        imagettftext($im, 60,0, 55, $date_h - 40, $date_color ,$date_font_file, $data['date']['day']);

        //标题
        if(isset($theTitle[0])){
            imagettftext($im, 26,0, 35, $title_h, $font_color ,$font_file, $theTitle[0]);
        }
        if(isset($theTitle[1])){
            imagettftext($im, 26,0, 35, $title_h + 43, $font_color ,$font_file, $theTitle[1]);
        }

        //摘要
        $content_color = ImageColorAllocate ($im, 101, 101, 101);
        if(isset($content[0])){
            imagettftext($im, 18,0, 35, $desc_h, $content_color ,$font_file, $content[0]);
        }
        if(isset($content[1])){
            imagettftext($im, 18,0, 35, $desc_h + 30, $content_color ,$font_file, $content[1]);
        }

        //分割线
        imagettftext($im, 12,0, 30, 1060, ImageColorAllocate ($im, 200, 200, 200) ,$font_file, '─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─');

        //logo
        list($g_w,$g_h) = getimagesize($data['logo']);
        $logo = imagecreatefromstring(file_get_contents($data['logo']));
        $dims = image_resize_dimensions( $g_w, $g_h, 129, 37,false);
        imagecopyresized($im, $logo, 40, 1110, $dims[2], $dims[3],$dims[4], $dims[5], $dims[6],$dims[7]);

        //站点描述
        imagettftext($im, 12,0, 40, 1170, $content_color ,$font_file, $data['desc']);

        //放置二维码
        list($g_w,$g_h) = getimagesize($data['qrcode']);
        $qrcode = imagecreatefromstring(file_get_contents($data['qrcode']));
        $dims = image_resize_dimensions( $g_w, $g_h, 100, 100,false);
        imagecopyresized($im, $qrcode, 600, 1075, $dims[2], $dims[3],$dims[4], $dims[5], $dims[6],$dims[7]);

        //站点描述
        imagettftext($im, 12,0, 600, 1195, $content_color ,$font_file, __('扫码查看详情','b2'));

        ob_start();
		imagejpeg($im,null,85);
		$image = ob_get_contents();
        ob_end_clean();

        $upload_file = '';
        if(wp_mkdir_p(self::$upload_dir['basedir'].B2_DS.'posters')){
            $upload_file = file_put_contents(self::$upload_dir['basedir'].B2_DS.'posters'.B2_DS.$post_id.'.jpg', $image );
            unlink($data['thumb']);
            unlink($data['logo']);
        }

        if($upload_file) {
			return apply_filters('b2_poster_url',self::$upload_dir['baseurl'].'/posters/'.$post_id.'.jpg');
		}

        imagedestroy($im);
        imagedestroy($qrcode);
        // imagedestroy($dims);
        // imagedestroy($avatar);

        return array('error'=>__('获取失败','b2'));
    }

    public static function url_to_path($url,$dir){

        //获取远程图片到本地
        $url = self::url_file_upload($url);

        //如果保存失败，返回错误
        if(isset($url['error']) && $url['error']){
            return $url;
        }

        return $url;
    }

    /**
     * 图片上传
     *
     * @param object $request restapi object
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function file_upload($request){

        $user_id = b2_get_current_user_id();
  
        if(!$user_id){
            return array('error'=>__('请先登录','b2'));
        }

        wp_set_current_user($user_id);

        if(!isset($request['post_id']))  return array('error'=>__('缺少参数','b2'));

        if(!$request['post_id'] || !is_numeric($request['post_id'])){
            return array('error'=>__('缺少文章ID','b2'));
        }

        if(!isset($request['type'])) return array('error'=>__('请设置一个type','b2'));

        if(!in_array($request['type'],b2_file_type())) return array('error'=>__('不支持这个type','b2'));

        //文件体积检查
        if(!isset($_FILES['file']['size'])){
            return array('error'=>sprintf(__('文件损坏，请重新选择（%s）','b2'),$_FILES['file']['name']));
        }

        $size = 0;
        $mime = '';
        if(strpos($_FILES['file']['type'],'image') !== false){
            $mime = 'image';
            $size = b2_get_option('normal_write','write_image_size');
            $text = __('图片','b2');
        }elseif(strpos($_FILES['file']['type'],'video') !== false){
            $mime = 'video';
            $size = b2_get_option('normal_write','write_video_size');
            $text = __('视频','b2');
        }else{
            $mime = 'file';
            $size = b2_get_option('normal_write','write_file_size');
            $text = __('文件','b2');
        }

        if($_FILES['file']['size'] > $size*1048576){
            return array('error'=>sprintf(__('%s必须小于%sM，请重新选择','b2'),$text,$size));
        }

        // if(!$user_id) return ['error'=>__('无权上传','b2')];
        //检查上传权限
        $role = User::check_user_media_role($user_id,$mime);
        if(!$role && $request['type'] != 'verify' && $request['type'] != 'avatar') return array('error'=>sprintf(__('您无权上传%s','b2'),$text));

        //检查图片上传数量
        $count = b2_get_option('normal_safe','upload_count');

        $has_upload_count = (int)wp_cache_get('b2_upload_limit_'.$user_id,'b2_upload_limit');

        if($has_upload_count >= $count && !user_can( $user_id, 'manage_options' )){
            
            return array('error'=>__('非法操作','b2'));
        }

        $count = 0;

        //如果是认证，检查上传的数量
        if(($request['type'] == 'verify' || $request['type'] == 'avatar') && !$role){
            $mimetype = b2isImg($_FILES['file']['tmp_name']);
            if ($mimetype){
                if(!$role){
                    $count = (int)get_user_meta($user_id,'b2_verify_upload_count',true);

                    if($count >= 10) return array('error'=>__('非法操作','b2'));
                }
            }else{
                return array('error'=>__('非法操作','b2'));
            }
        }
        
        wp_cache_set('b2_upload_limit_'.$user_id,($has_upload_count + 1),'b2_upload_limit',HOUR_IN_SECONDS*3);
        // $mimes = array('gif' => 'image/gif','jpeg'=>'image/jpeg','jpg'=>'image/jpeg','png'=>'image/png');

        // if($request['filetype'] === 'video'){
        //     $mimes = array('mp4'=>'video/mp4', 'asf'=>'video/x-ms-asf','wmv'=>'video/x-ms-wmv', 'avi'=>'video/avi','flv'=>'video/x-flv','mpeg'=>'video/mpeg','ogg'=>'video/ogg','webm'=>'video/webm','3gpp'=>'video/3gpp','3gpp2'=>'video/3gpp2');
        // }

        // if($request['filetype'] === 'file'){
        //     $mimes = array('mp4'=>'video/mp4', 'asf'=>'video/x-ms-asf','wmv'=>'video/x-ms-wmv', 'avi'=>'video/avi','flv'=>'video/x-flv','mpeg'=>'video/mpeg','ogg'=>'video/ogg','webm'=>'video/webm','3gpp'=>'video/3gpp','3gpp2'=>'video/3gpp2');
        // }
        
        require_once ABSPATH .B2_DS.'wp-admin'.B2_DS.'includes'.B2_DS.'image.php';
        require_once ABSPATH . 'wp-admin'.B2_DS.'includes'.B2_DS.'file.php';
        require_once ABSPATH . 'wp-admin'.B2_DS.'includes'.B2_DS.'media.php';

        if(!isset($request['file_name'])){
            $_FILES['file']['name'] = self::rename_filename($_FILES['file']['name'],$request['type'],$request['post_id']);
        }else{
            $_FILES['file']['name'] = $request['file_name'];
        }

        $id = media_handle_upload( 'file',$request['post_id'] );

        if ( is_wp_error( $id ) ) {
            return array('error'=>sprintf(__('上传失败(%s)：','b2'),$_FILES['file']['name']).$id->get_error_message());
        }else{

            if(!$role){
                update_user_meta($user_id,'b2_verify_upload_count',($count+1));
            }

            if(isset($request['set_poster']) && get_post_field('post_author',absint($request['set_poster'])) == $user_id){
                set_post_thumbnail(absint($request['set_poster']),$id);
            }
            return array('id'=> $id,'url'=>wp_get_attachment_url($id));
        }
    
    }
}