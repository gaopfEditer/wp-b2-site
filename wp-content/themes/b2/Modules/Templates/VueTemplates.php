<?php namespace B2\Modules\Templates;

use B2\Modules\Templates\Header;
use B2\Modules\Common\OAuth;
use B2\Modules\Common\FileUpload;
use B2\Modules\Common\Shop;

class VueTemplates{
    public static function init(){
        add_action( 'wp_enqueue_scripts', array(__CLASS__,'scripts_init'));
    }

    public static function scripts_init(){
        // $mark = preg_replace( '/^https?:\/\//', '', $_SERVER['SERVER_NAME'] );
        // $mark = str_replace('.','_',$mark);
        $single = is_singular();

        $post_id = 0;

        $show_slider = true;

        $shop_images = [];

        if($single){
            $post_id = get_the_id();

            $show_slider = Single::get_single_post_settings($post_id,'single_post_slider');
            $show_slider = $show_slider == '' ? true : $show_slider;

            if(get_post_type($post_id) == 'shop'){
                $shop_images = Shop::get_product_images($post_id);
            }

        }

        $text = b2_text();
        
        wp_localize_script( 'vue', 'b2_global',apply_filters('b2_global_settings',array(
            'is_home'=>is_home(),
            'is_weixin'=>b2_is_weixin(),
            'site_name'=>get_bloginfo('name'),
            'site_separator'=>b2_get_option('normal_main','separator'),
            'page_title'=>__('第{#}页','b2'),
            'login'=>self::login(),
            'search_box'=>self::search_box(),
            //'pay_box'=>self::pay_box(),
            'gg_box'=>self::gg_box(),
            'dmsg_box'=>self::directmessage_box(),
            'ds_box'=>self::ds_box(),
            'scan_box'=>self::Scan_box(),
            'pay_check'=>self::pay_check(),//支付检查
            'credit_box'=>self::credit_box(),//积分支付
            'mp_box'=>self::mp_box(),
            'weixin_bind'=>self::bind_weixin(),
            'bind_login'=>self::bind_phone_email(),
            //是否使用了固定链接
            'structure'=>get_option( 'permalink_structure' ),
            'check_code'=>self::check_code(),
            'site_info'=>array(
                'site_uri'=>B2_THEME_URI,
                'admin_ajax'=>admin_url('admin-ajax.php').'?action='
            ),
            'cookie_allow'=>b2_get_option('normal_login','allow_cookie'),
            'b2_gap'=>B2_GAP,
            'rest_url'=>get_rest_url(),
            'pay_url'=>b2_get_custom_page_url('pay'),
            'localStorage_msg'=>__('您的浏览器不支持 localStorage ，请更换浏览器以后再进行登录和注册的操作','b2'),
            //'mark'=>$mark,
            'empty_page'=>B2_EMPTY,
            'default_video_poster'=>b2_get_option('normal_main','default_video_poster'),
            'default_video_logo'=>b2_get_option('normal_main','img_logo_white'),
            'page_nav'=>self::page_nav(),
            'copy'=>array(
                'text'=>__('复制','b2'),
                'success'=>__('成功复制','b2'),
                'error'=>__('失败，请选中复制','b2')
            ),
            'search_type'=>b2_get_search_type(),
            'login_keep_days'=>(int)b2_get_option('normal_login','login_keep'),
            'language'=>get_locale(),
            'post_id'=>$post_id,
            'author_id'=>$single ? get_post_field('post_author') : 0,
            'poster_box'=>$single ? self::poster_box() : '',
            'show_slider'=>$show_slider,
            'poster_error'=>__('获取海报失败','b2'),
            'check_type'=>B2_VERIFY_CODE,
            'home_url'=>B2_HOME_URI,
            'alert_following'=>__('确认要取消关注吗？','b2'),
            'followed'=>__('已关注','b2'),
            'nofollowed'=>b2_get_icon('b2-add-line').__('关注','b2'),
            'alert_favorites'=>__('确定要取消收藏吗？','b2'),
            'top_scroll_pc'=>b2_get_option('template_top','top_scroll_pc') ? : 1,
            'top_scroll_mobile'=>b2_get_option('template_top','top_scroll_mobile') ? : 1,
            'footer_scroll'=>b2_get_option('template_footer','footer_menu_open'),
            'open_oauth'=>b2_oauth_types(true),
            'js_text'=>$text,
            'wx_mp_in_login'=>b2_get_option('normal_login','wx_mp_in_login'),
            'wx_appid'=>b2_get_option('normal_login','wx_pc_key'),
            'post_gg'=>$single ? self::post_gg() : '',
            'prettify_load'=>(int)b2_get_option('template_main','prettify_load'),
            'shop_images'=>$shop_images,
            'shop_after_sale'=>(int)b2_get_option('shop_main','after_sale'),
            'chat'=>array(
                'type'=>b2_get_option('template_aside','aside_chat_type'),
                'qq'=>b2_get_option('template_aside','aside_chat_qq'),
                'crisp'=>b2_get_option('template_aside','aside_chat_crisp_id'),
                'dmsg'=>b2_get_option('template_aside','aside_chat_dmsg_id')
            )
        )));
    }

    public static function get_logo(){
        $text_logo = b2_get_option('normal_main','text_logo');
        $img_logo = b2_get_option('normal_main','img_logo');
        return '<div class="login-logo">'.($img_logo ? '<img src="'.$img_logo.'" />' : $text_logo ).'</div>';
    }

