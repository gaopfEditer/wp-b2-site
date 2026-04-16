<?php namespace B2\Modules\Templates;

use B2\Modules\Common\Post;
use B2\Modules\Common\Shop;

class Single{

    public function init(){
        add_action('b2_single_post_content_before',array($this,'content_audio'),5);
        //add_action('b2_single_article_after',array($this,'post_download_content'),4);
        add_filter( 'the_content', array($this,'post_download'),10,1); 
        //add_filter( 'b2_single_post_content_after', array($this,'post_download_content'),10); 
        add_action('b2_single_article_after',array($this,'post_ds'),5);
        add_action('b2_single_post_content_after',array($this,'copyright_footer'),5);
        add_action('b2_single_article_after',array($this,'content_footer'),6);
        add_action('b2_single_article_after',array($this,'list_tags'),7);

        //加入顶部广告代码
        add_action('b2_single_article_before',array($this,'single_ad_top'),5);

        //加入底部上下文
        $show_next = b2_get_option('template_single','single_show_next_post');
        if($show_next == '' || $show_next == 1){
            add_action('b2_single_content_after',array($this,'next_pre_posts'),4);
        }

        //加入底部广告
        add_action('b2_single_content_after',array($this,'single_ad_bottom'),5);

        //二维码展示
        add_action('b2_single_article_before',array($this,'qrcode_display'),7);

        add_action('b2_single_wrapper_before',array($this,'single_wrapper_before'),7);
 
        //add_action('b2_single_post_down',array($this,'post_download'),8);

        add_action('b2_single_content_after',array($this,'get_related_posts'),4);

        add_action('b2_single_wrapper_before',array($this,'shop_single_top'),5);
    }

    public function next_pre_posts(){
        global $post;
        $post_id = $post->ID;

        $post_style = self::get_single_post_settings($post_id,'single_post_style');

        //如果是视频，不显示上一篇和下一篇
        if($post_style === 'post-style-5') return;

        $post_type = get_post_type($post_id);

        if($post_type !== 'post') return;

        $data = Post::get_pre_next_post($post_id,'category');

        $next_cat = $data['next']['category'];
        $next_html = '';
        foreach ($next_cat as $k => $v) {
            $next_html .= '<a href="'.$v['link'].'" ><span>'.$v['name'].'</span></a>';
        }

        $pre_cat = $data['pre']['category'];

        $pre_html = '';
        foreach ($pre_cat as $k => $v) {
            $pre_html .= '<a href="'.$v['link'].'" ><span>'.$v['name'].'</span></a>';
        }

        if(!empty($data)){
            echo '
                <div class="post-pre-next mg-b">
                    <div class="post-pre">
                        <div class="post-pre-next-in">
                            <div class="post-pre-next-info b2-pd b2-radius box">
                                <div class="next-jt">'.b2_get_icon('b2-arrow-left-s-line').'</div>
                                <div class="post-per-next-cats">'.$pre_html.'</div>
                                <h2><a href="'.$data['pre']['link'].'">'.$data['pre']['title'].'</a></h2>
                                <p>'.$data['pre']['date'].'</p>
                            </div>
                        </div>
                    </div>
                    <div class="post-next">
                        <div class="post-pre-next-in">
                            <div class="post-pre-next-info b2-pd b2-radius box">
                                <div class="post-per-next-cats">'.$next_html.'</div>
                                <h2><a href="'.$data['next']['link'].'">'.$data['next']['title'].'</a></h2>
                                <p>'.$data['next']['date'].'</p>
                                <div class="next-jt">'.b2_get_icon('b2-arrow-right-s-line').'</div>
                            </div>
                        </div>
                    </div>
                </div>
            ';
        }
    }

