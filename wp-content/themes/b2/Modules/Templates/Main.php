<?php
namespace B2\Modules\Templates;

class Main{

    public function init(){

        //加载css和js
        add_action( 'wp_enqueue_scripts', array( $this, 'setup_frontend_scripts' ),10 );

        // add_filter( 'script_loader_tag', array($this,'regal_tag'), 10, 3 );

        //加载模板
        $this->load_templates();

        //更改页面连接符
        add_filter( 'document_title_separator', array($this,'document_title_separator'), 10, 1 );

        //禁止转义某些符号
        //add_filter( 'run_wptexturize', '__return_false', 9999);

        add_filter('b2_custom_post_type', array($this,'custom_name'), 1);

        add_action('wp_head', array($this,'hook_js'));

        // add_action('wp',array($this,'footer_action'),999);
        // add_filter('autoptimize_filter_js_movelast',array($this,'my_ao_override_movelast'),10,1);

    }

    public function my_ao_override_movelast($movelast){
        $movelast[]="b2-last";
        return $movelast;
    }

    public function hook_js_footer(){
        ?>
        <script>
            if(typeof lazyLoadInstance === 'undefined'){
                var lazyLoadInstance = new LazyLoad({
                    elements_selector: ".lazy",
                    threshold: 0,
                });
            }
        </script>
        <?php
    }

    public function footer_action(){
        add_action('wp_footer', array($this,'hook_js_footer'),99999);
    }

    public function hook_js(){
        ?>
        <script>
            function b2loadScript(url, id,callback){
                var script = document.createElement ("script");
                script.type = "text/javascript";
                script.id = id;
                if (script.readyState){
                    script.onreadystatechange = function(){
                        if (script.readyState == "loaded" || script.readyState == "complete"){
                            script.onreadystatechange = null;
                            callback();
                        }
                    };
                } else {
                    script.onload = function(){
                        callback();
                    }
                }
                script.src = url;
                document.getElementsByTagName("head")[0].appendChild(script);
            }
            function b2loadStyle(url, id,callback){
                var script = document.createElement ("link");
                script.type = "text/css";
                script.rel = "stylesheet";
                script.id = id;
                if (script.readyState){
                    script.onreadystatechange = function(){
                        if (script.readyState == "loaded" || script.readyState == "complete"){
                            script.onreadystatechange = null;
                            callback();
                        }
                    };
                } else {
                    script.onload = function(){
                        callback();
                    }
                }
                script.href = url;
                document.getElementsByTagName("head")[0].appendChild(script);
            }
            function b2getCookie(name){
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                }
                return null;
            }