    public static function Login(){
        /**
         * loginType 为 1 是登录，2是注册，3是找回密码，4是重设密码
         */

        //是否允许注册
        $allow_regeister = b2_get_option('normal_login','allow_register');
        $site_privacy = b2_get_option('normal_login','site_privacy');
        $site_terms = b2_get_option('normal_login','site_terms');
        
        $login_html = '<div :class="[\'modal\',{\'show-modal\':show}]" v-cloak>
            <div class="modal-content login-box-content b2-radius">
                <div class="box login-box-top">
                    <span class="close-button" @click="close(0)">×</span>
                    '.self::get_logo().'
                    <form @submit.stop.prevent="loginSubmit">

                        <div class="invitation-box" v-show="invitation != 0 && (loginType == 2 && !invitationPass)">
                            '.b2_get_icon('b2-gift-2-line').'
                            <p class="invitation-des">'.__('使用邀请码，您将获得一份特殊的礼物！', 'b2').'</p>
                            <p class="invitation-tips">'.__('请输入邀请码', 'b2').'</p>
                            <div class="invitation-input"><input type="text" id="invitation-code2" name="invitation_code" v-model="data.invitation_code" autocomplete="off"></div>
                            <div class="invitation-button">
                                <div><a :href="invitationLink" target="__blank">{{invitationText}}</a></div>
                                <div>
                                    <b class="empty text button" v-show="invitation == 1" @click.stop.prevent="invitationPass = true;showLuo = true">'.__('跳过','b2').'</b>
                                    <button :class="[\'button\',{\'b2-loading\':locked}]" :disabled="locked" >'.__('提交','b2').'</button>
                                </div>
                            </div>
                        </div>
                        <div class="login-box-in" v-show="!(invitation != 0 && (loginType == 2 && !invitationPass))">
                            <div class="login-title">
                                <span v-if="loginType == 1"><b class="repass-tips" v-if="repass">'.__('密码修改成功，请登录', 'b2').'</b><b v-else>'.__('登录', 'b2').'</b></span>
                                <template v-else-if="allowRegister == 1 && loginType == 2">
                                    <span>'.__('注册', 'b2').'</span>
                                </template>
                                <span v-else-if="loginType == 3">'.__('找回密码', 'b2').'</span>
                                <span v-else-if="loginType == 4">'.__('请输入您的新密码', 'b2').'</span>
                            </div>

                            <label class="login-form-item" v-show="loginType == 2">
                                <input type="text" name="nickname" tabindex="1" v-model="data.nickname" :class="data.nickname ? \'active\' : \'\'" spellcheck="false" autocomplete="off">
                                <span><b>'.__('可爱的昵称', 'b2').'</b></span>
                            </label>

                            <label class="login-form-item" v-show="loginType != 4">
                                <input type="text" name="username" tabindex="2" v-model="data.username" :class="data.username ? \'active\' : \'\'" spellcheck="false" autocomplete="off">
                                <span v-if="loginType == 3"><b>'.__('登录手机号或邮箱', 'b2').'</b></span>
                                <span v-else><b>'.__('登录','b2').'{{loginText}}</b></span>
                                <p class="login-box-des" v-show="loginType == 2 && (checkType == \'luo\' || checkType == \'text\')">'.__('用作登录：字母或数字的组合，最少6位字符','b2').'</p>
                                <p class="login-box-des" v-show="loginType == 2 && (checkType != \'luo\' && checkType != \'text\')">'.__('用作登录','b2').'</p>
                            </label>

                            <label class="login-form-item" v-show="checkType == \'luo\' && showLuo && loginType != 3 && loginType != 4">
                                <div class="check-code-luo"><div class="l-captcha" data-site-key="'.b2_get_option('normal_login','site_key').'" data-width="100%" data-callback="getResponse"></div></div>
                            </label>

                            <label :class="[\'login-form-item login-check-input\',{\'show\':(((loginType == 2 || loginType == 3) && data.username && checkType != \'luo\') || (checkType == \'luo\' && loginType == 3))}]">
                                <input type="text" name="checkCode" tabindex="3" v-model="data.img_code" :class="data.img_code ? \'active\' : \'\'" spellcheck="false" v-if="checkType == \'text\' && loginType != 3" autocomplete="off">
                                <input type="text" name="checkCode" tabindex="3" v-model="data.code" :class="data.code ? \'active\' : \'\'" spellcheck="false" autocomplete="off" v-else>
                                <span><b>'.__('验证码', 'b2').'</b></span>
                                <div class="check-code-img" v-if="checkType == \'text\' && loginType != 3" @click="changeCode">
                                    <img :src="codeImg" v-if="codeImg"/>
                                    <i class="recaptcha-load" v-else></i>
                                </div>
                                <b class="login-eye button text" @click.stop.prevent="!SMSLocked && count == 60 ? sendCode() : \'\'" v-else>{{count < 60 ? count+\''.__('秒后可重发', 'b2').'\' : \''.__('发送验证码', 'b2').'\'}}</b>
                            </label>

                            <label class="login-form-item" v-show="loginType != 3">
                                <input name="password" :type="eye ? \'text\' : \'password\'" tabindex="4" v-model="data.password" :class="data.password ? \'active\' : \'\'" autocomplete="off" spellcheck="false">
                                <span><b v-if="loginType == 4">'.__('新密码', 'b2').'</b><b v-else>'.__('密码', 'b2').'</b></span>
                                <b class="login-eye button text" @click.stop.prevent="eye = !eye"><i :class="[\'b2font\',eye ? \'b2-eye-fill\' : \'b2-eye-off-fill\']"></i></b>
                            </label>

                            <label class="login-form-item" v-show="loginType == 4">
                                <input name="repassword" :type="eye ? \'text\' : \'password\'" tabindex="5" v-model="data.confirmPassword" :class="data.confirmPassword ? \'active\' : \'\'" autocomplete="off" spellcheck="false">
                                <span><b>'.__('重复新密码', 'b2').'</b></span>
                                <p class="login-box-des" v-show="loginType == 4">'.__('最少6位字符').'</p>
                            </label>

                            <div class="forget-pass-info" v-if="loginType == 3">'.__('请填写您注册时使用的手机号码或邮箱，发送验证码以后，请在手机号码或邮箱中查看，并填写到此处！（验证码3分钟有效期）', 'b2').'</div>
                            <div class="site-terms" v-if="loginType == 2 && allowRegister == 1">
                                <label><input type="checkbox" name="xieyi" v-model="$store.state.xieyi"/>'.sprintf(__('我已同意 %s用户协议%s 和 %s隐私政策%s','b2'),'<a href="'.$site_terms.'" target="_blank">','</a>','<a href="'.$site_privacy.'" target="_blank">','</a>').'</label>
                            </div>
                            <div class="login-bottom">
                                <button v-if="loginType == 1" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('快速登录', 'b2').'</button>
                                <button :class="locked ? \'b2-loading\' : \'\'" v-if="loginType == 2 && allowRegister == 1" :disabled="locked || !$store.state.xieyi">'.__('快速注册', 'b2').'</button>
                                <button v-if="loginType == 3" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('下一步', 'b2').'</button>
                                <button v-if="loginType == 4" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('提交', 'b2').'</button>
                            </div>
                            <div :class="loginType == 3 || loginType == 4 || (invitationPass && loginType == 2) ? \'login-tk-forget login-tk\' : \'login-tk\'">
                                <p v-if="loginType == 4"><a href="javascript:void(0)" @click="loginAc(3)">'.__('返回修改','b2').'</a></p>
                                <p v-if="loginType == 2 && invitationPass">'.__('邀请码错了？', 'b2').'<a href="javascript:void(0)" @click="invitationPass = false;showLuo = false">'.__('修改','b2').'</a></p>
                                <p class="login-p" v-if="(loginType == 1 || loginType == 3) && allowRegister == 1"><a v-if="loginType == 1" href="javascript:void(0)" @click="loginAc(3)">'.__('忘记密码？', 'b2').'</a><span>'.__('新用户？', 'b2').'<a href="javascript:void(0)" @click="loginAc(2)">'.__('注册', 'b2').'</a></span></p>
                                <p v-if="loginType == 2 || loginType == 3 || loginType == 4"><a v-if="loginType == 2" href="javascript:void(0)" @click="loginAc(3)">'.__('忘记密码？', 'b2').'</a><span>'.__('已有帐号？', 'b2').'<a href="javascript:void(0)" @click="loginAc(1)">'.__('登录', 'b2').'</a></span></p>
                            </div>
                        </div>
                        <div :class="\'login-social-button \'+(openOauth ? \'show\' : \'\')" v-if="!(invitation != 0 && (loginType == 2 && !invitationPass))">
                            <div :class="[\'login-social-button-bottom\',{\'is-weixin\':isWeixin}]">
                                <div>'.__('社交登录:','b2').'</div>
                                <div>
                                    <template v-for="(open,key,index) in oauth">
                                        <a :href="open.url" :class="\'button login-\'+key" @click="markHistory(open.mp,$event)" v-if="open.open" :style="\'color:\'+open.color"><i :class="\'b2font b2-\'+key+\' \'+open.icon"></i><span>{{open.name}}</span></a>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>';

        return apply_filters( 'b2_vue_html_login',$login_html,$site_terms,$site_privacy,$allow_regeister );
    }

    public static function check_code(){
        return '<div :class="[\'modal\',\'recaptcha-form\',{\'show-modal\':show}]">
            <div class="modal-content b2-radius">
                <div class="check-code-luo modal-content-innter" v-if="loginType != 3 && checkType != \'normal\'">
                    <a href="javascript:void(0)" class="close-icon" @click="close">'.b2_get_icon('b2-close-line').'</a>
                    <h2>'.__('请进行人机验证','b2').'</h2>
                    <div class="l-captcha" data-site-key="'.b2_get_option('normal_login','site_key').'" data-width="100%" data-callback="getResponse"></div>
                </div>
                <form @submit.stop.prevent="checkCode" v-else>
                    <div class="modal-content-innter">
                        <div class="recaptcha-img">
                            <img :src="recaptchaUrl" @click="change" v-if="recaptchaUrl">
                            <span class="recaptcha-load" v-else></span>
                            <h2>'.__('请输入验证码', 'b2').'</h2>
                            <p>'.sprintf(__('请输入图片中的验证码%s点击发送按钮获取验证码', 'b2'),'<br>').'</p>
                        </div>
                        <input type="text" spellcheck="false" :class="recaptcha ? \'input-align-center\' : \'\'" autocomplete="off" v-model="recaptcha">
                    </div> 
                    <div class="recaptcha-button">
                        <a href="javascript:void(0)" class="button" @click="close">'.__('取消', 'b2').'</a><button type="submit" :disabled="disabled" class="recaptcha-send button">'.__('发送', 'b2').'</button>
                    </div>
                </form>
            </div>
        </div>';
    }

    public static function page_nav(){
        return '<div v-show="pages > 1" class="ajax-pager">
        <div class="ajax-pagenav" v-if="type === \'p\'">
            <div class="btn-group">
                <button v-for="page in cpages" :class="[\'empty button\',cpaged == page ? \'selected\' : \'\',page == 0 ? \'bordernone\' : \'\',disabled(page) ? \'b2-loading\' : \'\']" @click.stop.self="go(page)" :disabled="disabled(page) || page == 0 || cpaged == page">{{page != 0 ? page : \'...\'}}</button>
                <label class="pager-center" v-show="pages >= 7 || mobile"><input type="text" ref="pagenavnumber" :value="cpaged" @keyup.enter="jump($event)" @focus="focus" @blur="blur" autocomplete="off"/>/<span v-show="!showGo">{{pages}}'.__(' 页','b2').'</span><span v-show="showGo" class="b2-color" @click.prevent.stop="jump($event)"> '.__('前往','b2').'</span></label>
            </div>
            <div class="btn-pager">
                <button :class="[\'empty button\',{\'b2-loading\' : locked && next}]" @click.stop.self="go(cpaged-1,\'next\')" :disabled="cpaged <= 1 ? true : false">❮</button>
                <button :class="[\'empty button\',{\'b2-loading\' : locked && per}]" @click.stop.self="go(cpaged+1,\'per\')" :disabled="cpaged >= pages ? true : false">❯</button>
            </div>
        </div>
        <div class="ajax-more" v-else>
            <div class="pager-center" for="page-input">
                <input id="page-input" type="text" :value="cpaged" @keyup.enter="jump($event)" autocomplete="off"/>
                <button class="text button mar10-l" @click.stop="jump($event)">'.__('前往','b2').'</button>
            </div>
            <button :class="[\'button empty\',{\'b2-loading\' : locked}]" :disabled="locked || cpaged >= pages ? true : false" @click="go(cpaged+1)">
                <span v-if="cpaged >= pages">'.__('没有更多','b2').'</span>
                <span v-else>'.__('加载更多','b2').'</span>
            </button>
        </div>
    </div>';
    }

    public static function poster_box(){

        $arg = Single::get_share_links(false);
       
        return '<div :class="[\'modal\',\'poster-box\',{\'show-modal\':show}]">
                    <div class="modal-content b2-radius" ref="poster">
                        <span class="close-button" @click="close(0)">×</span>
                        <div class="poster-content">
                            <div id="poster-box-left" class="poster-box-left" ref="posterContent">
                                <template v-if="!isWeixin || (isWeixin && !poster)">
                                    <div>
                                        <div class="poster-image">
                                        <img :src="(thumb ? thumb : data.thumb)" class="poster-img" ref="posterImg" v-if="data != \'\'"/>
                                            <div class="poster-date b2-radius">
                                                <div class="poster-date-day">
                                                    <span v-text="data.date.day" v-if="data != \'\'"></span>
                                                </div>
                                                <div class="poster-date-year">
                                                    <span v-text="data.date.year" v-if="data != \'\'"></span>
                                                    <span v-text="data.date.month" v-if="data != \'\'"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="poster-info">
                                            <h2 v-html="data.title" v-if="data != \'\'"></h2>
                                            <p v-html="data.content" v-if="data != \'\'"></p>
                                        </div>
                                        <div class="poster-footer">
                                            <div class="poster-footer-left">
                                                <img :src="data.logo" class="poster-img-logo" ref="posterLogo" v-if="data.logo"/>
                                                <p v-text="data.desc"></p>
                                            </div>
                                            <div class="poster-footer-right">
                                                <img :src="data.qrcode" class="poster-img-qrcode" ref="posterQrcode" v-if="data != \'\'">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="poster && isWeixin">
                                    <img :src="poster" />
                                </template>
                            </div>
                            <div class="poster-share">
                                <div class="fuzhi" :data-clipboard-text="data.link" v-if="data != \'\'">
                                    <p>'.__('点击复制推广网址：','b2').'</p>
                                    <input disabled="true" :value="data.link" />
                                </div>
                                <div class="share-text" v-if="isWeixin" v-cloak>{{poster ? \''.__('长按图片分享给朋友，或者保存到手机','b2').'\' : \''.__('海报创建中','b2').'\'}}</div>
                                <template v-else v-cloak>
                                    <div class="social-share">
                                        <p>'.__('分享到：','b2').'</p>
                                        <button class="poster-share-weibo" @click="openWin(\''.$arg['weibo'].'\',\'weibo\')">'.__('微博','b2').'</button>
                                        <button class="poster-share-qq" @click="openWin(\''.$arg['qq'].'\',\'qq\')">'.__('QQ好友','b2').'</button>
                                        <button class="poster-share-qq-k" @click="openWin(\''.$arg['qq-k'].'\',\'qq-k\')">'.__('QQ空间','b2').'</button>
                                    </div>
                                    <div class="poster-down-load">
                                        <p>'.__('下载海报：','b2').'</p>
                                        <button  class="poster-share-download button" :disabled="poster ? false : true" @click="download">{{poster ? \''.__('点击下载','b2').'\' : \''.__('海报创建中','b2').'\'}}</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>';
    }

    public static function search_box(){

        return '<div :class="[\'modal\',{\'show-modal\':show}]" v-cloak @click.stop="">
                <div class="modal-content search-box-content b2-radius" >
                    '.self::get_logo().'
                    <span class="close-button" @click="close()">×</span>
                    <div class="search-title">'.__('搜索一下可能来得更快','b2').'</div>
                    <form @click.stop="" method="get" action="'.B2_HOME_URI.'" :class="showSearch ? \'search-form-normal b2-show b2-radius\' : \'search-form-normal b2-hidden b2-radius\'" v-cloak>
                        <input class="search-input b2-radius" type="text" name="s" autocomplete="off" placeholder="'.__('请输入关键词','b2').'">
                        <input type="hidden" name="type" v-model="type">
                        <div class="search-button"><button class="">'.__('搜索','b2').'</button></div>
                    </form>
                </div>
            </div>
        ';
    }

    public static function pay_box(){
        return '
            <div :class="[\'modal\',\'pay-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    <div class="pay-box-title"></div>
                    <div class="pay-box-content"></div>
                    <div class="pay-money">
                        <sup>'.B2_MONEY_SYMBOL.'</sup><span v-text="data.price"></span>
                    </div>
                    <div class="pay-my-money"><span v-text="data.myMoney"></span></div>
                    <div class="pay-type">
                        <div class="pay-alipay">'.__('支付宝','b2').'</div>
                        <div class="pay-weixin">'.__('微信支付','b2').'</div>
                        <div class="pay-yue">'.sprintf(__('%s支付','b2'),B2_MONEY_NAME).'</div>
                    </div>
                </div>
            </div>
        ';
    }

    //支付组件
    public static function ds_box(){
        $default_settings = b2_get_option('template_single','single_ds_group');
        $default_settings = isset($default_settings[0]) ? $default_settings[0] : array();
        $title = isset($default_settings['single_post_ds_title']) ? $default_settings['single_post_ds_title'] : __('打赏','b2');
        
        return '
            <div :class="[\'modal\',\'ds-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    <div class="pay-box-title">
                        <div :class="\'pay-box-left\'+(showtype == \'ds\' ? \' ds-pay-title\' : \'\')" ref="dstitle">
                            <template v-if="showtype == \'ds\'">
                                <img :src="author.avatar">
                                <span>'.__('给','b2').'{{author.name}}'.$title.'</span>
                            </template>
                            <template v-else-if="showtype == \'cz\' || showtype == \'card\'">
                                <span>'.__('充值','b2').'</span>
                            </template>
                            <template v-else-if="showtype == \'cg\'">
                                <span>'.__('积分购买','b2').'</span>
                            </template>
                            <template v-else>
                                {{data.title}}
                            </template>
                        </div>
                        <div class="pay-box-right">
                            <span class="pay-close" @click="close()">×</span>
                        </div>
                    </div>
                    <div v-if="showtype == \'cg\'" class="cg-info">
                        '.B2_MONEY_SYMBOL.sprintf(__('%s可购买%s积分，最低兑换额度%s','b2'),'<span>{{payMoney}}</span>','<span>{{creditAdd()}}</span>',B2_MONEY_SYMBOL.'<span>{{cg.min}}</span>').'
                    </div>
                    <div :class="\'pay-box-content\'+\' \'+showtype">
                        <template v-if="showtype == \'ds\'">
                            <div class="ds-msg">{{msg}}</div>
                            <ul class="ds-money">
                                <li v-for="(m,index) in money" @click="picked(m,index)" :class="value == index ? \'picked\' : \'\'"><div class="ds-item">'.b2_get_icon('b2-tang').'<span v-text="m"></span><b>'.B2_MONEY_SYMBOL.'</b></div></li>
                                <li @click="picked(0,5)" :class="value == 5 ? \'picked\' : \'\'">
                                    <label class="ds-item" v-if="value == 5">'.b2_get_icon('b2-tang').'<input type="number" id="dsinput" v-model="payMoney" oninput="value=value.replace(/[^\d]/g,\'\')"><b>'.B2_MONEY_SYMBOL.'</b></label>
                                    <label class="ds-item ds-item-custom" v-else for="dsinput">'.b2_get_icon('b2-edit-2-line').__('自定义','b2').'</label>
                                </li>
                            </ul>
                            <div class="ds-textarea">
                                <textarea placeholder="给Ta留言…" v-model="content"></textarea>
                            </div>
                        </template>
                        <template v-else-if="showtype == \'cz\'">
                            <div class="pay-box-desc">'.__('请输入充值金额：','b2').'</div>
                            <input class="pay-cz-input" type="number" v-model="payMoney" oninput="value=value.replace(/[^\d]/g,\'\')">
                        </template>
                        <template v-else-if="showtype == \'card\'">
                            <div class="pay-box-desc">'.__('请输入您的充值卡和密码：','b2').'</div>
                            <input type="text" v-model="card.number" placeholder="'.__('卡号','b2').'">
                            <input type="text" v-model="card.password" placeholder="'.__('密码','b2').'">
                            <div class="card-text">
                                <span v-html="card.text"></span>
                            </div>
                        </template>
                        <template v-else-if="showtype == \'cg\'">
                            <div class="pay-box-desc">'.__('请输入要购买的金额','b2').'</div>
                            <input type="number" v-model="payMoney" placeholder="'.__('金额','b2').'" oninput="value=value.replace(/[^\d]/g,\'\')">
                        </template>
                        <template v-else>
                            <div class="pay-box-desc">'.__('支付金额','b2').'</div>
                        </template>
                        <template v-if="showtype !== \'cz\' && showtype !== \'card\' && showtype !== \'cg\'">
                            <div :class="showtype !== \'ds\' ? \'ds-price\' : \'ds-price mar0\'">
                                <p class="ds-current-money"><i>'.B2_MONEY_SYMBOL.'</i><span v-text="payMoney"></span></p>
                            </div>
                        </template>
                        </div>
                        <div class="pay-my-money" v-if="login">
                            <span class="b2-radius">'.sprintf(__('您当前的%s为','b2'),B2_MONEY_NAME).B2_MONEY_SYMBOL.'{{user.money ? user.money : 0}}<a href="'.b2_get_custom_page_url('gold').'" class="b2-color" target="_blank" v-show="showtype != \'cz\' && showtype != \'card\'">'.sprintf(__('充值%s','b2'),B2_MONEY_NAME).'</a></span>
                            <p v-show="payMoney === 0 && payMoney != \'\' && showtype !== \'cz\' && showtype !== \'card\'" v-cloak>'.sprintf(__('商品价格为0元，请使用%s付款！','b2'),B2_MONEY_NAME).'</p>
                        </div>
                        <div class="pay-type">
                            <ul>
                                <li v-if="allow.alipay && !isWeixin"><button class="b2-radius" :class="payType == \'alipay\' ? \'picked\' : \'\'" @click="chosePayType(\'alipay\')" :disabled="payMoney == 0 && payType !== \'card\' ? true : false">'.b2_get_icon('b2-alipay-fill').'<span>'.__('支付宝','b2').'</span></button></li>
                                <li v-if="allow.wecatpay"><button class="b2-radius" :class="payType == \'wecatpay\' ? \'picked\' : \'\'" @click="chosePayType(\'wecatpay\')" :disabled="payMoney == 0 && payType !== \'card\' ? true : false">'.b2_get_icon('b2-wechat-pay-fill').'<span>'.__('微信','b2').'</span></button></li>
                                <li v-if="allow.paypal"><button class="b2-radius" :class="payType == \'paypal\' ? \'picked\' : \'\'" @click="chosePayType(\'paypal\')" :disabled="payMoney == 0 && payType !== \'card\' ? true : false">'.b2_get_icon('b2-paypal-fill').'<span>'.__('PayPal','b2').'</span></button></li>
                                <li v-if="allow.balance && login"><button class="b2-radius" :class="payType == \'balance\' ? \'picked\' : \'\'" @click="chosePayType(\'balance\')"><i class="ds-pay-yue">'.B2_MONEY_SYMBOL.'</i><span>'.B2_MONEY_NAME.'</span></button></li>
                                <li v-if="allow.card"><button class="b2-radius" :class="payType == \'card\' ? \'picked\' : \'\'" @click="chosePayType(\'card\')">'.b2_get_icon('b2-bank-card-fill').'<span>'.__('卡密','b2').'</span></button></li>
                            </ul>
                        </div>
                        <div class="pay-button">
                            <div>
                                <a v-if="jump == \'jump\' || jump == \'mweb\' || jump == \'jsapi\'" :href="!disabled() && href ? href : \'javascript:void(0)\'" :class="\'button \'+(disabled() ? \'disabled\' : \'\')" :target="!disabled() && href ? \'_blank\' : \'\'" @click="pay()"><span v-if="!payMoney && payType != \'card\'">'.__('请输入金额','b2').'</span><span v-else-if="!payType">'.__('请选择支付方式','b2').'</span><span v-else-if="waitOrder">'.__('创建订单中...','b2').'</span><span v-else-if="payType">'.__('支付','b2').'</span></a>
                                <button :class="locked ? \'b2-loading\' : \'\'" :disabled="disabled() || locked ? true : false" @click="pay()" v-else><span v-if="!payMoney && payType != \'card\'">'.__('请输入金额','b2').'</span><span v-else-if="!payType">'.__('请选择支付方式','b2').'</span><span v-else-if="payType">'.__('支付','b2').'</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    public static function address_box(){
        return '
        <div :class="[\'modal\',\'address-box\',{\'show-modal\':showAddressBox}]" v-cloak>
            <div class="modal-content b2-radius">
                <div class="pay-box-title">
                    <div class="pay-box-left">
                        '.__('选择地址','b2').'
                    </div>
                    <div class="pay-box-right">
                        <span class="pay-close" @click="close()">×</span>
                    </div>
                </div>
                <div class="pay-box-content">
                    <div class="address-picked" v-if="editAddressKey === \'\'">
                        <div v-if="emptyAddress()">
                            <ul>
                                <li v-for="(item,key) in address.addresses" :class="pickedAddress === key ? \'picked\' : \'\'" @click="pickedAddressAc(key)">
                                    <div class="address-list-left">
                                        '.b2_get_icon('b2-map-pin-fill').'
                                        <div class="address-list-info">
                                            <span v-text="item.province ? item.province+\' \'+item.city+\' \'+item.county+\' \'+item.address : item.address"></span>
                                            <span v-text="item.name"></span>
                                            <span v-text="item.phone"></span>
                                        </div>
                                    </div>
                                    <div class="address-list-right">
                                        <button class="text" @click.stop="deleteAddress(key)">'.__('删除','b2').'</button>
                                        <button class="text" @click.stop="editAddress(key)">'.__('编辑','b2').'</button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="carts-empty-address" v-else>
                            '.__('请添加一个收货地址','b2').'
                        </div>
                        <div class="address-edit-add">
                            <button @click="addNewAddress">'.__('添加新的地址','b2').'</button>
                        </div>
                    </div>
                    <div class="address-edit" v-else>
                        <label>
                            <p>'.__('收货地址','b2').'</p>
                            <input type="text" v-model="addressEditData.address">
                        </label>
                        <label>
                            <p>'.__('收货人姓名','b2').'</p>
                            <input type="text" v-model="addressEditData.name">
                        </label>
                        <label>
                            <p>'.__('收货人手机号码','b2').'</p>
                            <input type="text" v-model="addressEditData.phone">
                        </label>
                        <div class="address-edit-add">
                            <button class="text" @click="editAddressKey = \'\'">'.__('返回地址列表','b2').'</button>
                            <button @click="saveAddress()">'.__('保存地址','b2').'</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    public static function email_box(){
        return '
            <div :class="[\'modal\',\'address-box email-box\',{\'show-modal\':showEmailBox}]" v-cloak>
                <div class="modal-content b2-radius">
                    <div class="pay-box-title">
                        <div class="pay-box-left">
                            '.__('填写邮箱','b2').'
                        </div>
                        <div class="pay-box-right">
                            <span class="pay-close" @click="close()">×</span>
                        </div>
                    </div>
                    <div class="pay-box-content">
                        <input type="text" v-model="pickedEmail">
                    </div>
                    <div class="email-box-save">
                        <button @click="showEmailBox = false">'.__('保存','b2').'</button>
                    </div>
                </div>
            </div>
        ';
    }

    //支付确认
    public static function pay_check(){
        return '
            <div :class="[\'modal\',\'ds-box\',{\'show-modal\':show}]">
            <div class="modal-content b2-radius">
                <div class="pay-box-title">
                    <div class="pay-box-left" ref="dstitle" v-html="title">
                    </div>
                    <div class="pay-box-right">
                        <span class="pay-close" @click="close()">×</span>
                    </div>
                </div>
                <div class="pay-box-content">
                    <template v-if="type == \'card\'">
                        <div class="pay-check">
                            <div class="green">'.b2_get_icon('b2-check-double-line').'</div>
                            <h2>....'.__('充值成功','b2').'....</h2>
                            <div class="pay-check-button"><button @click="refresh()">'.__('确定','b2').'</button></div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="pay-check" v-if="success == false">
                            <div>'.b2_get_icon('b2-waiting').'</div>
                            <h2>....'.__('支付确认中','b2').'....</h2>
                            <div class="qrcode-time">「<span v-html="timesec"></span>」</div>
                        </div>
                        <div class="pay-check" v-if="success == \'fail\'">
                            <div class="red">'.b2_get_icon('b2-close-line').'</div>
                            <h2>....'.__('支付失败','b2').'....</h2>
                            <div class="pay-check-button"><button @click="close()">'.__('确定','b2').'</button></div>
                        </div>
                        <div class="pay-check" v-if="success == true">
                            <div class="green">'.b2_get_icon('b2-check-double-line').'</div>
                            <h2>....'.__('支付成功','b2').'....</h2>
                            <div class="pay-check-button"><button @click="refresh()">'.__('确定','b2').'</button></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        ';
    }

    //公告
    public static function gg_box(){
        $g_name = b2_get_option('normal_custom','custom_announcement_name');
        return '
            <div :class="[\'modal\',\'gg-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius" v-if="ggdata">
                    <span class="close-button" @click="close()">×</span>
                    <div class="gg-box-title" :style="ggdata.thumb ? \'background-image:url(\'+ggdata.thumb+\')\' : \'\'">
                        <div class="gg-title">
                            <h2>'.$g_name.'</h2>
                            <span>{{ggdata.date}}</span>
                        </div>
                        <div class="title-bg"></div>
                    </div>
                    <div class="gg-title"><a :href="ggdata.href" v-text="ggdata.title" @click="close()"></a></div>
                    <div class="gg-desc"><p v-html="ggdata.desc"></p></div>
                    <div class="gg-button"><a class="button" :href="ggdata.href" @click="close()">'.__('前往查看详情','b2').'</a></div>
                </div>
            </div>
        ';
    }

    //私信
    public static function directmessage_box(){
        return '
            <div :class="[\'modal\',\'dmsg-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    <div class="dmsg-box-header" v-if="type !== \'select\'" v-cloak>
                        <img :src="user.avatar"><span v-text="user.name"></span><span class="dmsg-edit-user" v-if="search == true && type == \'\'" @click="edit()">'.__('修改','b2').'</span>
                    </div>
                    <div class="dmsg-select" v-else v-cloak>
                        <p>'.__('请输入对方昵称，从下拉框里选择收件人','b2').'</p>
                        <input type="text" v-model="nickname" placeholder="'.__('搜索昵称','b2').'" readonly onfocus="this.removeAttribute(\'readonly\');" autocomplete="off">
                        <div class="dmsg-user-list" v-if="UserList.length > 0" v-cloak>
                            <ul>
                                <li v-for="user in UserList" @click="getUserData(user.id)">
                                    <img :src="user.avatar" class="b2-radius"/>
                                    <div class="dmsg-user-list-row">
                                        <h2 v-text="user.name"></h2>
                                        <p>{{user.desc ? user.desc : \''.__('暂无描述','b2').'\'}}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <span class="close-button" @click="close()">×</span>
                    <div class="">
                        <textarea id="textarea" :placeholder="user.name ? \''.__('给','b2').'\'+user.name+\''.__('发私信','b2').'\' : \''.__('私信内容','b2').'\'" class="dmsg-textarea" v-model="content"></textarea>
                    </div>
                    <div class="dmsg-button">
                        <button class="empty" @click="close()">'.__('取消','b2').'</button>
                        <button @click="send()" :disabled="content == \'\' || (type !== \'select\' && !user.name) ? true : false" :class="locked ? \'b2-loading\' : \'\'">'.__('发送','b2').'</button>
                    </div>
                </div>
            </div>
        ';
    }

    //扫码支付
    public static function Scan_box(){
        return '
        <div :class="[\'modal\',\'ds-box scan-box\',{\'show-modal\':show}]" data-money="">
            <div class="modal-content b2-radius">
                <div class="pay-box-title">
                    <div class="pay-box-left" ref="dstitle">
                        <span class="" v-html="data.title"></span>
                    </div>
                    <div class="pay-box-right">
                        <span class="pay-close" @click="close()">×</span>
                    </div>
                </div>
                <div class="pay-box-content">
                    <div class="pay-check" v-if="success === true">
                        <div class="green">'.b2_get_icon('b2-check-double-line').'</div>
                        <h2>....'.__('支付成功','b2').'....</h2>
                        <div class="pay-check-button"><button @click="refresh()">'.__('确定','b2').'</button></div>
                    </div>
                    <div class="pay-check" v-else-if="success === \'fail\'">
                        <div class="red">'.b2_get_icon('b2-close-line').'</div>
                        <h2>....'.__('支付失败','b2').'....</h2>
                        <div class="pay-check-button"><button @click="close()">'.__('确定','b2').'</button></div>
                    </div>
                    <div class="scan-info" v-else>
                        <div class="qrcode-img b2-radius"><img :src="backData.qrcode"></div>
                        <div class="qrcode-money"><span v-text="\''.B2_MONEY_SYMBOL.'\'+data.order_price"></span></div>
                        <div v-if="!backData.is_weixin && backData.is_mobile">'.sprintf(__('请截图使用%s识别支付','b2'),'<span v-if="data.pay_type == \'alipay\'" class="scan-alipay">'.__('支付宝','b2').'</span><span v-else class="scan-wecatpay">'.__('微信相册','b2').'</span>').'</div>
                        <div class="qrcode-desc" v-else>'.__('请打开手机使用','b2').'<span v-if="data.pay_type == \'alipay\'" class="scan-alipay">'.__('支付宝','b2').'</span><span v-else class="scan-wecatpay">'.__('微信','b2').'</span>'.__('扫码支付','b2').'</div>
                        <div class="qrcode-time">「<span v-html="timesec"></span>」</div>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    //积分支付
    public static function credit_box(){
        return '
            <div :class="[\'modal\',\'credit-pay-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    <div class="pay-box-title">
                        <div class="pay-box-left">
                            {{decodeURI(data.title)}}
                        </div>
                        <div class="pay-box-right">
                            <span class="pay-close" @click="close()">×</span>
                        </div>
                    </div>
                    <div class="pay-box-content">
                        <div class="credit-pay-title">
                            '.__('积分支付','b2').'
                        </div>
                        <div class="pay-credit">
                            '.b2_get_icon('b2-coin-line').'<span v-text="data.order_price"></span>
                        </div>
                        <div class="pay-my-money">
                            <span class="b2-radius">'.__('您当前的积分为','b2').b2_get_icon('b2-coin-line').'{{user.credit ? user.credit : 0}}</span>
                        </div>
                        <div v-show="parseInt(user.credit) < parseInt(data.order_price)" class="credit-tips b2-hover red">
                            '.sprintf(__('您当前的积分不足，请 %s购买积分%s','b2'),'<a href="'.b2_get_custom_page_url('gold').'/credit" target="blank">','</a>').'
                        </div>
                        <div class="pay-button">
                            <div>
                                <button :class="locked ? \'b2-loading\' : \'\'" :disabled="disabled()" @click="pay()">'.__('支付','b2').'</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    //关注公众号登录
    public static function mp_box(){
        return '
            <div :class="[\'modal\',\'mp-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    '.self::get_logo().'
                    <span class="pay-close" @click="close()">×</span>
                    <div class="mp-box-content">
                        <div class="invitation-box" v-show="token">
                            '.b2_get_icon('b2-gift-2-line').'
                            <p class="invitation-des">'.__('使用邀请码，您将获得一份特殊的礼物！', 'b2').'</p>
                            <p class="invitation-tips">'.__('请输入邀请码', 'b2').'</p>
                            <div class="invitation-input"><input type="text" id="invitation-code" name="invitation_code" v-model="invitationCode" autocomplete="off"></div>
                            <div class="invitation-button">
                                <div><a :href="invitationLink" target="__blank">{{invitationText}}</a></div>
                                <div>
                                    <b class="empty text button" v-show="invitation == 1" @click="checkInv()">'.__('跳过','b2').'</b>
                                    <button :class="[\'button\',{\'b2-loading\':locked}]" :disabled="locked" @click="checkInv()">'.__('提交','b2').'</button>
                                </div>
                            </div>
                        </div>
                        <div v-show="!token">
                            <div class="mp-login-img" id="mp-login-box"><img :src="qrcode" v-if="qrcode"></div>
                            <p>'.__('打开微信扫一扫','b2').'</p>
                            <p class="desc">{{oauthLink && oauthLink.weixin.pc_open ? \''.__('扫码快速登陆，无需重复注册','b2').'\' : \''.__('扫码并「关注我们的公众号」安全快捷登录','b2').'\'}}</p>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    //绑定微信账户
    public static function bind_weixin(){
        return '
            <div :class="[\'modal\',\'bind-weixin\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                   <div class="weixin-bind">
                        <p>{{msg ? msg : \'检测到您未绑定微信账户，请先绑定微信\'}}</p>
                        <a :href="url" class="button" v-if="!msg">立刻绑定</a>
                   </div>
                </div>
            </div>
        ';
    }

    //绑定微信账户
    public static function bind_phone_email(){
        $type = '<b v-if="type === \'tel\'">'.__('手机号码','b2').'</b><b v-else-if="type === \'email\'">'.__('邮箱','b2').'</b><b v-else-if="type === \'telandemail\'">'.__('手机号码或邮箱','b2').'</b><b v-else>'.__('登录用户名','b2').'</b>';
        return '
            <div :class="[\'modal\',\'binding-login\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    <div class="login-box-in">
                    <span class="pay-close" @click="close()">×</span>
                        <div class="bind-pass-info">
                            <img :src="userData.avatar" />
                            <p><span v-text="userData.name"></span></p>
                            <div v-if="!success">'.sprintf(__('为了确保您的账户安全%s请您设置一个%s和密码','b2'),'<br>',$type).'</div>
                        </div>
                        <div class="bind-success" v-if="success">
                            <p class="green">'.b2_get_icon('b2-check-double-line').__('设置成功','b2').'</p>
                            <div>'.sprintf(__('现在，您也可以通过下面的账户来登录%s','b2'),'<h2><span v-text="success"></span></h2>').'</div>
                            <p>'.__('祝您使用愉快','b2').'</p>
                            <button class="empty" @click="close">'.__('关闭','b2').'</button>
                        </div>
                        <template v-else>
                            <label class="login-form-item">
                                <input type="text" name="username" v-model="data.username" tabindex="1" spellcheck="false" autocomplete="off" class=""> 
                                <span>'.$type.'</span> 
                                <p class="login-box-des" v-if="type === \'tel\'">'.__('请填写您的手机号码','b2').'</p>
                                <p class="login-box-des" v-else-if="type === \'email\'">'.__('请填写您的邮箱','b2').'</p>
                                <p class="login-box-des" v-else-if="type === \'telandemail\'">'.__('请填写您的手机号码或邮箱','b2').'</p>
                                <p class="login-box-des" v-else>'.__('请填写您的的登录用户名','b2').'</p>
                                
                            </label>
                            <label :class="[\'login-form-item login-check-input\',{\'show\':showCheck()}]" v-if="type != \'login\'">
                                <input type="text" name="checkCode_bind" tabindex="3" class="" v-model="data.code" autocomplete="off" spellcheck="false"> 
                                <span><b>'.__('验证码', 'b2').'</b></span>
                                <b class="login-eye button text" @click.stop.prevent="!SMSLocked && count == 60 ? sendCode() : \'\'">{{count < 60 ? count+\''.__('秒后可重发', 'b2').'\' : \''.__('发送验证码', 'b2').'\'}}</b>
                            </label>
                            <label class="login-form-item">
                                <form>
                                 <input name="password" :type="eye ? \'text\' : \'password\'" tabindex="4" v-model="data.password" :class="data.password ? \'active\' : \'\'" autocomplete="off" spellcheck="false">
                                </form>
                                
                                <span><b>'.__('密码', 'b2').'</b></span>
                                <b class="login-eye button text" @click.stop.prevent="eye = !eye"><i :class="[\'b2font\',eye ? \'b2-eye-open\' : \'b2-eye-close\']"></i></b>
                                <p class="login-box-des">'.__('最少6位字符','b2').'</p>
                            </label>
                            <div class="login-bottom"><button class="" @click="submit" :class="locked ? \'b2-loading\' : \'\'" :disabled="locked">'.__('提交','b2').'</button></div>
                        </template>
                    </div>
                </div>
            </div>
        ';
    }

    public static function post_gg(){
        return '
            <div :class="[\'modal\',\'post-gg-box\',{\'show-modal\':show}]">
                <div class="modal-content b2-radius">
                    <div class="post-gg-title" v-html="title"></div>
                    <div class="post-gg-content" v-html="content"></div>
                    <div class="post-gg-button"><a class="b2-color" href="javascript:void(0)" @click="close">'.__('确定','b2').'</a></div>
                </div>
            </div>
        
        ';
    }
}