    public function single_wrapper_before(){
        $post_id = get_the_id();

        $post_style = self::get_single_post_settings($post_id,'single_post_style');

        $sidebar_width = b2_get_option('template_main','sidebar_width');

        $html = '';
        $down_open = get_post_meta($post_id,'b2_open_download',true);

        if($post_style === 'post-style-5'){
            
            //获取post meta
            $post_meta = Post::post_meta($post_id);

            $html = '<div>
                <div class="post-style-5-top" ref="postType5" data-id="'.$post_id.'">
                <div class="wrapper">
                    <div class="post-style-5-video-box">
                        <div class="post-style-5-video-box-in">
                            <div class="post-style-5-video-box-player" v-show="user !== \'\'" v-cloak>
                                <div class="video-role-box" v-if="!user.allow">
                                    
                                    <div class="video-role-title" v-cloak v-if="user.length != 0">
                                        <div class="video-view b2-radius" @click="showAc()">'.b2_get_icon('b2-information-line').'<span>'.__('查看完整视频','b2').'</span></div>
                                    </div>
                                    <div :class="\'video-role-info\'+(show ? \' b2-show\' : \'\')" v-if="user.length != 0" v-cloak>
                                        <div v-cloak v-if="user.role.type == \'dark_room\'">
                                            <div>'.__('小黑屋思过中，禁止观看！','b2').'</div>
                                        </div>
                                        <div class="video-role-login" v-if="user.role.type == \'login\'">
                                            <span class="video-tips">
                                                '.b2_get_icon('b2-lock-2-fill').'<b>'.__('注册会员专属','b2').'</b>
                                            </span>
                                            <p>'.__('您需要登录以后才能查看完整视频','b2').'</p>
                                            <div class="video-view-button">
                                                <button class="empty" @click="login(1)">'.__('登录','b2').'</button>
                                                <button class="empty video-views" @click="play" v-if="showViews">'.b2_get_icon('b2-play-circle-line').__('观看预览视频','b2').'</button>
                                            </div>
                                        </div>
                                        <div class="" v-if="user.role.type == \'comment\'">
                                            <span class="video-tips">
                                                '.b2_get_icon('b2-lock-2-fill').'<b>'.__('评论并刷新后可见','b2').'</b>
                                            </span>
                                            <p style="margin-bottom:40px">'.__('您需要在视频最下面评论并刷新后，方可查看完整视频','b2').'</p>
                                            <button class="empty" @click="goComment()">'.__('去评论','b2').'</button>
                                            <button class="empty video-views" @click="play" v-if="showViews">'.b2_get_icon('b2-play-circle-line').__('观看预览视频','b2').'</button>
                                        </div>
                                        <div class="" v-if="user.role.type == \'credit\'">
                                            <span class="video-tips">
                                                '.b2_get_icon('b2-lock-2-fill').'<b>'.__('积分观看','b2').'</b>
                                            </span>
                                            <p>'.__('支付积分后查看完整视频','b2').'</p>
                                            <div class="video-creidt">
                                                <span>'.b2_get_icon('b2-coin-line').'{{user.role.value}}</span>
                                            </div>
                                            <div class="video-view-button">
                                                <button class="empty" @click="credit()">'.__('积分支付观看','b2').'</button>
                                                <button class="empty video-views" @click="play" v-if="showViews">'.b2_get_icon('b2-play-circle-line').__('观看预览视频','b2').'</button>
                                            </div>
                                        </div>
                                        <div class="" v-if="user.role.type == \'money\'">
                                            <span class="video-tips">
                                                '.b2_get_icon('b2-lock-2-fill').'<b>'.__('付费视频','b2').'</b>
                                            </span>
                                            <p>'.__('支付完成后查看完整视频','b2').'</p>
                                            <div class="video-creidt">
                                                <span>'.B2_MONEY_SYMBOL.'{{user.role.value}}</span>
                                            </div>
                                            <div class="video-view-button">
                                                <button class="empty" @click="pay()">'.__('支付后观看完整视频','b2').'</button>
                                                <button class="empty video-views" @click="play" v-if="showViews">'.b2_get_icon('b2-play-circle-line').__('观看预览视频','b2').'</button>
                                            </div>
                                        </div>
                                        <div class="" v-if="user.role.type == \'role\'">
                                            <span class="video-tips">
                                                '.b2_get_icon('b2-lock-2-fill').'<b>'.__('专属视频','b2').'</b>
                                            </span>
                                            <p>'.__('只允许以下等级用户查看该视频','b2').'</p>
                                            <div class="video-role-list">
                                                <ul>
                                                    <li v-for="item in user.role.value" v-html="item"></li>
                                                </ul>
                                            </div>
                                            <div class="video-view-button">
                                                <a class="empty button" target="_blank" href="'.b2_get_custom_page_url('vips').'">'.b2_get_icon('b2-vip-crown-2-line').'<span>'.__('升级','b2').'</span></a>
                                                <button class="empty video-views" @click="play" v-if="showViews">'.b2_get_icon('b2-play-circle-line').__('观看预览视频','b2').'</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="video-role-box" v-else-if="user.role.type == \'vip\'" v-cloak>
                                    <div class="video-view b2-radius vip-video">'.__('会员专享','b2').'</div>
                                </div>
                                <div id="post-style-5-player" oncontextmenu="return false;"></div>
                            </div>
                        </div>
                    </div>    
                    <div class="post-video-list" style="width:'.$sidebar_width.'px;margin-left:'.B2_GAP.'px;" ref="videoList">
                        <div class="post-video-list-title">
                            <h2>'.__('视频选集','b2').'</h2><span>'.sprintf(__('共%s节','b2'),'<b v-text="videos.length"></b>').'</span>
                        </div>
                        <ul v-show="videos.length > 1" v-cloak ref="videoListIn">
                            <li v-for="(list,i) in videos" @click.stop="select(i)" :class="index == i ? \'picked\' : \'\'">
                                <div class="post-video-list-title" v-if="list.h2"><b v-text="list.h2"></b></div>
                                <div class="post-video-list-link b2-radius">
                                    <div class="video-list-play-icon"><span v-if="index == i">'.b2_get_icon('b2-rhythm-line').'</span><span v-else>'.b2_get_icon('b2-play-mini-fill').'</span></div>
                                    <div>
                                        <div class="video-list-title"><span v-text="list.title"></span></div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                </div>
                <div class="box">
                    <header class="entry-header wrapper">
                        <h1>'.get_the_title().'</h1>
                        <div id="post-meta">
                            <div class="post-meta-row">
                                <ul class="post-meta">
                                    <li> '.\B2\Modules\Templates\Modules\Posts::get_post_cats('target="__blank"',$post_meta,array('cats'),'post_3').'</li>
                                    <li class="single-date">
                                        <span>'.$post_meta['date'].'</span>
                                    </li>
                                    <li class="single-like">
                                        <span>'.b2_get_icon('b2-heart-fill').'<b v-text="postData.up"></b></span>
                                    </li>
                                    <li class="single-eye">
                                        <span>'.b2_get_icon('b2-eye-fill').'<b v-text="postData.views"></b></span>
                                    </li>
                                    <li class="single-edit" v-cloak v-if="userData.is_admin">
                                        <a href="'.get_edit_post_link($post_id).'" target="_blank">'.__('编辑','b2').'</a>
                                    </li>
                                </ul>
                                '.($down_open ? '<div class="single-button-download"><button class="empty b2-radius" @click="scroll">'.b2_get_icon('b2-download-cloud-line').__('前往下载','b2').'</button></div>' : '').'
                            </div>
                            '.(!is_audit_mode() ? '<div class="post-user-info">
                            <div class="post-meta-left">
                                <a class="link-block" href="'.$post_meta['user_link'].'"></a>
                                <div class="avatar-parent"><img class="avatar b2-radius" src="'.$post_meta['user_avatar'].'" />'.($post_meta['user_title'] ? $post_meta['verify_icon'] : '').'</div>
                                <div class="post-user-name"><b>'.$post_meta['user_name'].'</b><span class="user-title">'.$post_meta['user_title'].'</span></div>
                            </div>
                            <div class="post-meta-right">
                            <div class="" v-if="self == false" v-cloak>
                                <button @click="followingAc" class="author-has-follow" v-if="following">'.__('取消关注','b2').'</button>
                                <button @click="followingAc" v-else>'.b2_get_icon('b2-add-line').__('关注','b2').'</button>
                                <button class="empty" @click="dmsg()">'.__('私信','b2').'</button>
                            </div>
                        </div>' : '').'
                            </div>
                        </div>
                    </header>
                    <div class="wrapper post-video-table">
                        <ul>
                            <li :class="table === \'content\' ? \'picked b2-color\' : \'\'" @click="table = \'content\'">'.__('视频介绍','b2').'</li>
                            <li :class="table === \'list\' ? \'picked b2-color\' : \'\'" @click="table = \'list\'">'.__('视频选集','b2').'</li>
                            <li :class="table === \'comment\' ? \'picked b2-color\' : \'\'" @click="table = \'comment\'">'.__('交流讨论','b2').'</li>
                        </ul>
                    </div>
                </div>
            </div>
            ';

        }elseif($post_style === 'post-style-3'){
            $thumb = \B2\Modules\Common\Post::get_post_thumb($post_id);
            $show_sidebar = self::get_single_post_settings($post_id,'single_post_sidebar_show');

            //计算缩略图宽高
            $w = b2_get_option('template_main','wrapper_width');
            $w = preg_replace('/\D/s','',$w);
            $h = ceil($w*0.618);

            $thumb = b2_get_thumb(array(
                'thumb'=>$thumb,
                'width'=>$w,
                'height'=>$h
            ));
            
            $post_meta = \B2\Modules\Common\Post::post_meta($post_id);

            $html = '
            <div class="post-style-3-top" style="height:'.$h.'px">
                <img src="'.$thumb.'" class="img-fliter"/>
                <div class="wrapper">
                    <div class="post-style-3-title '.(!$show_sidebar ? 'content-area' : '').'">
                        <h1>'.get_the_title().'</h1>
                    </div>
                </div>
            </div>';
        }

        echo $html;

    }

    public static function get_tag_list($post_id){
        $tags = wp_get_post_tags(get_the_id());
        if(!$tags) return;
        $html = '<div class="post-tags-meat">';
        foreach ( $tags as $tag ) {
            $thumb = get_term_meta($tag->term_id,'b2_tax_img',true);
            if($thumb){
                $thumb = '<img src="'.b2_get_thumb(array('thumb'=>$thumb,'height'=>32,'width'=>32)).'" />';
            }else{
                $thumb = b2_get_icon('b2-price-tag-3-line');
            }
            $tag_link = get_tag_link( $tag->term_id );
            $html .= '<a class="b2-radius" href="'.esc_url($tag_link).'"><span class="tag-img">'.$thumb.'</span>';
            $html .= '<span class="tag-text">'.esc_attr($tag->name).'</span></a>';
        }
        $html .= '</div>';
        return $html;
    }