            function b2setCookie(name,value,days){
                days = days ? days : 100;
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days*24*60*60*1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "")  + expires + "; path=/";
            }

            function b2delCookie(name){
                document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            }
        </script>
        <?php
    }

    // b2loadScript('/Assets/fontend/library/darkreader.js?ver=<?php echo B2_VERSION;','b2-dark',()=>{
    // 	DarkReader.enable({
    // 		brightness: 100,
    // 		contrast: 110,
    // 		sepia: 30
    // 	});
    // })

    public function custom_name($data){

        if(!b2_get_option('document_main','document_open')){
            unset($data['document']);
        }

        if(!b2_get_option('shop_main','shop_open')){
            unset($data['shop']);
        }

        if(!b2_get_option('newsflashes_main','newsflashes_open')){
            unset($data['newsflashes']);
        }

        if(!b2_get_option('circle_main','circle_open')){
            unset($data['circle']);
        }

		if(!b2_get_option('links_main','link_open')){
            unset($data['links']);
        }
		
        return $data;
        
    }

    public function regal_tag($tag, $handle, $src){
        switch ($handle) {
            //case 'toasted':
            //case 'sliders':
            //case 'packery':
            //case 'timeago':
            //case 'sliders-fade':
            // case 'ngprogress-js':
            //case 'sliders-fade':
            //case 'b2-js-zooming':
            //case 'b2-autosize':
            case 'scrollto-js':
            case 'lazyload-js':
            // case 'clipboard':
            //case 'tooltip-js':
            //case 'sticky-js':
            // case 'weixin':
                return '<script src='.$src.' async></script>';
            default:
                return '<script src='.$src.'></script>';
        }
    }

    /**
     * 更改页面标题连接符
     *
     * @param string $sep
     *
     * @return string 网页标题连接符号
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    function document_title_separator( $sep ) {
        return b2_get_option('normal_main','separator');
    }

    /**
     * 加载前台使用的CSS和JS文件
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function setup_frontend_scripts(){

        //加载css rest
        wp_enqueue_style( 'b2-style-main', get_stylesheet_uri() , array() , B2_VERSION , 'all');

        //加载主题样式
        wp_enqueue_style( 'b2-style', B2_THEME_URI.'/Assets/fontend/style.css' , array() , B2_VERSION , 'all');

        //暗黑模式
        // wp_enqueue_style( 'b2-dark', B2_THEME_URI.'/Assets/fontend/dark.css' , array() , B2_VERSION , 'all');

        //幻灯样式
        wp_enqueue_style( 'b2-sliders', B2_THEME_URI.'/Assets/fontend/library/flickity.css' , array() , B2_VERSION , 'all');

        //progress css
        // wp_enqueue_style( 'ngprogress-css', B2_THEME_URI.'/Assets/fontend/library/nprogress.css' , array() , B2_VERSION , 'all');

        //加载字体
        wp_enqueue_style( 'b2-fonts', '//at.alicdn.com/t/c/font_2579934_s72o9zozq1m.css' , array() , B2_VERSION , 'all');

        $chat_open = b2_get_option('template_aside','aside_chat_type');

        if($chat_open == 'crisp'){
            
            wp_enqueue_script( 'crisp', 'https://client.crisp.chat/l.js', array(), B2_VERSION , true );
            wp_add_inline_script( 'crisp', 'window.$crisp=[];
            window.CRISP_WEBSITE_ID="'.b2_get_option('template_aside','aside_chat_crisp_id').'";','before');
        }
        // wp_enqueue_script( 'b2dark', B2_THEME_URI.'/Assets/fontend/library/darkreader.js', array(), B2_VERSION , false );
        //加载Vue
        wp_enqueue_script( 'vue', B2_THEME_URI.'/Assets/fontend/library/vue.min.js', array(), B2_VERSION , true );

        //加载Vuex
        wp_enqueue_script( 'vuex', B2_THEME_URI.'/Assets/fontend/library/vuex.min.js', array(), B2_VERSION , true );

        //clipboard
        wp_enqueue_script( 'clipboard', B2_THEME_URI.'/Assets/fontend/library/clipboard.min.js' , array() , B2_VERSION , true);
        
        //加载axios
        wp_enqueue_script( 'axios', B2_THEME_URI.'/Assets/fontend/library/axios.min.js', array(), B2_VERSION , true );

        //加载QS
        wp_enqueue_script( 'qs', B2_THEME_URI.'/Assets/fontend/library/qs.min.js', array(), B2_VERSION , true );

        //幻灯
        wp_enqueue_script( 'sliders', B2_THEME_URI.'/Assets/fontend/library/flickity.pkgd.min.js', array(), B2_VERSION , true );

        wp_enqueue_script( 'imagesloaded', B2_THEME_URI.'/Assets/fontend/library/imagesloaded.pkgd.min.js', array(), B2_VERSION , true );

        wp_enqueue_script( 'qrious', B2_THEME_URI.'/Assets/fontend/library/qrious.min.js', array(), B2_VERSION , true );

        //瀑布流
        wp_enqueue_script( 'packery', B2_THEME_URI.'/Assets/fontend/library/packery.pkgd.min.js', array(), B2_VERSION , true );

        //timeago
        wp_enqueue_script( 'timeago', B2_THEME_URI.'/Assets/fontend/library/timeago.min.js', array(), B2_VERSION , true );

        //message
        wp_enqueue_script( 'message', B2_THEME_URI.'/Assets/fontend/library/message.min.js', array(), B2_VERSION , true );

        //progress js
        // wp_enqueue_script( 'ngprogress-js', B2_THEME_URI.'/Assets/fontend/library/nprogress.js', array(), B2_VERSION , true );

        //图片缩放
        wp_enqueue_script( 'b2-js-zooming', B2_THEME_URI.'/Assets/fontend/library/zooming.min.js', array(), B2_VERSION , true );

        //评论框自动长高
        wp_enqueue_script( 'b2-autosize', B2_THEME_URI.'/Assets/fontend/library/autosize.min.js', array(), B2_VERSION , true );

        //跳转
        wp_enqueue_script( 'scrollto-js', B2_THEME_URI.'/Assets/fontend/library/vue-scrollto.js', array(), B2_VERSION , true );

        //跟随滚动
        wp_enqueue_script( 'sticky-js', B2_THEME_URI.'/Assets/fontend/library/sticky-sidebar.min.js', array(), B2_VERSION , true );

        //懒加载
        wp_enqueue_script( 'lazyload-js', B2_THEME_URI.'/Assets/fontend/library/lazyload.min.js', array(), B2_VERSION , true );

        if((int)b2_get_option('template_main','prettify_load') && is_singular()){
           wp_enqueue_script( 'prettify', B2_THEME_URI.'/Assets/fontend/library/prettify.min.js', array(), B2_VERSION , true );
        }

        //加载微信js
        wp_enqueue_script( 'weixin', '//res.wx.qq.com/open/js/jweixin-1.2.0.js', array(), B2_VERSION , true );

        wp_enqueue_script( 'default-passive-events', B2_THEME_URI.'/Assets/fontend/library/default-passive-events.js', array(), B2_VERSION , true );

        //加载js rest
        wp_enqueue_script( 'b2-js-main', B2_THEME_URI.'/Assets/fontend/main.js', array(), B2_VERSION , true );

        // wp_enqueue_script( 'b2-js-form2object', B2_THEME_URI.'/Assets/fontend/library/formToObject.min.js', array(), B2_VERSION , true );

        if(is_author()){
            wp_enqueue_script( 'b2-author', B2_THEME_URI.'/Assets/fontend/author.js', array(), B2_VERSION , true );
        }

        if((is_single() || is_page()) && !is_singular('circle') && !is_front_page()){
            wp_enqueue_script( 'b2-js-scrollbar', B2_THEME_URI.'/Assets/fontend/library/perfect-scrollbar.min.js', array(), B2_VERSION , true );
            wp_enqueue_script( 'b2-js-sketchpad', B2_THEME_URI.'/Assets/fontend/library/sketchpad.js', array(), B2_VERSION , true );
            wp_enqueue_script( 'b2-js-single', B2_THEME_URI.'/Assets/fontend/single.js', array(), B2_VERSION , true );
            wp_enqueue_style( 'b2_block_css',B2_THEME_URI.'/Assets/admin/gd_block.css', array() , B2_VERSION , 'all');
            
            // if((int)b2_get_option('template_main','prettify_load')){
            //     wp_enqueue_script( 'prettify', B2_THEME_URI.'/Assets/fontend/library/prettify.min.js', array(), B2_VERSION , true );
            // }
        }

        if(apply_filters('b2_is_page', 'ajaxupdate')){
            wp_enqueue_script( 'b2-ajaxupdate', B2_THEME_URI.'/Assets/fontend/ajaxupdate.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'gold') || apply_filters('b2_is_page', 'gold-top')){
            wp_enqueue_script( 'b2-js-gold', B2_THEME_URI.'/Assets/fontend/gold.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'vips')){
            wp_enqueue_script( 'b2-js-vip', B2_THEME_URI.'/Assets/fontend/vips.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'message')){
            wp_enqueue_script( 'b2-js-vip', B2_THEME_URI.'/Assets/fontend/message.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'verify')){
            wp_enqueue_script( 'b2-js-verify', B2_THEME_URI.'/Assets/fontend/verify.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'task')){
            wp_enqueue_script( 'b2-js-task', B2_THEME_URI.'/Assets/fontend/task.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'mission')){
            wp_enqueue_script( 'b2-js-mission', B2_THEME_URI.'/Assets/fontend/mission.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'write')){
            wp_enqueue_script( 'b2-normal-editor', B2_THEME_URI.'/Assets/fontend/library/tinymce/tinymce.min.js', array(), B2_VERSION , true );
            wp_enqueue_script( 'b2-js-write', B2_THEME_URI.'/Assets/fontend/write.js', array(), B2_VERSION , true );

            wp_enqueue_style( 'b2-write-css', B2_THEME_URI.'/Assets/fontend/write.css', array(), B2_VERSION , 'all' );
        }

        if(apply_filters('b2_is_page', 'shop')){
            wp_enqueue_style( 'b2-shop-css', B2_THEME_URI.'/Assets/fontend/shop.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-shop', B2_THEME_URI.'/Assets/fontend/shop.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'dark-room')){
            wp_enqueue_script( 'b2-js-shop', B2_THEME_URI.'/Assets/fontend/dark-room.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'carts')){
            wp_enqueue_style( 'b2-carts-css', B2_THEME_URI.'/Assets/fontend/carts.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-carts', B2_THEME_URI.'/Assets/fontend/carts.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'document')){
            wp_enqueue_style( 'b2-document-css', B2_THEME_URI.'/Assets/fontend/document.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-document', B2_THEME_URI.'/Assets/fontend/document.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'links')){
            // wp_enqueue_style( 'b2-links-css', B2_THEME_URI.'/Assets/fontend/links.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-links', B2_THEME_URI.'/Assets/fontend/links.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'newsflashes')){
            wp_enqueue_style( 'b2-newsflashes-css', B2_THEME_URI.'/Assets/fontend/newsflashes.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-newsflashes', B2_THEME_URI.'/Assets/fontend/newsflashes.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'distribution')){
            wp_enqueue_script( 'b2-js-distribution', B2_THEME_URI.'/Assets/fontend/distribution.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'circle')){
            wp_enqueue_style( 'b2-circle-css', B2_THEME_URI.'/Assets/fontend/circle.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-circle', B2_THEME_URI.'/Assets/fontend/circle.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'po-infomation')){
            wp_enqueue_script( 'b2-normal-editor', B2_THEME_URI.'/Assets/fontend/library/tinymce/tinymce.min.js', array(), B2_VERSION , true );
            wp_enqueue_style( 'b2-write-css', B2_THEME_URI.'/Assets/fontend/write.css', array(), B2_VERSION , 'all' );
        }

        if(apply_filters('b2_is_page', 'infomation') || apply_filters('b2_is_page', 'po-infomation')){
            wp_enqueue_style( 'b2-infomation-css', B2_THEME_URI.'/Assets/fontend/infomation.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-infomation', B2_THEME_URI.'/Assets/fontend/infomation.js', array(), B2_VERSION , true );
        }

        if(apply_filters('b2_is_page', 'po-ask')){
            wp_enqueue_script( 'b2-normal-editor', B2_THEME_URI.'/Assets/fontend/library/tinymce/tinymce.min.js', array(), B2_VERSION , true );
            wp_enqueue_style( 'b2-write-css', B2_THEME_URI.'/Assets/fontend/write.css', array(), B2_VERSION , 'all' );
        }

        if(apply_filters('b2_is_page', 'ask') || apply_filters('b2_is_page', 'po-ask')){
            wp_enqueue_style( 'b2-ask-css', B2_THEME_URI.'/Assets/fontend/ask.css', array(), B2_VERSION , 'all' );
            wp_enqueue_script( 'b2-js-ask', B2_THEME_URI.'/Assets/fontend/ask.js', array(), B2_VERSION , true );
        }
        
        if(is_singular( 'ask' )){
            wp_enqueue_script( 'b2-js-ask-single', B2_THEME_URI.'/Assets/fontend/ask-single.js', array(), B2_VERSION , true );
        }

        //加载移动端主题样式
        wp_enqueue_style( 'b2-mobile', B2_THEME_URI.'/Assets/fontend/mobile.css' , array() , B2_VERSION , 'all');

        //自定义样式
        $css = '[v-cloak]{
            display: none!important
        }';

        //字体

        $fonts = b2_get_option('template_main','wrapper_fonts');

        if($fonts !== 0){
            if(!wp_is_mobile()){
                if($fonts == 1){
                    $css .= '
                    @font-face {
                        font-family: "OPPOSans2";
                        src: url("https://cdn.jsdelivr.net/gh/liruchun/b2procdn/OPPOSans-Regular.woff");
                        font-display: swap;
                    }
                    body,button,select,input,textarea{
                        font-family: OPPOSans2,-apple-system,BlinkMacSystemFont,Helvetica Neue,PingFang SC,Microsoft YaHei,Source Han Sans SC,Noto Sans CJK SC,WenQuanYi Micro Hei,sans-serif;
                    }
                    ';
                }
    
                if($fonts == 2){
                    $css .= '
                    @font-face {
                        font-family:HarmonyOS_Sans_SC_Medium;
                        src: url(\'https://cdn.jsdelivr.net/gh/liruchun/b2procdn/HarmonyOS_Sans_SC_Medium.woff2\') format(\'woff2\'),
                             url(\'https://cdn.jsdelivr.net/gh/liruchun/b2procdn/HarmonyOS_Sans_SC_Medium.woff\') format(\'woff\');
                             font-display: swap;
                    }
                    body,button,select,input,textarea{
                        font-family: HarmonyOS_Sans_SC_Medium,-apple-system,BlinkMacSystemFont,Helvetica Neue,PingFang SC,Microsoft YaHei,Source Han Sans SC,Noto Sans CJK SC,WenQuanYi Micro Hei,sans-serif;
                    }
                    ';
                }
    
                if($fonts == 3){
                    wp_enqueue_style( 'Noto-fonts', apply_filters('b2_noto_fonts_link', 'https://fonts.loli.net/css2?family=Noto+Serif+SC&display=swap'), array(), B2_VERSION , 'all' );
                    $css .= apply_filters('b2_noto_font_family', 'body,button,select,input,textarea{
                        font-family: \'Noto Serif SC\',-apple-system,BlinkMacSystemFont,Helvetica Neue,PingFang SC,Microsoft YaHei,Source Han Sans SC,Noto Sans CJK SC,WenQuanYi Micro Hei,sans-serif;
                    }');
                }
    
                if($fonts == 4){
                    wp_enqueue_style( 'LXGW-WenKai', apply_filters('b2_noto_fonts_link', 'https://cdn.jsdelivr.net/npm/lxgw-wenkai-webfont@1.1.0/style.css'), array(), B2_VERSION , 'all' );
                    $css .= apply_filters('b2_noto_font_family', 'body,button,select,input,textarea{
                        font-family: new-spirit, LXGW WenKai,\'Noto Serif SC\',-apple-system,BlinkMacSystemFont,Helvetica Neue,PingFang SC,Microsoft YaHei,Source Han Sans SC,Noto Sans CJK SC,WenQuanYi Micro Hei,sans-serif;
                    }');
                }
    
                if($fonts == 5){
                    $css .= '
                    @font-face {
                        font-family: "sourcehansans"; /* 这个名字可以自己定义 */
                        src: url("https://cdn.jsdelivr.net/gh/liruchun/b2procdn/sourcehansans/sourcehansans.eot"); /* IE9 Compat Modes */ /*这里以及下面的src后面的地址填的都是自己本地的相对地址*/
                        src: url("https://cdn.jsdelivr.net/gh/liruchun/b2procdn/sourcehansans/sourcehansans.eot?#iefix") format("embedded-opentype"),
                          /* IE6-IE8 */ url("https://cdn.jsdelivr.net/gh/liruchun/b2procdn/sourcehansans/sourcehansans.woff") format("woff"),
                          /* Modern Browsers */ url("https://cdn.jsdelivr.net/gh/liruchun/b2procdn/sourcehansans/sourcehansans.ttf") format("truetype"),
                          /* Safari, Android, iOS */ url("https://cdn.jsdelivr.net/gh/liruchun/b2procdn/sourcehansans/sourcehansans.svg#YourWebFontName")
                            format("svg"); /* Legacy iOS */
                        font-weight: bold;
                        font-style: normal;
                        font-display: swap;
                      }
                      /** div样式 **/
                      body,button,select,input,textarea{
                        font-family: sourcehansans,-apple-system,BlinkMacSystemFont,Helvetica Neue,PingFang SC,Microsoft YaHei,Source Han Sans SC,Noto Sans CJK SC,WenQuanYi Micro Hei,sans-serif;
                    }
                    ';
                }
            }
        }

        $wrapper_width = b2_get_option('template_main','wrapper_width');
        $wrapper_width = preg_replace('/\D/s','',$wrapper_width);
        $css .= '.wrapper{
            width:'.$wrapper_width.'px;
            max-width:100%;
            margin:0 auto;
        }';

        $top_width = b2_get_option('template_top','top_width');
        if($top_width){
            $css .= '.top-style .wrapper{
                width:'.$top_width.';
                max-width:100%
            }
            ';
        }

        $web_color = b2_get_option('template_main','web_color');
        $redius = b2_get_option('template_main','button_radius');
        $logo_width = b2_get_option('normal_main','logo_width');
        $bg_color = b2_get_option('template_main','bg_color');

        // list($r, $g, $b) = sscanf($web_color, "#%02x%02x%02x");
        // list($_r, $_g, $_b) = sscanf($bg_color, "#%02x%02x%02x");

        $bg_img = b2_get_option('template_main','bg_image');
        $gg_img = b2_get_option('template_top','gg_bg_image');

        $bg_repeat = b2_get_option('template_main','bg_image_repeat');

        $footer_img = b2_get_option('template_footer','footer_img');
        $footer_img = strpos($footer_img,'//none') !== false ? '' : '.site-footer{
            background-image: url('.$footer_img.');
        }';

        $rgb = \B2\Modules\Common\FileUpload::hex2rgb($web_color);
        // @font-face {
        //     font-family: font-regular;
        //     src: url(\''.B2_THEME_URI.'/Assets/fonts/sans-regular.woff\');
        // }
        $modal_bg = b2_get_option('normal_main','default_box_bg');
        
        $css .= '
        :root{
            --b2lightcolor:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.2);
            --b2radius:'.$redius.';
            --b2color:'.$web_color.';
            --b2light:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.03);
        }
        .header .login-button button{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.03);
        }
        .header .login-button button.empty{
            background:none
        }
        .news-item-date{
            border:1px solid '.$web_color.';
        }
        .author .news-item-date{
            border:0;
        }
        .news-item-date p span:last-child{
            background:'.$web_color.';
        }
        .widget-newsflashes-box ul::before{
            border-left: 1px dashed rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.2);
        }
        .widget-new-content::before{
            background:'.$web_color.';
        }
        .modal-content{
            background-image: url('.$modal_bg.');
        }
        .d-weight button.picked.text,.d-replay button.picked i,.comment-type button.picked i{
            color:'.$web_color.';
        }
        .d-replay button.text:hover i{
            color:'.$web_color.';
        }
        .slider-info-box {
            border-radius:'.$redius.';
        }
        .button,button{
            background:'.$web_color.';
            border:1px solid '.$web_color.';
            border-radius:'.$redius.';
        }
        .b2-menu-4 ul ul li a img{
            border-radius:'.$redius.';
        }
        input,textarea{
            border-radius:'.$redius.';
        }
        .post-carts-list-row .flickity-button{
            border-radius:'.$redius.';
        }
        button.b2-loading:after{
            border-radius:'.$redius.';
        }
        .bar-middle .bar-normal,.bar-footer,.bar-top,.gdd-quick-link-buy-vip{
            border-top-left-radius:'.$redius.';
            border-bottom-left-radius: '.$redius.'
        }
        .entry-content a.button.empty,.entry-content a.button.text{
            color:'.$web_color.';
        }
        .coll-3-top img{
            border-top-left-radius:'.$redius.';
            border-top-right-radius:'.$redius.';
        }
        .coll-3-bottom li:first-child img{
            border-bottom-left-radius:'.$redius.';
        }
        .coll-3-bottom li:last-child img{
            border-bottom-right-radius:'.$redius.';
        }
        .slider-info::after{
            border-radius:'.$redius.';
        }
        .circle-info{
            border-radius:'.$redius.' '.$redius.' 0 0;
        }
        .b2-bg{
            background-color:'.$web_color.';
        }
        .gdd-quick-link-buy-vip__hover-block,.gdd-quick-link-buy-vip__popover--btn,.gdd-quick-link-buy-vip,.gdd-quick-link-buy-vip__popover{
            background-color:'.$web_color.';
        }
        .b2-page-bg::before{
            background: linear-gradient(to bottom,rgba(0,0,0,0) 40%,'.$bg_color.' 100%);
        }
        .site{
            background-color:'.$bg_color.';
        }
        '.($bg_repeat != 2 ? '.site{
            '.($bg_img ? 'background-image:url('.$bg_img.');' : '').'
            '.($bg_repeat ? 'background-repeat: repeat;' : 'background-repeat: no-repeat;').'
            background-attachment: fixed;
            background-position: center top;
        }' : '').'
        .header-banner{
            '.($gg_img ? 'background-image:url('.$gg_img.')' : '').'
        }
        .b2-radius{
            border-radius:'.$redius.';
        }
        .ads-box img{
            border-radius:'.$redius.';
        }
        .post-style-4-top,.post-style-2-top-header,.tax-header .wrapper.box{
            border-radius:'.$redius.' '.$redius.' 0 0;
        }
        .entry-content blockquote,.content-excerpt{
            border-radius:'.$redius.';
        }
        .user-sidebar-info.active{
            border-radius:'.$redius.';
        }
        .dmsg-header a{
            color:'.$web_color.';
        }
        .user-edit-button{
            color:'.$web_color.'
        }
        .b2-color{
            color:'.$web_color.'!important
        }
        .b2-light,.newsflashes-nav-in ul li.current-menu-item a{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.18)
        }
        .b2-light-dark{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.52)
        }
        .b2-light-bg{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.12)
        }
        .b2-menu-1 .sub-menu-0 li a{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.08)
        }
        .b2-menu-1 .sub-menu-0 li:hover a{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.6);
            color:#fff;
        }
        .topic-footer-left button.picked,.single .post-list-cat a,.saf-z button.picked,.news-vote-up .isset, .news-vote-down .isset,.w-d-list.gujia button,.w-d-download span button{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.1);
            color:'.$web_color.'!important
        }
        .po-topic-tools-right .button-sm{
            color:'.$web_color.'
        }
        .author-links .picked a, .collections-menu .current{
            background-color:'.$web_color.';
            color:#fff
        }
        .b2-widget-hot-circle .b2-widget-title button.picked:before{
            border-color: transparent transparent '.$web_color.'!important
        }
        .login-form-item input{
            border-radius:'.$redius.';
        }
        .topic-child-list ul{
            border-radius:'.$redius.';
        }
        .b2-loading path {
            fill: '.$web_color.'
        }
        .header-search-tpye a.picked{
            border-color:'.$web_color.'
        }
        button.empty,.button.empty,li.current-menu-item > a,.top-menu-hide:hover .more,.header .top-menu ul li.depth-0:hover > a .b2-jt-block-down,button.text{
            color:'.$web_color.'
        }
        input,textarea{
            caret-color:'.$web_color.'; 
        }
        .login-form-item input:focus{
            border-color:'.$web_color.'
        }
        .login-form-item input:focus + span{
            color:'.$web_color.'
        }
        .mobile-footer-center i{
            background:'.$web_color.'
        }
        .login-box-content a{
            color:'.$web_color.'
        }
        .verify-number.picked span{
            background:'.$web_color.'
        }
        .verify-header::after{
            color:'.$web_color.'
        }
        .top-user-box-drop li a i{
            color:'.$web_color.'
        }
        #bigTriangleColor path{
            fill: '.$bg_color.';
            stroke: '.$bg_color.';
        }
        .post-list-cats a:hover{
            color:'.$web_color.';
        }
        trix-toolbar .trix-button.trix-active{
            color:'.$web_color.';
        }
        .picked.post-load-button:after{
            border-color:'.$web_color.' transparent transparent transparent;
        }
        .task-day-list li i{
            color:'.$web_color.'
        }
        .task-day-list li .task-finish-icon i{
            background:'.$web_color.'
        }
        .bar-item-desc{
            background:'.$web_color.';
        }
        .bar-user-info-row-title > a span:first-child::before{
            background:'.$web_color.';
        }
        .bar-item.active i{
            color:'.$web_color.'
        }
        .bar-user-info .bar-mission-action{
            color:'.$web_color.'
        }
        .gold-table.picked:after{
            border-color:'.$web_color.'
        }
        .gold-table.picked{
            color:'.$web_color.'
        }
        .user-sidebar-info p i{
            color:'.$web_color.'
        }
        .user-sidebar-info.active p{
            color:'.$web_color.'
        }
        .picked.post-load-button span{
            color:'.$web_color.';
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.18)!important
        }
        .post-carts-list-row .next svg,.post-carts-list-row .previous svg{
            color:'.$web_color.';
        }
        .picked.post-load-button:before{
            background-color:'.$web_color.'
        }
        .aside-carts-price-left span{
            color:'.$web_color.'
        }
        .top-user-avatar img,.header-user .top-user-box,.social-top .top-user-avatar img{
            border-radius:'.$redius.';
        }
        .link-in:hover{
            color:'.$web_color.'
        }
        @media screen and (max-width:768px){
            .aside-bar .bar-item:hover i{
                color:'.$web_color.'
            }
            .post-video-list li.picked .post-video-list-link{
                color:'.$web_color.';
                border-color:'.$web_color.'
            }
            .post-style-2-top-header{
                border-bottom:8px solid '.$bg_color.';
            }
            .po-form-box {
                border-radius:'.$redius.';
            }
            .circle-desc{
                border-radius:0 0 '.$redius.' '.$redius.';  
            }
        }
        .circle-admin-info>div:hover{
            border-color:'.$web_color.';
        }
        .circle-admin-info>div:hover span,.circle-admin-info>div:hover i{
            color:'.$web_color.';
            opacity: 1;
        }
        .bar-top{
            background:'.$web_color.'
        }
        .bar-item.bar-qrcode:hover i{
            color:'.$web_color.'
        }
        .b2-color-bg{
            background-color:'.$web_color.'
        }
        .b2-color{
            color:'.$web_color.'
        }
        .b2-hover a{
            color:'.$web_color.'
        }
        .b2-hover a:hover{
            text-decoration: underline;
        }
        .filter-items a.current,.single-newsflashes .single-tags span,.single-infomation .single-tags span{
            color:'.$web_color.';
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.18)
        }
        .circle-vote{
            background:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.04)
        }
        .user-sidebar-info.active{
            background:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.04)
        }
        .user-w-qd-list-title{
            background-color:'.$web_color.'
        }
        #video-list ul li > div:hover{
            background-color:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.04)
        }
        .post-5 .post-info h2::before{
            background-color:'.$web_color.'
        }
        .tox .tox-tbtn--enabled svg{
            fill:'.$web_color.'!important
        }
        .entry-content a,.entry-content .content-show-roles > p a,.entry-content > ul li a,.content-show-roles > li a,.entry-content > ol li a{
            color:'.$web_color.';
        }
        .entry-content .file-down-box a:hover{
            color:'.$web_color.';
            border:1px solid '.$web_color.';
        }
        .entry-content h2::before{
            color:'.$web_color.';
        }
        .header-banner-left .menu li.current-menu-item a:after{
            background:'.$web_color.';
        }
        .user-w-announcement li a::before{
            background-color:'.$web_color.';
        }
        .topic-footer-right button{
            color:'.$web_color.'
        }
        .content-user-money span{
            color:'.$web_color.';
            background:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.08)
        }
        .vote-type button.picked{
            color:'.$web_color.';
        }
        .post-video-table ul li.picked{
            border-bottom:2px solid '.$web_color.';
        }
        .create-form-item button.picked{
            border-color:'.$web_color.';
        }
        .b2-widget-hot-circle .b2-widget-title button.picked{
            color:'.$web_color.';
        }
        .topic-type-menu button.picked{
            color:#fff;
            background:'.$web_color.';
        }
        .circle-topic-role{
            border:1px solid rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', 0.4)
        }
        .circle-topic-role:before{
            border-color: transparent transparent '.$web_color.';
        }
        .topic-content-text p a{
            color:'.$web_color.';
        }
        '.$footer_img;

        $show_footer_menu = b2_get_option('template_footer','footer_menu_show');
        if(!$show_footer_menu){
            $css .= '.mobile-footer-menu{display:none!important}';
        }

        //页面间隔
        $css .= '
        .home_row_0.module-search{
            margin-top:-'.B2_GAP.'px;
        }
        .home_row_0.home_row_bg_img{
            margin-top:-'.B2_GAP.'px;
        }
        .shop-cats .shop-cats-item{
            margin-right:'.B2_GAP.'px;
        }
        .mg-r{
            margin-right:'.B2_GAP.'px;
        }
        .mg-b{
            margin-bottom:'.B2_GAP.'px;
        }
        .mg-t{
            margin-top:'.B2_GAP.'px;
        }
        .mg-l{
            margin-left:'.B2_GAP.'px;
        }
        .b2-mg{
            margin:'.B2_GAP.'px;
        }
        .b2-pd{
            padding:'.B2_GAP.'px;
        }
        .b2_gap,.shop-normal-list,.shop-category,.user-search-list,.home-collection .collection-out{
            margin-right:-'.B2_GAP.'px;
            margin-bottom:-'.B2_GAP.'px;
            padding:0
        }
        .post-3-li-dubble .b2_gap{
            margin-right:-'.B2_GAP.'px;
            margin-bottom:-'.B2_GAP.'px;
        }
        .b2_gap>li .item-in,.shop-list-item,.shop-normal-item-in,.user-search-list li > div,.home-collection .home-collection-content,.post-3.post-3-li-dubble .b2_gap>li .item-in{
            margin-bottom:'.B2_GAP.'px;
            margin-right:'.B2_GAP.'px;
            overflow: hidden;
        }
        .b2-pd-r{
            padding-right:'.B2_GAP.'px;
        }
        .widget-area section + section{
            margin-top:'.B2_GAP.'px;
        }
        .b2-pd,.b2-padding{
            padding:'.B2_GAP.'px;
        }
        .single-post-normal .single-article{
            margin-right:'.B2_GAP.'px;
        }
        .site-footer .widget{
            padding:0 '.B2_GAP.'px;
        }
        .author-page-right{
            margin-right:'.B2_GAP.'px;
        }
        .single-article{
            margin-bottom:'.B2_GAP.'px;
        }
        .home-collection .flickity-prev-next-button.next{
            right:-'.(-B2_GAP+32).'px;
        }
        .post-style-5-top{
            margin-top:-'.B2_GAP.'px
        }
        .home-collection-title{
            padding:12px '.B2_GAP.'px
        }
        .home_row_bg,.home_row_bg_img{
            padding:'.(B2_GAP*2).'px 0
        }
        .shop-coupon-box{
            margin-right:-'.B2_GAP.'px
        }
        .shop-box-row .shop-coupon-item .stamp{
            margin-right:'.B2_GAP.'px;
            margin-bottom:'.B2_GAP.'px;
        }
        .mg-t-{
            margin-top:-'.B2_GAP.'px;
        }
        .collection-box{
            margin:-'.round(B2_GAP/2,0).'px
        }
        .collection-item{
            padding:'.round(B2_GAP/2,0).'px
        }
        .site-footer-widget-in{
            margin:0 -'.B2_GAP.'px;
        }
        .module-sliders.home_row_bg{
            margin-top:-'.B2_GAP.'px;
        }
        .home_row_0.homw-row-full.module-sliders{
            margin-top:-'.B2_GAP.'px;
        }
        .widget-area.widget-area-left{
            padding-right:'.B2_SIDEBAR_GAP.'px;
        }
        ';

        $css .= self::sidebar_css();

        //底部第一层文字颜色
        $footer_1 = b2_get_option('template_footer','footer_text_color');
        $footer_2 = b2_get_option('template_footer','footer_nav_text_color');
        $footer_color = '
            .footer{
                color:'.$footer_1.';
            }
            .footer-links{
                color:'.$footer_2 .';
            }
            .footer-bottom{
                color:'.$footer_2 .';
            }
        ';

        $css .= $footer_color;

        $is_post = is_singular('post');
        $is_circle_single = is_singular('circle');
        $page = is_page();

        $sidebars_widgets = wp_get_sidebars_widgets();

        if(($is_post && empty($sidebars_widgets['sidebar-3'])) || ($page && empty($sidebars_widgets['sidebar-4']))){
            $css .= '
            .single .content-area, .page .content-area{
                margin-right:auto;
                margin-left:auto;
            }';
        }
        
        $is_stream = apply_filters('b2_is_page', 'stream');

        if($is_circle_single && empty($sidebars_widgets['sidebar-12'])){
            $css .='.single-circle .b2-single-content, .circle-topic-edit.b2-single-content {
                width: 100%;
                max-width: 620px;
            }';
        }

        wp_add_inline_style( 'b2-style', $css );
        wp_add_inline_style( 'parent-style', $css );
    }

    public static function show_sidebar(){

        if(is_home() || is_front_page()) return true;

        $term = get_queried_object();
        
        $shop_category = isset($term->taxonomy) ? $term->taxonomy : '';
        $shop_type = get_query_var('b2_shop_type');

        //商品侧边栏
        if($shop_type || $shop_category === 'shoptype' || is_singular('shop') || is_post_type_archive('shop') || is_tax('shoptype')){
            return true;
        }

        //文档侧边栏
        if($shop_type || $shop_category === 'document_cat' || is_singular('document') || is_post_type_archive('document') || is_tax('document_cat')){
            return false;
        }
        
        if(isset($term->term_id)){
            $sidebar = get_term_meta($term->term_id,'b2_show_sidebar',true);
            return $sidebar !== '' ? (int)$sidebar : true;
        }

        $page_name = get_query_var('b2_page');
        if(apply_filters('b2_is_page', $page_name)) return true;
        
        //圈子是否显示侧边栏
        if(is_singular('circle') || is_post_type_archive('circle') || is_tax('circle_tags') || get_query_var('circle_tags')) return true;

        // if(is_tax() || is_category() || is_archive()) return true;
        // return 111;
        if(is_singular()){
            $post_id = get_the_id();

            if(is_singular('post')){
                $post_style = Single::get_single_post_settings($post_id,'single_post_style');
                if($post_style === 'post-style-2'){
                    return false;
                }else{
                    return Single::get_single_post_settings($post_id,'single_post_sidebar_show');
                }
            }

            return true;
        }

        return false;
    }

    public static function sidebar_css(){

        $css = '';
        $width = b2_get_option('template_main','sidebar_width');
        $width = $width ? $width : 300;
        // if(self::show_sidebar()){
            $css .= '.widget-area{
                width:'.$width.'px;
                min-width:'.$width.'px;
                margin-left:'.B2_SIDEBAR_GAP.'px;
                max-width:100%;
            }
            .widget-area-left.widget-area{
                width:'.($width - 80).'px;
                max-width:'.($width - 80).'px;
                min-width:'.($width - 80).'px;
            }
            .post-type-archive-circle #secondary.widget-area,.tax-circle_tags #secondary.widget-area,.page-template-page-circle #secondary.widget-area{
                width:'.($width - 20).'px;
                max-width:'.($width - 20).'px;
                min-width:'.($width - 20).'px;
            }
            .single .content-area,.page .content-area,.links-register .content-area{
                max-width: calc(100% - '.(B2_SIDEBAR_GAP + $width).'px);
                margin: 0 auto;
                flex:1
            }
            .page-template-pageTemplatespage-index-php .content-area{
                max-width:100%
            }
            ';

            $page_width = b2_get_option('template_main','wrapper_width');
            $page_width = preg_replace('/\D/s','',$page_width);
            
            $css .= '
                .tax-collection .content-area,
                .tax-newsflashes_tags .content-area,
                .post-type-archive-newsflashes .content-area,.page-template-page-newsflashes .content-area
                .all-circles.content-area,
                .announcement-page.content-area,
                .single-announcement .content-area,
                .post-style-2.single .content-area,
                .create-circle.content-area,
                .mission-page.wrapper,
                ,#carts .vip-page{
                    max-width:'.($page_width-$width+40).'px;
                    width:100%;
                }
            ';

        
        return $css;
    }

    /**
     * 加载前台的各个模块
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function load_templates(){

        //加载文章形式(公告)
        $announcement = new PostType\Announcement();
        $announcement->init();

        //加载顶部模块
        $header = new Header();
        $header->init();

        //加载首页
        $index = new Index();
        $index->init();

        //文章内页方法
        $single = new Single();
        $single->init();

        //存档页面
        $archive = new Archive();
        $archive->init();

        //专题页面
        $collection = new Collection();
        $collection->init();

        //加载Vue组件Template
        $vue_template = new VueTemplates();
        $vue_template->init();

        // //加载footer模块
        $footer = new Footer();
        $footer->init();

        //加载小工具
        $widgets = new Widgets();
        $widgets->init();
    }

}