    public function content_audio(){

        $post_id = get_the_id();

        //是否允许使用语音朗读
        $allow = self::get_single_post_settings($post_id,'single_show_radio');

        if($allow){
            echo self::audio_html($post_id);
        }
    }

    public function qrcode_display(){

        return;

        $post_style = self::get_single_post_settings(get_the_id(),'single_post_style');

        if($post_style === 'post-style-2') return;

        echo '<div id="fold" :class="[\'fold\',{\'open\':open}]" @click.stop="click" v-cloak data-image="">
        <img src="'.get_post_qrcode().'" />
    </div>';
    }

    public function single_ad_top(){

        $post_id = get_the_id();

        $post_style = self::get_single_post_settings($post_id,'single_post_style');

        $post_ad = self::get_single_post_settings($post_id,'single_post_top_ads');

        if($post_ad === true) return;

        if($post_style === 'post-style-2') return;

        if($post_ad){
            echo '<div class="single-top-html">'.$post_ad.'</div>';
        }
    }

    public function single_ad_bottom(){

        $post_id = get_the_id();

        if(get_post_type($post_id) !== 'post') return;

        $post_style = self::get_single_post_settings($post_id,'single_post_style');

        if($post_style === 'post-style-5') return;

        $post_ad = self::get_single_post_settings($post_id,'single_post_bottom_ads');

        if($post_ad === true) return;

        if($post_ad){
            echo '<div class="single-bottom-html mg-b box b2-radius">'.$post_ad.'</div>';
        }
    }

    public static function audio_html($post_id){

        if(get_post_type($post_id) !== 'post') return;

        $html = '
            <div class="b2-audio-content">
                <audio id="tts_autio_id" ref="audio" :src="url" data-id="'.$post_id.'"></audio>
                <div class="b2-audio-button" @click="play">
                    <i :class="[\'b2font\',!playStatus ? \'b2-play-mini-fill\' : \'b2-stop-mini-fill\']"></i>
                </div>
                <div class="b2-audio-progress">
                    <div>'.__('释放双眼，带上耳机，听听看~！','b2').'</div>
                    <div class="b2-audio-progress-bar">
                        <div class="b2-audio-start-time" v-text="startTime"></div>
                        <div class="b2-audio-start-bar-box">
                            <div class="b2-audio-progress-box" :style="\'width:\'+width"></div>
                        </div>
                        <div class="b2-audio-end-time" v-text="currentTime"></div>
                    </div>
                </div>
            </div>
        ';

        return $html;
    }

    public static function get_content_arr($post_id){
        $text = preg_replace('#\[[^\]]+\]#', '',wp_filter_nohtml_kses(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content',$post_id)))));
        $text = self::str_split_unicode($text,500);
        return $text;
    }

    public static function str_split_unicode($str, $l = 0) {  
        if ($l > 0) {  
            $ret = array();  
            $len = mb_strlen($str, "UTF-8");  
            for ($i = 0; $i < $len; $i += $l) {  
                $ret[] = mb_substr($str, $i, $l, "UTF-8");  
            }  
            return $ret;  
        }  
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);  
    }

    //获取内页的设置项
    public static function get_single_post_settings($post_id,$type){

        if(get_post_type($post_id) !== 'post') return true;

        //默认内页文章样式
        $default_settings = get_option('b2_template_single');
        $default_settings = isset($default_settings['single_style_group']) ? $default_settings['single_style_group'][0] : array();

        $post_style = get_post_meta($post_id,'b2_'.$type,true);

        return $post_style !== '' && $post_style !== 'global' ? $post_style : (isset($default_settings[$type]) ? $default_settings[$type] : '');
    }

    // public function isset_download_widget($post_id){

    //     $isset = false;

    //     $widgets = wp_get_sidebars_widgets();

    //     $post_style = self::get_single_post_settings($post_id,'single_post_style');

    //     if(isset($widgets['sidebar-3']) && !empty($widgets['sidebar-3']) && $post_style != 'post-style-2'){
    //         foreach ($widgets['sidebar-3'] as $v) {
    //             if(strpos($v,'b2-widget-download') !== false){
    //                 $isset = true;
    //             }
    //         }
    //     }

    //     return $isset;
        
    // }

    public function post_download_content(){
        
        echo $this->post_download();
    }

    //下载模块
    public function post_download($content){

        $post_id = get_the_ID();

        if(get_query_var('b2_link_id')) return $content;

        //检查是否开启了下载
        $can_download = get_post_meta($post_id,'b2_open_download',true);

        if(!$can_download) return $content;

        $download_settings = get_post_meta($post_id,'b2_single_post_download_group',true);
        if(empty($download_settings)) return $content;

        $html = '';

        $title = '';
        $count = 0;
        foreach ($download_settings as $k => $v) {
            $count++;

            $t = isset($v['name']) ? $v['name'] : get_the_title($post_id);

            $title .= '<div id="item-name" :class="[\'item-name b2-radius\',{\'picked b2-color\':picked == '.$k.'}]" @click="picked = '.$k.'"><span v-cloak>'.$t.'</span><div class="n-thumb">'.(isset($v['thumb']) && $v['thumb'] ? '<img src="'.b2_get_thumb(array('thumb'=>$v['thumb'],'width'=>40,'height'=>40)).'" />' : '<b>'.$count.'</b>').'</div></div>';
            
        }

        return $content.'
            <div class="download-box mg-b" id="download-box" ref="downloadBox">
            <div>
                '.($count > 1 ? '<div class="item-name-box" ref="downloadTitleBox">'.$title.'</div>' : '').'
                <div class="down-ready">
                    <div class="download-list gujia" ref="gujia">
                        <div class="download-item">
                            <div class="download-thumb" style="\'background-image: url();\'">
                            </div>
                            <div class="download-rights">
                                <h2><span class="gujia-bg"></span></h2>
                                <ul>
                                    <li><span class="gujia-bg"></span></li>
                                    <li><span class="gujia-bg"></span></li>
                                    <li><span class="gujia-bg"></span></li>
                                    <li><span class="gujia-bg"></span></li>
                                    <li><span class="gujia-bg"></span></li>
                                    <li><span class="gujia-bg"></span></li>
                                </ul>
                            </div>
                            <div class="download-info">
                                <h2><span class="gujia-bg"></span></h2>
                                <ul>
                                </ul>
                                <div class="download-current">
                                    <div class=""></div>
                                </div>
                                <div class="download-button-box">
                                    <div class=""></div>
                                    <div class=""></div>
                                    <div class=""></div>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="download-list" v-cloak>
                    <div v-for="(item,index) in list" :class="\'download-item b2-radius \'+(item.current_user.can.allow ? \'allow-down\' : \'not-allow-down\')" v-show="picked == index" v-cloak>
                        <div class="download-rights" v-cloak>
                            <h2>'.b2_get_icon('b2-download-cloud-line1').__('下载权限','b2').'</h2><span class="mobile-show" @click="item.show_role = !item.show_role">'.__('查看','b2').'</span>
                            <ul v-if="item.show_role">
                                <li v-for="right in item.rights" :class="right.lv == item.current_user.lv.lv.lv || right.lv == item.current_user.lv.vip.lv ? \'red\' : \'\'">
                                    <div><span v-text="right.lv_name+\'：\'"></span></div>
                                    <div v-if="right.type == \'money\'">'.B2_MONEY_SYMBOL.'<span v-text="right.value"></span></div>
                                    <div v-if="right.type == \'credit\'">'.b2_get_icon('b2-coin-line').'<span v-text="right.value"></span></div>
                                    <div v-if="right.type == \'free\'">'.__('免费下载','b2').'</div>
                                    <div v-if="right.type == \'comment\'">'.__('评论并刷新后下载','b2').'</div>
                                    <div v-if="right.type == \'login\'">'.__('登录后下载','b2').'</div>
                                    '.apply_filters("b2_download_right_html",'').'
                                </li>
                            </ul>
                        </div>
                        '.do_action('b2_download_box_row').'
                        <div class="download-info">
                            <h2><span v-text="item.name"></span><a :href="item.view" target="_blank" class="download-view button empty text" v-if="item.view">'.__('查看演示','b2').b2_get_icon('b2-arrow-right-s-line').'</a></h2>
                            <ul v-show="item.attrs.length >0">
                                <li v-for="attr in item.attrs">
                                    <span class="download-attr-name">{{attr.name}}：</span>
                                    <span v-html="attr.value"></span>
                                </li>
                            </ul>
                            <div class="download-current">
                                <span>'.__('您当前的等级为','b2').'</span>
                                <span v-if="item.current_user.lv.lv" v-html="item.current_user.lv.lv.icon"></span>
                                <span v-if="item.current_user.lv.vip" v-html="item.current_user.lv.vip.icon"></span>
                                <div class="" v-if="!item.current_user.can.allow">
                                    <span v-if="item.current_user.can.type == \'login\'">
                                    '.__('登录后免费下载','b2').'<a href="javascript:void(0)" onclick="login.show = true;login.loginType = 1">'.__('登录','b2').'</a>
                                    </span>
                                    <span v-else-if="item.current_user.lv.lv.lv == \'dark_room\'">
                                    '.__('小黑屋反思中，不准下载！','b2').'
                                    </span>
                                    <span v-else-if="item.current_user.can.type == \'comment\'">
                                    '.__('评论后刷新页面下载','b2').'<a href="#respond">'.__('评论','b2').'</a>
                                    </span>
                                    <span v-else-if="item.current_user.lv.lv.lv == \'guest\' && !item.current_user.guest">
                                        <span v-show="list[index].rights[0].lv == \'all\'" v-cloak>'.sprintf(__('支付%s以后下载','b2'),'<b><template v-if="item.current_user.can.type == \'credit\'">'.b2_get_icon('b2-coin-line').'</template><template v-else>'.B2_MONEY_SYMBOL.'</template><i v-html="list[index].current_user.can.value"></i></b>').'</span>
                                        '.__('请先','b2').'<a href="javascript:void(0)" onclick="login.show = true;login.loginType = 1">'.__('登录','b2').'</a>
                                    </span>
                                    <span v-else-if="item.current_user.can.type == \'full\'" class="green">
                                        '.sprintf(__('您今天的下载次数（%s次）用完了，请明天再来','b2'),'<b v-text="item.current_user.can.total_count"></b>').'
                                    </span>
                                    <span v-else-if="item.current_user.can.type == \'credit\'">
                                        '.sprintf(__('支付积分%s以后下载','b2'),'<b>'.b2_get_icon('b2-coin-line').'<i v-html="list[index].current_user.can.value"></i></b>').'<a href="javascript:void(0)" @click="credit(index)">'.__('立即支付','b2').'</a>
                                    </span>
                                    <span v-else-if="item.current_user.can.type == \'money\'">
                                        '.sprintf(__('支付%s以后下载','b2'),'<b v-text="\''.B2_MONEY_SYMBOL.'\'+list[index].current_user.can.value"></b>').'<a href="javascript:void(0)" @click="pay(index)">'.__('立即支付','b2').'</a>
                                    </span>
                                    '.apply_filters("b2_download_current_html",'').'
                                    <span v-else>
                                        '.__('您当前的用户组不允许下载','b2').'<a href="'.b2_get_custom_page_url('vips').'" target="_blank">'.__('升级会员','b2').'</a>
                                    </span>
                                </div>
                                <div class="" v-else>
                                    <span v-if="item.current_user.current_guest == 0 || item.current_user.can.free_down" class="green">
                                        '.__('您已获得下载权限','b2').'
                                    </span>
                                    <span class="green" v-else>
                                        '.__('您可以每天下载资源','b2').'<b v-text="item.current_user.can.total_count"></b>'.__('次，今日剩余','b2').'<b v-text="item.current_user.can.count"></b>'.__('次','b2').'
                                    </span>
                                </div>
                            </div>
                            <div class="download-button-box">
                                <button @click="go(b.link,item.current_user.can.allow,item,index)" class="button" v-text="b.name" v-for="b in item.button"></button>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        ';
    }

    //打赏模块
    public function post_ds(){

        global $post;
        $post_id = $post->ID;
        $post_type = get_post_type($post_id);
        if($post_type !== 'post') return;

        if(is_audit_mode()) return;

        $default_settings = b2_get_option('template_single','single_ds_group');
        $default_settings = isset($default_settings[0]) ? $default_settings[0] : array();

        if(isset($default_settings['single_post_ds_open']) && $default_settings['single_post_ds_open'] == '1') {

            $title = isset($default_settings['single_post_ds_title']) ? $default_settings['single_post_ds_title'] : __('打赏','b2');

            echo '<div id="content-ds" class="content-ds" v-show="data != \'\'" v-cloak>
            <p v-cloak v-show="data.single_post_ds_text"><span v-text="data.single_post_ds_text"></span></p>
            <div class="content-ds-button">
                <div id="con">
                    <div id="TA-con" @click="show()">
                        <div id="text-con">
                            '.b2_get_icon('b2-tang').'
                            <div id="TA">'.sprintf(__('给TA%s','b2'),$title).'</div>
                        </div>
                    </div> 
                    <div id="tube-con">
                        <svg viewBox="0 0 1028 385" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 77H234.226L307.006 24H790" stroke="#e5e9ef" stroke-width="20"></path> <path d="M0 140H233.035L329.72 71H1028" stroke="#e5e9ef" stroke-width="20"></path> <path d="M1 255H234.226L307.006 307H790" stroke="#e5e9ef" stroke-width="20"></path> <path d="M0 305H233.035L329.72 375H1028" stroke="#e5e9ef" stroke-width="20"></path> <rect y="186" width="236" height="24" fill="#e5e9ef"></rect> <ellipse cx="790" cy="25.5" rx="25" ry="25.5" fill="#e5e9ef"></ellipse> <circle r="14" transform="matrix(1 0 0 -1 790 25)" fill="white"></circle> <ellipse cx="790" cy="307.5" rx="25" ry="25.5" fill="#e5e9ef"></ellipse> <circle r="14" transform="matrix(1 0 0 -1 790 308)" fill="white"></circle></svg> 
                        <div id="mask">
                            <svg viewBox="0 0 1028 385" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 77H234.226L307.006 24H790" stroke="#f25d8e" stroke-width="20"></path> <path d="M0 140H233.035L329.72 71H1028" stroke="#f25d8e" stroke-width="20"></path> <path d="M1 255H234.226L307.006 307H790" stroke="#f25d8e" stroke-width="20"></path> <path d="M0 305H233.035L329.72 375H1028" stroke="#f25d8e" stroke-width="20"></path> <rect y="186" width="236" height="24" fill="#f25d8e"></rect> <ellipse cx="790" cy="25.5" rx="25" ry="25.5" fill="#f25d8e"></ellipse> <circle r="14" transform="matrix(1 0 0 -1 790 25)" fill="white"></circle> <ellipse cx="790" cy="307.5" rx="25" ry="25.5" fill="#f25d8e"></ellipse> <circle r="14" transform="matrix(1 0 0 -1 790 308)" fill="white"></circle></svg></div> <div id="orange-mask"><svg viewBox="0 0 1028 385" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 77H234.226L307.006 24H790" stroke="#ffd52b" stroke-width="20"></path> <path d="M0 140H233.035L329.72 71H1028" stroke="#ffd52b" stroke-width="20"></path> <path d="M1 255H234.226L307.006 307H790" stroke="#ffd52b" stroke-width="20"></path> <path d="M0 305H233.035L329.72 375H1028" stroke="#ffd52b" stroke-width="20"></path> <rect y="186" width="236" height="24" fill="#ffd52b"></rect> <ellipse cx="790" cy="25.5" rx="25" ry="25.5" fill="#ffd52b"></ellipse> <circle r="14" transform="matrix(1 0 0 -1 790 25)" fill="white"></circle> <ellipse cx="790" cy="307.5" rx="25" ry="25.5" fill="#ffd52b"></ellipse> <circle r="14" transform="matrix(1 0 0 -1 790 308)" fill="white"></circle></svg>
                        </div> 
                        <span id="people">'.sprintf(__('共%s人','b2'),'{{data.count}}').'</span>
                    </div>
                </div>
            </div>
            <div class="content-ds-count"><span v-if="data.count"><b v-text="data.count"></b>'.sprintf(__('人已%s','b2'),$title).'</span><span v-else v-text="data.single_post_ds_none_text"></span></div>
            <ul class="content-ds-users">
                <li v-for="(user,index) in data.users">
                    <a :href="user.link" class="b2tooltipbox" :data-title="user.name+\':\'+user.money">
                        <img :src="user.avatar" class="avatar b2-radius"/>
                    </a>
                </li>
            </ul>
        </div>';
        }
    }

    public function copyright_footer(){

        if(is_singular('post' )){
            $content = b2_get_option('template_single','single_copyright');
            if($content){
                echo '<div class="post-note">'.$content.'</div>';
            }
        }
        
        
    }

    public function content_footer(){
        global $post;
        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if($post_type == 'infomation') return;

        $vote_up = b2_get_option('newsflashes_main','newsflashes_vote_up_text');
        $vote_down = b2_get_option('newsflashes_main','newsflashes_vote_down_text');

        if($post_type == 'newsflashes'){
            echo '
            <div class="content-footer">
                <div class="content-footer-poster">
                    <button class="poster-span" @click="openPoster()">'.b2_get_icon('b2-share-forward-fill').'<b>'.__('海报分享','b2').'</b></button>
                    <button :class="[\'text favorite-button\',{\'sc\':postData.favorites_isset}]" @click="postFavoriteAc" v-cloak>'.b2_get_icon('b2-star-fill').'{{postData.favorites_isset ? \''.__('已收藏','b2').'\' : \''.__('收藏','b2').'\'}}</button>
                </div>
                <div class="content-footer-zan-cai" v-cloak>
                    <span @click="vote(\'up\')" :class="postData.up_isset ? \'picked up\' : \'\'">'.b2_get_icon('b2-heart-fill').$vote_up.'<b v-text="postData.up"></b></span>
                    <span @click="vote(\'down\')" :class="postData.down_isset ? \'picked down\' : \'\'">'.b2_get_icon('b2-dislike-fill').$vote_down.'<b v-text="postData.down"></b></span>
                </div>
            </div>
        ';
        }else{
            echo '
            <div class="content-footer post-content-footer" v-cloak>
                <div class="post-content-footer-in">
                    <div class="content-footer-poster">
                        <button class="poster-span b2tooltipbox" @click="openPoster()" data-title="'.__('海报分享','b2').'">'.b2_get_icon('b2-share-forward-fill').'<b class="mobile-show">'.__('海报分享','b2').'</b></button>
                        <button @click="goComment()" class="mobile-hidden b2tooltipbox comment-span" data-title="'.__('去评论','b2').'">'.b2_get_icon('b2-chat-2-fill').'</button>
                        <button :class="[\'text favorite-button\',{\'sc\':postData.favorites_isset},\'b2tooltipbox\']" @click="postFavoriteAc" data-title="'.__('收藏','b2').'">'.b2_get_icon('b2-star-fill').'<b class="mobile-show">{{postData.favorites_isset ? \''.__('已收藏','b2').'\' : \''.__('收藏','b2').'\'}}</b></button>
                    </div>
                    <div class="content-footer-zan-cai b2tooltipbox" data-title="'.__('喜欢','b2').'">
                        <span @click="vote(\'up\')" :class="postData.up_isset ? \'picked\' : \'\'">'.b2_get_icon('b2-heart-fill').'<b v-text="postData.up"></b></span>
                        <span @click="vote(\'down\')" :class="postData.down_isset ? \'picked mobile-show\' : \'mobile-show\'">'.b2_get_icon('b2-dislike-fill').'<b v-text="postData.down"></b></span>
                    </div>
                </div>
            </div>
        ';
        }
    }

    public static function get_share_links($have_pic = false,$post_id = 0){
        $metas = \B2\Modules\Common\Seo::single_meta($post_id);

        return array(
            'title'=>$metas['title'],
            'weibo'=>esc_url('http://service.weibo.com/share/share.php?url='.$metas['url'].'&sharesource=weibo&title='.wptexturize(urlencode($metas['title'])).($have_pic ? '&pic='.$metas['image'] : '')),
            'qq'=>esc_url('http://connect.qq.com/widget/shareqq/index.html?url='.$metas['url'].'&sharesource=qzone&title='.wptexturize(urlencode($metas['title'])).($have_pic ? '&pics='.$metas['image'] : '').($metas['description'] ? '&summary='.wptexturize(urlencode($metas['description'])) : '')),
            'qq-k'=>esc_url('https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='.$metas['url'].'&sharesource=qzone&title='.wptexturize(urlencode($metas['title'])).($have_pic ? '&pics='.$metas['image'] : '').($metas['description'] ? '&summary='.wptexturize(urlencode($metas['description'])) : '')),
            'weixin'=>''
        );
        
    }

    public function list_tags(){
        $post_id = get_the_id();

        if(get_post_type($post_id) !== 'post') return;

        $show = self::get_single_post_settings($post_id,'single_show_tags');
        if(!$show) return;
        echo  self::get_tag_list($post_id);
    }

    public function get_related_posts($post_id){

        if(!$post_id){
            global $post;
            $post_id = $post->ID;
        }

        $data = Post::get_related_posts($post_id);

        if(empty($data)) return;

        $_html = '<div class="related-posts mg-t mg-b box b2-radius"><div class="related-posts-title">'.__('猜你喜欢','b2').'</div><div class="hidden-line"><div class="related-posts-in">';
        foreach ($data as $k => $v) {
            $_html .= '
            <div class="related-posts-item">
                <div>
                    <div class="related-post-thumb" style="background-image:url('.$v['thumb'].')"><a href="'.$v['link'].'" class="link-block"></a></div>
                    <h2><a href="'.$v['link'].'">'.$v['title'].'</a></h2>
                    <div class="realte-post-meta">
                        <span>'.$v['date'].'</span><span>'.b2_get_icon('b2-chat-2-fill').$v['comment_count'].'</span><span>'.b2_get_icon('b2-eye-fill').$v['views'].'</span>
                    </div>
                </div>
            </div>
            ';
        }
        $_html .= '</div></div></div>';

        echo $_html;
    }

    public function shop_normal_action(){
        $post_id = get_the_id();

        $shop_type = get_post_meta($post_id,'zrz_shop_type',true);

        $shop_multi = get_post_meta($post_id,'zrz_shop_multi',true);

        $multi_html = '';

        if($shop_type == 'normal' && $shop_multi == 1){
            $multi_html = '<template v-if="data !== \'\' && data['.$post_id.'][\'multi\'][\'list\'].length > 0">
                <li class="multi-row" v-for="(item,index) in data['.$post_id.'][\'multi\'][\'list\']" :key="index">
                    <div class="shop-single-data-title" v-text="item.key">
                    </div>
                    <div class="shop-single-data-value-list">
                    <div :class="[\'shop-single-data-value\',{\'picked\':data['.$post_id.'][\'multi\'][\'picked\'][index] == i}]" v-for="(_item,i) in item.values" v-text="_item" @click="multiPicked(index,i,_item)"></div>
                    </div>
                </li>
            </template>';
        }
        ?>
        <div class="shop-single-data-list">
            <ul>
                <li class="shop-single-data-price b2-radius">
                    <div class="shop-single-data-value">
                        <div :class="[data !== '' && data[<?php echo $post_id; ?>].price.current_price === data[<?php echo $post_id; ?>].price.price ? 'shop-item-picked' : 'shop-item-delete',data !== '' && data[<?php echo $post_id; ?>].price.price === 0 ? 'shop-item-hidden' : '','shop-item-normal-price']">
                            <span class="shop-single-price-title"><?php echo __('价格：','b2'); ?></span>
                            <span class="shop-single-price">
                                <i><?php echo B2_MONEY_SYMBOL; ?></i>
                                <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.price"></b>
                                <b v-else>--</b>
                            </span>
                        </div>
                        <div :class="['shop-item-d-price',data !== '' && data[<?php echo $post_id; ?>].price.d_price === data[<?php echo $post_id; ?>].price.current_price ? 'shop-item-picked' : 'shop-item-delete',data !== '' && data[<?php echo $post_id; ?>].price.d_price === 0 ? 'shop-item-hidden' : '']" v-cloak>
                            <span class="shop-single-price-title"><?php echo __('折扣价：','b2'); ?></span>
                            <span class="shop-single-price">
                                <i><?php echo B2_MONEY_SYMBOL; ?></i>
                                <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.d_price"></b>
                                <b v-else>--</b>
                            </span>
                            <span v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.price_text" class="shop-zk"></span>
                        </div>
                        <div :class="['shop-item-u-price',data !== '' && data[<?php echo $post_id; ?>].price.u_price === data[<?php echo $post_id; ?>].price.current_price && data[<?php echo $post_id; ?>].is_vip ? 'shop-item-picked' : 'shop-item-delete',data !== '' && data[<?php echo $post_id; ?>].price.u_price === 0 ? 'shop-item-hidden' : '']" v-cloak>
                            <span class="shop-single-price-title"><?php echo __('会员价：','b2'); ?></span>
                            <span class="shop-single-price">
                                <i><?php echo B2_MONEY_SYMBOL; ?></i>
                                <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.u_price"></b>
                                <b v-else>--</b>
                            </span>
                        </div>
                        <span class="views"><?php echo b2_get_icon('b2-blaze-line'); ?><b v-text="data[<?php echo $post_id; ?>].views" v-if="data !== ''"></b></span>
                    </div>
                </li>
                <li class="shop-single-data-can-buy" v-if="data != '' && data[<?php echo $post_id; ?>].can_buy.roles.length > 0">
                    <div class="shop-single-data-title">
                        <?php echo __('允许购买的用户组','b2'); ?>
                    </div>
                    <div class="shop-single-data-value shop-single-data-roles">
                        <div v-for="item in data[<?php echo $post_id; ?>].can_buy.roles" v-html="item"></div>
                    </div>
                </li>
                <?php echo $multi_html; ?>
                <li class="shop-single-data-credit" v-if="data !== '' && data[<?php echo $post_id; ?>].price.credit" v-cloak>
                    <div class="shop-single-data-title">
                        <?php echo __('赠送积分','b2'); ?>
                    </div>
                    <div class="shop-single-data-value">
                        <?php echo b2_get_icon('b2-coin-line');?>
                        <span v-text="data[<?php echo $post_id; ?>].price.credit"></span>
                    </div>
                </li>
                <li class="shop-single-data-stock">
                    <div class="shop-single-data-title">
                        <?php echo __('库存数量','b2'); ?>
                    </div>
                    <div class="shop-single-data-value">
                        <span v-text="data[<?php echo $post_id; ?>].stock.total" v-if="data !== ''"></span>
                        <span v-else>--</span>
                    </div>
                </li>
                <li class="shop-single-data-sell" v-show="data !== '' && data[<?php echo $post_id; ?>].stock.sell !== ''" v-cloak>
                    <div class="shop-single-data-title">
                        <?php echo __('已售数量','b2'); ?>
                    </div>
                    <div class="shop-single-data-value">
                        <span v-text="data[<?php echo $post_id; ?>].stock.sell" v-if="data !== ''"></span>
                        <span v-else>--</span>
                    </div>
                </li>
                <li class="shop-single-data-count" v-if="data && data[<?php echo $post_id; ?>].commodity == 1">
                    <div class="shop-single-data-title">
                        <?php echo __('购买数量','b2'); ?>
                    </div>
                    <div class="shop-single-data-value">
                        <button @click="countSub">-</button>
                        <input type="number" v-model="count" onkeypress='validate(event)' v-if="data !== ''">
                        <button @click="countAdd">+</button>
                    </div>
                </li>
            </ul>
        </div>
        <?php do_action( 'shop_single_action_before' ); ?>
        <div class="mg-t shop-single-action">
            <div class="shop-single-action-left" v-if="data !== '' && !data[<?php echo $post_id; ?>]['out_link']" v-cloak>
                <a class="button" target="_blank" :href="'<?php echo b2_get_custom_page_url('carts').'?id='.$post_id.'&count='; ?>'+count+'&index='+pickedMultiId" v-if="data !== '' && data[<?php echo $post_id; ?>].can_buy.allow"><?php echo __('购买','b2'); ?></a>
                <button disabled="true" v-else-if="data !== ''" v-text="data[<?php echo $post_id; ?>].can_buy.text" style="margin-right:10px" v-cloak></button>
                <button class="empty" @click="addCart" v-if="!inCart()" :disabled="data !== '' && !data[<?php echo $post_id; ?>].can_buy.allow">
                    <?php echo __('加入购物车','b2'); ?>
                </button>
                <button class="empty" @click="addCart" v-else disabled v-cloak>
                    <?php echo __('已加入购物车','b2'); ?>
                </button>
                <?php do_action( 'shop_single_action_btn' );?>
            </div>
            <div class="shop-single-action-left" v-else-if="data !== ''">
                <a class="button" :href="data[<?php echo $post_id; ?>]['out_link']" target="_blank">
                    <?php echo __('立刻购买','b2'); ?>
                </a>
                <?php do_action( 'shop_single_action_btn' ); ?>
            </div>
            <div class="shop-single-action-right" v-if="postData !== ''">
                <button :class="['text favorite-button',{'sc':postData.favorites_isset}]" @click="postFavoriteAc" v-cloak><?php echo b2_get_icon('b2-star-fill'); ?><span v-text="postData.favorites_isset ? '<?php echo __('已收藏','b2'); ?>' : '<?php echo __('收藏','b2'); ?>'"></span></button>    
            </div>
        </div>
        <?php do_action( 'shop_single_action_after' ); ?>
    <?php
        
    }

    public function shop_exchange_action(){
        $post_id = get_the_id();
    ?>
    <div class="shop-single-data-list">
        <ul>
            <li class="shop-single-data-price">
                <div class="shop-single-data-value">
                    <div class="shop-item-normal-price shop-item-delete" v-if="data != '' && data[<?php echo $post_id; ?>].price.price" v-cloak>
                        <span class="shop-single-price-title"><?php echo __('价格：','b2'); ?></span>
                        <span class="shop-single-price">
                            <i><?php echo B2_MONEY_SYMBOL; ?></i>    
                            <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.price"></b>
                            <b v-else>--</b>
                        </span>
                    </div>
                    <div class="shop-item-u-price">
                        <span class="shop-single-price-title"><?php echo __('积分：','b2'); ?></span>
                        <span class="shop-single-price">
                            <i><?php echo b2_get_icon('b2-coin-line'); ?></i>
                            <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.credit"></b>
                            <b v-else>--</b>
                        </span>
                    </div>
                    <span class="views"><?php echo b2_get_icon('b2-blaze-line'); ?><b v-text="data[<?php echo $post_id; ?>].views" v-if="data !== ''"></b></span>
                </div>
            </li>
            <li v-if="data != '' && data[<?php echo $post_id; ?>].can_buy.roles.length > 0">
                <div class="shop-single-data-title">
                    <?php echo __('允许兑换的用户组','b2'); ?>
                </div>
                <div class="shop-single-data-value shop-single-data-roles">
                    <div v-for="item in data[<?php echo $post_id; ?>].can_buy.roles" v-html="item"></div>
                </div>
            </li>
            <li>
                <div class="shop-single-data-title">
                    <?php echo __('商品类型','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <span v-text="data[<?php echo $post_id; ?>].commodity == 1 ? '<?php echo __('实物','b2'); ?>' : '<?php echo __('虚拟物品','b2'); ?>'" v-if="data !== ''"></span>
                    <span v-else>--</span>
                </div>
            </li>
            <li>
                <div class="shop-single-data-title">
                    <?php echo __('库存','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <span v-text="data[<?php echo $post_id; ?>].stock.total" v-if="data !== ''"></span>
                    <span v-else>--</span>
                </div>
            </li>
            <li v-show="data !== '' && data[<?php echo $post_id; ?>].stock.sell !== ''" v-cloak>
                <div class="shop-single-data-title">
                    <?php echo __('已兑','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <span v-text="data[<?php echo $post_id; ?>].stock.sell" v-if="data !== ''"></span>
                    <span v-else>--</span>
                </div>
            </li>
            <li class="shop-single-data-count">
                <div class="shop-single-data-title">
                    <?php echo __('购买数量','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <button @click="countSub">-</button>
                    <input type="text" v-model="count" onkeypress='validate(event)'>
                    <button @click="countAdd">+</button>
                </div>
            </li>
            <li class="shop-single-data-address-picked" v-if="showAddress('<?php echo $post_id; ?>')" v-cloak>
                <p class="shop-single-data-address-desc"><?php echo __('实物收货地址（必选）：'); ?><span @click="showAddressBox = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑地址','b2'); ?></span></p>
                <p v-if="pickedAddress !== ''" class="shop-single-data-address-lisst"><span v-text="address.addresses[pickedAddress].province ? (address.addresses[pickedAddress].province+' '+address.addresses[pickedAddress].city+' '+address.addresses[pickedAddress].county+' '+address.addresses[pickedAddress].address) : address.addresses[pickedAddress].address"></span><span v-text="address.addresses[pickedAddress].name"></span><span v-text="address.addresses[pickedAddress].phone"></span></p>
                <p v-else><?php echo __('收货地址为空，请添加一个收货地址！','b2'); ?></p>
            </li>
            <li class="shop-single-data-address-picked" v-if="showEmail('<?php echo $post_id; ?>')" v-cloak>
                <p class="shop-single-data-address-desc"><?php echo __('虚拟物品接收邮箱（必填）：','b2'); ?><span @click="showEmailBox = true"><?php echo b2_get_icon('b2-edit-2-line').__('更换邮箱','b2'); ?></span></p>
                <p v-if="pickedEmail !== ''" class="shop-single-data-address-lisst" v-text="pickedEmail"></p>
                <p v-else><?php echo __('请设置一个邮箱，用以接收购买信息！','b2'); ?></p>
            </li>
        </ul>
    </div>
    <div class="mg-t shop-single-action">
        <div class="shop-single-action-left">
            <button :disabled="disabled(<?php echo $post_id; ?>)" v-text="data !== '' ? data[<?php echo $post_id; ?>].can_buy.text : '<?php echo __('兑换','b2'); ?>'" @click="exchange('<?php echo $post_id; ?>')"></button>
        </div>
        <div class="shop-single-action-right" v-if="postData !== ''">
            <button :class="['text favorite-button',{'sc':postData.favorites_isset}]" @click="postFavoriteAc" v-cloak><?php echo b2_get_icon('b2-star-fill'); ?><span v-text="postData.favorites_isset ? '<?php echo __('已收藏','b2'); ?>' : '<?php echo __('收藏','b2'); ?>'"></span></button>    
        </div>
    </div>
    <?php echo \B2\Modules\Templates\VueTemplates::address_box(); ?>
    <?php echo \B2\Modules\Templates\VueTemplates::email_box(); ?>
    <?php
    }

    public function shop_lottery_action(){
        $post_id = get_the_id();
    ?>
    <div class="shop-single-data-list">
        <ul>
            <li class="shop-single-data-price">
                <div class="shop-single-data-value">
                    <div class="shop-item-normal-price shop-item-delete" v-if="data != '' && data[<?php echo $post_id; ?>].price.price" v-cloak>
                        <span class="shop-single-price-title"><?php echo __('价格：','b2'); ?></span>
                        <span class="shop-single-price">
                            <i><?php echo B2_MONEY_SYMBOL; ?></i>    
                            <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.price"></b>
                            <b v-else>--</b>
                        </span>
                    </div>
                    <div class="shop-item-u-price">
                        <span class="shop-single-price-title"><?php echo __('积分：','b2'); ?></span>
                        <span class="shop-single-price">
                            <i><?php echo b2_get_icon('b2-coin-line'); ?></i>
                            <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.credit"></b>
                            <b v-else>--</b>
                        </span>
                    </div>
                    <span class="views"><?php echo b2_get_icon('b2-blaze-line'); ?><b v-text="data[<?php echo $post_id; ?>].views" v-if="data !== ''"></b></span>
                </div>
            </li>
            <li v-if="data != '' && data[<?php echo $post_id; ?>].can_buy.roles.length > 0">
                <div class="shop-single-data-title">
                    <?php echo __('允许参与','b2'); ?>
                </div>
                <div class="shop-single-data-value shop-single-data-roles">
                    <div v-for="item in data[<?php echo $post_id; ?>].can_buy.roles" v-html="item"></div>
                </div>
            </li>
            <li>
                <div class="shop-single-data-title">
                    <?php echo __('商品类型','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <span v-text="data[<?php echo $post_id; ?>].commodity == 1 ? '<?php echo __('实物','b2'); ?>' : '<?php echo __('虚拟物品','b2'); ?>'" v-if="data !== ''"></span>
                    <span v-else>--</span>
                </div>
            </li>
            <li>
                <div class="shop-single-data-title">
                    <?php echo __('库存','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <span v-text="data[<?php echo $post_id; ?>].stock.total" v-if="data !== ''"></span>
                    <span v-else>--</span>
                </div>
            </li>
            <li v-show="data !== '' && data[<?php echo $post_id; ?>].stock.sell !== ''" v-cloak>
                <div class="shop-single-data-title">
                    <?php echo __('抽中','b2'); ?>
                </div>
                <div class="shop-single-data-value">
                    <span v-text="data[<?php echo $post_id; ?>].stock.sell" v-if="data !== ''"></span>
                    <span v-else>--</span>
                </div>
            </li>
            <li class="shop-single-data-address-picked" v-if="showAddress('<?php echo $post_id; ?>')" v-cloak>
                <p class="shop-single-data-address-desc"><?php echo __('实物收货地址（必选）：','b2'); ?><span @click="showAddressBox = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑地址','b2'); ?></span></p>
                <p v-if="pickedAddress !== ''" class="shop-single-data-address-lisst"><span v-text="address.addresses[pickedAddress].address"></span><span v-text="address.addresses[pickedAddress].name"></span><span v-text="address.addresses[pickedAddress].phone"></span></p>
                <p v-else><?php echo __('收货地址为空，请添加一个收货地址！','b2'); ?></p>
            </li>
            <li class="shop-single-data-address-picked" v-if="showEmail('<?php echo $post_id; ?>')" v-cloak>
                <p class="shop-single-data-address-desc"><?php echo __('虚拟物品接收邮箱（必填）：'); ?><span @click="showEmailBox = true"><?php echo b2_get_icon('b2-edit-2-line').__('更换邮箱','b2'); ?></span></p>
                <p v-if="pickedEmail !== ''" class="shop-single-data-address-lisst" v-text="pickedEmail"></p>
                <p v-else><?php echo __('请设置一个邮箱，用以接收购买信息！','b2'); ?></p>
            </li>
        </ul>
        <div class="lottery-box">
            <div class="lottery-box-one">
                <span><b v-text="fir[0]"></b></span>
                <span><b v-text="fir[1]"></b></span>
                <span><b v-text="fir[2]"></b></span>
                <span><b v-text="fir[3]"></b></span>
            </div>
            <div :class="'lottery-box-two ' + (m <= 0 ? 'picked ' : '') + (this.resData.fir === this.resData.sec && this.resData.fir !== 0 && m <= 0 ? 'success' : '')"><b v-text="f"></b></div>
            <div class="lottery-box-three">
                <span><b v-text="sec[0]"></b></span>
                <span><b v-text="sec[1]"></b></span>
                <span><b v-text="sec[2]"></b></span>
                <span><b v-text="sec[3]"></b></span>
            </div>
        </div>
    </div>
    <div class="lottery-desc">
        <p v-if="m <= 0" v-cloak>
            <span v-if="this.resData.fir === this.resData.sec && this.resData.fir !== 0" class="green">
               <b v-if="data[<?php echo $post_id; ?>].commodity == 1"><?php echo sprintf(__('恭喜，您中奖啦！请前往 %s订单中心%s 查看中奖结果','b2'),'<a :href="userData.link+\'/orders\'" target="_blank">','</a>'); ?></b>
               <b v-else><?php echo sprintf(__('恭喜，您中奖啦！请刷新本页：%s刷新%s','b2'),'<a href="javascript:void(0)" @click="reflush()">','</a>'); ?></b>
            </span>
            <span v-else class="red"><?php echo __('运气稍微差了一点，再接再厉！','b2'); ?></span>
        </p>
        <p v-else><?php echo __('两边数字相等，代表中奖！','b2'); ?></p>
    </div>
    <div class="mg-t shop-single-action">
        <div class="shop-single-action-left">
            <button :disabled="disabled(<?php echo $post_id; ?>)" v-text="data !== '' ? data[<?php echo $post_id; ?>].can_buy.text : '<?php echo __('抽奖','b2'); ?>'" @click="lottery('<?php echo $post_id; ?>')"></button>
        </div>
        <div class="shop-single-action-right" v-if="postData !== ''">
            <button :class="['text favorite-button',{'sc':postData.favorites_isset}]" @click="postFavoriteAc" v-cloak><?php echo b2_get_icon('b2-star-fill'); ?><span v-text="postData.favorites_isset ? '<?php echo __('已收藏','b2'); ?>' : '<?php echo __('收藏','b2'); ?>'"></span></button>    
        </div>
    </div>
    <?php echo \B2\Modules\Templates\VueTemplates::address_box(); ?>
    <?php echo \B2\Modules\Templates\VueTemplates::email_box(); ?>
    <?php
    }

    public function shop_single_top(){
        $post_id = get_the_id();
        if(get_post_type($post_id) !== 'shop') return;

        $type = get_post_meta($post_id,'zrz_shop_type',true);
        ?>
        <div class="box mg-b b2-radius wrapper shop-top-box">
        <div class="shop-single-top" ref="shopSingle" data-id="<?php echo $post_id; ?>">
            <div class="shop-single-imgs">
                <div class="shop-single-img-box">
                    <div class="img-box-current">
                        <?php echo b2_get_img(array(
                            'src_data'=>':src="currentThumb[\'thumb\']"',
                            'class'=>array('shop-box-img','b2-radius'),
                            'pic_data'=>' v-if="currentThumb[\'thumb\']" v-cloak',
                            'source_data'=>':srcset="currentThumb[\'thumb_webp\']"'
                        ));?>
                    </div>                   
                </div>
                <div class="shop-box-img-list-box" v-if="thumbs.length > 0">
                    <div v-for="(img,i) in thumbs"  @click="pickedThumb(i)" :class="thumbIndex == i ? 'picked' : ''">
                        <?php echo b2_get_img(array(
                            'src_data'=>':src="img[\'thumb\']"',
                            'class'=>array('shop-box-img-list','b2-radius'),
                            'pic_data'=>' v-if="img[\'thumb\']" v-cloak',
                            'source_data'=>':srcset="img[\'thumb_webp\']"'
                        ));?>
                    </div>
                </div>
                <?php do_action( 'shop_single_imgs_after' ); ?>
            </div>
            <div class="shop-single-data">
                <div class="shop-breadcrumb b2-hover">
                    <?php echo Shop::shop_single_breadcrumb($post_id); ?>
                </div>
                <div class="">
                    <h1><?php echo get_the_title($post_id); ?></h1>
                </div>
                <?php 
                if($type){
                    $fn = 'shop_'.$type.'_action';

                    echo $this->$fn(); 
                }
                ?>
                <?php do_action( 'shop_single_data_after' );  ?>
            </div>
        </div>
    </div>
    <?php
    }
}
