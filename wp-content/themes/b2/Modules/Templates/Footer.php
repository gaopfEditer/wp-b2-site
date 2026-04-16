<?php 
namespace B2\Modules\Templates;
use B2\Modules\Common\Post;
use B2\Modules\Common\Seo;

class Footer{
    public function init(){
        add_action( 'get_footer', array($this,'footer_html'));
        add_action('wp_footer',array($this,'footer_settings'),10);
        add_action( 'wp_footer',array($this,'weixin_share'), 9999);
    }

    public function footer_html(){
        //vue组件相关的html
        echo self::vue_template();
        self::aside_html();
    }

    public function footer_settings(){
        $footer_html = b2_get_option('normal_main','footer_code');
        if($footer_html){
           echo $footer_html;
        }
    }

    public function get_wxshare_data(){
        if(!b2_is_weixin()) return;
    
        $key = b2_get_option('normal_weixin','weixin_appid');
        $secret = b2_get_option('normal_weixin','weixin_appsecret');
        if(!$key || !$secret) return;

        $jssdk = new \JSSDK($key, $secret);
        $signPackage = $jssdk->GetSignPackage();
    
        if($signPackage){
            global $post;
            if(isset($post->ID)){
                $img = Post::get_post_thumb($post->ID);

                if(get_post_type($post->ID) == 'circle'){
                    $imgs = get_post_meta($post->ID,'b2_circle_image',true);
                    if(isset($imgs[0])){
                        $img = wp_get_attachment_url($imgs[0]);
                    }
                }
            }else{
                $img = b2_get_default_img();
            }

            $img = b2_get_thumb(array('thumb'=>$img,'width'=>300,'height'=>300));
    
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    
            return array('msg'=>$signPackage,'post_data'=>array(
                'imgUrl'=>$img,
                'link'=>$url,
                'desc'=>Seo::get_desc(),
                'title'=>str_replace('&#8211;','-',wp_get_document_title())
            ));
    
        }
    
        return;
    
    }

    public static function b2_copyright() {

        $copyright = wp_cache_get('b2_copyright', 'b2_copyright' );
        if($copyright) return $copyright;

        global $wpdb;
        $copyright_dates = $wpdb->get_results("
            SELECT
            YEAR(min(post_date_gmt)) AS firstdate,
            YEAR(max(post_date_gmt)) AS lastdate
            FROM
            $wpdb->posts
            WHERE
            post_status = 'publish' AND post_type = 'post'
        ");
            $output = '';
        if($copyright_dates) {

            $copyright = "&copy;" . $copyright_dates[0]->firstdate;

            if($copyright_dates[0]->firstdate != $copyright_dates[0]->lastdate) {
                $copyright .= '-' . $copyright_dates[0]->lastdate;
            }
        
            $output = $copyright;
        }

        wp_cache_set('b2_copyright',$output,'b2_copyright',DAY_IN_SECONDS);

        return $output;
    }

    public function weixin_share(){
        $share_data = $this->get_wxshare_data();

        if(!$share_data) return;
        $wx_data = $share_data['msg'];
        $post_data = $share_data['post_data'];

        $js_api_list = apply_filters('b2_footer_wx_js_api_list', ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo']);
        $js_api_str = "'".implode("','", $js_api_list)."'";

    ?>
    <script>
       
         wx.config ({
                debug : false,    // true:调试时候弹窗
                appId : '<?php echo $wx_data['appId']; ?>',  // 微信appid
                timestamp : '<?php echo $wx_data['timestamp']; ?>', // 时间戳
                nonceStr : '<?php echo $wx_data['nonceStr']; ?>',  // 随机字符串
                signature : '<?php echo $wx_data['signature']; ?>', // 签名
                jsApiList : [<?php echo $js_api_str; ?>]
            });
            
            wx.ready (function () {
                // 微信分享的数据
                var shareData = {
                    "imgUrl" : '<?php echo $post_data['imgUrl']; ?>',    // 分享显示的缩略图地址
                    "link" : '<?php echo $post_data['link']; ?>',    // 分享地址
                    "desc" : '<?php echo $post_data['desc']; ?>',   // 分享描述
                    "title" :'<?php echo $post_data['title']; ?>',   // 分享标题
                    success : function () {  
                        alert("分享成功"); 
                    } 
                };
                wx.onMenuShareTimeline (shareData); 
                wx.onMenuShareAppMessage (shareData); 
                wx.onMenuShareQQ (shareData); 
                wx.onMenuShareWeibo (shareData);
                <?php do_action('b2_footer_wx_js_api'); ?>            
            });

            wx.error(function(res){
                console.log(res);
            })
    </script>
    <?php
    }

    public static function footer_menu_str_to_html($str){
        if(!$str) return '';
        $str = trim($str," \t\n\r\0\x0B\xC2\xA0");
        $str = explode(PHP_EOL, $str );
        $arg = array();

        $aside_show = b2_get_option('template_aside','aside_show');

        if($aside_show){
            $login = '<a href="javascript:void(0)" onclick="window.event.cancelBubble = true;b2AsideBar.showAc(true)"><span v-if="!msg">'.b2_get_icon('b2-user-heart-line').'</span><span v-else v-cloak>'.b2_get_icon('b2-notification-3-line').'</span><b id="footer-menu-user">'.__('我的','b2').'</b><b class="footer-new-msg" v-show="msg" v-cloak></b></a>';
        }else{
            $login = '<a href="javascript:void(0)" onclick="window.event.cancelBubble = true;b2AsideBar.goMyPage()"><span v-if="!msg">'.b2_get_icon('b2-user-heart-line').'</span><span v-else v-cloak>'.b2_get_icon('b2-notification-3-line').'</span><b id="footer-menu-user">'.__('我的','b2').'</b><b class="footer-new-msg" v-show="msg" v-cloak></b></a>';
        }

        $top = '<a href="javascript:void(0)" onclick="b2AsideBar.goTop()"><span>'.b2_get_icon('b2-rocket-2-line').'</span><b>'.__('顶部','b2').'</b></a>';
        $search = '<a href="javascript:void(0)" onclick="b2SearchBox.show = true"><span>'.b2_get_icon('b2-search-line').'</span><b>'.__('搜索','b2').'</b></a>';
        $menu = '<a href="javascript:void(0)" onclick="mobileMenu.showAc(true)"><span>'.b2_get_icon('b2-menu-fill').'</span><b>'.__('菜单','b2').'</b></a>';
        $chat = '<a href="javascript:void(0)" onclick="b2AsideBar.chat()"><span>'.b2_get_icon('b2-customer-service-2-line1').'</span><b>'.__('客服','b2').'</b></a>';

        foreach ($str as $k => $v) {
            $v = trim($v," \t\n\r\0\x0B\xC2\xA0");
            if($v === 'login'){
                $arg[] = $login;
            }elseif($v === 'top'){
                $arg[] = $top;
            }elseif($v === 'search'){
                $arg[] = $search;
            }elseif($v === 'menu'){
                $arg[] = $menu;
            }elseif($v === 'chat'){
                $arg[] = $chat;
            }elseif(strpos($v,'|') !== false){
                $row = explode('|', $v );
                $icon = isset($row[0]) ? $row[0] : '';
                $text = isset($row[1]) ? $row[1] : '';
                $link = isset($row[2]) ? $row[2] : '';
                $arg[] = '<a href="'.$link.'"><span>'.$icon.'</span><b>'.$text.'</b></a>';
            }
        }

        return $arg;
    }

    public static function footer_menu_left(){
        $str = b2_get_option('template_footer','footer_menu_left');
        $arg = self::footer_menu_str_to_html($str);
        $html = '';

        if($arg){
            foreach ($arg as $k => $v) {
                $html .= $v;
            }
        }

        return $html;
    }

    public static function footer_menu_right(){
        $str = b2_get_option('template_footer','footer_menu_right');
        $arg = self::footer_menu_str_to_html($str);

        $html = '';

        if($arg){
            foreach ($arg as $k => $v) {
                $html .= $v;
            }
        }

        return $html;
    }

    public static function vue_template(){

        $single = is_singular();

        //是否开启注册
        $allow_register = b2_get_option('normal_login','allow_register');

        //注册验证形式
        $check_type = b2_get_option('normal_login','check_type');

        $login_text = __('手机号或邮箱', 'b2');

        switch ($check_type) {
            case 'tel':
                $login_text = __('手机号', 'b2');
                break;
            case 'email':
                $login_text = __('邮箱', 'b2');
                break;
            case 'telandemail':
                $login_text = __('手机号或邮箱', 'b2');
                break;
            default:
                $login_text = __('用户名', 'b2');
                break;
        }

        //是否启用验证码
        $inv = b2_get_inv_settings();

        $html = '
            <!-- 登陆与注册组件 -->
            <div id="login-box">
                <login-box 
                ref="loginBox"
                :show="show" 
                :allow-register="\''.$allow_register.'\'" 
                :check-type="\''.$check_type.'\'" 
                :login-type="loginType" 
                :login-text="\''.$login_text.'\'"
                :invitation="\''.$inv['type'].'\'"
                :invitation-link="\''.$inv['link'].'\'"
                :invitation-text="\''.$inv['text'].'\'"
                :img-box-code="imgCode"
                @close-form="close" 
                @login-ac="loginAc" v-cloak></login-box>
            </div>

            <!-- 验证码组件 -->
            <div id="recaptcha-form">
                <recaptcha-box :show="show" :type="type" @close-form="close" v-cloak></recaptcha-box>
            </div>

            <!-- 搜索组件 -->
            <div id="search-box">
                <search-box :show="show" :search-type="searchType" @close="close" v-cloak></search-box>
            </div>

            <!-- 公告弹窗 -->
            <div id="gg-box">
                <gg-box :show="show" @close="close" v-cloak></gg-box>
            </div>
            
            <!-- 私信弹窗 -->
            <div id="dmsg-box" @click.stop="">
                <dmsg-box :show="show" :userid="userid" :type="select" @close="close" v-cloak></dmsg-box>
            </div>

            <!-- 扫码支付 -->
            <div id="scan-box" @click.stop="">
                <scan-box :show="show" :data="data" @close="close" v-cloak></scan-box>
            </div>

            <!-- 支付检查 -->
            <div id="pay-check" @click.stop="">
                <check-box :show="show" :title="title" :type="type" :payt="payType" @close="close" v-cloak></check-box>
            </div>

            <!-- 支付组件 -->
            <div id="ds-box" ref="dsmoney">
                <ds-box :show="show" :money="money" :msg="msg" :user="user" :author="author" :data="data" :showtype="showtype" @close="close" @clean="clean" @change="change" v-cloak></ds-box>
            </div>

            <!-- 积分支付组件 -->
            <div id="credit-box" ref="creditbox">
                <credit-box :show="show" :data="data" :user="user" @close="close" v-cloak></credit-box>
            </div>

            <!-- 财富页面组件 -->
            <div id="money-buy" ref="moneyBuy">
                <money-buy :show="show" :data="data" :user="user" @close="close" v-cloak></money-buy>
            </div>

            <!-- 微信绑定组件 -->
            <div id="weixin-bind" ref="weixinBind">
                <weixin-bind :show="show" :url="url" :msg="msg" @close="close" v-cloak></weixin-bind>
            </div>

            <!-- 公告弹窗 -->
            <div id="post-gg">
                <post-gg :show="show" :title="title" :content="content" @close="close" v-cloak></post-gg>
            </div>

            <!-- 关注公众号登录 -->
            <div id="mp-box">
                <mp-box ref="b2mp"
                    :show="show"             
                    :invitation="\''.$inv['type'].'\'"
                    :invitation-link="\''.$inv['link'].'\'"
                    :invitation-text="\''.$inv['text'].'\'" 
                    @close="close" 
                v-cloak></mp-box>
            </div>

            <!-- 社交强制绑定 -->
            <div id="binding-login" ref="bindLogin">
                <bind-login :show="show" :type="type" @close="close" v-cloak ref="bindBox"></bind-login>
            </div>
        ';

        if($single){

            $html .= '
                <!-- 海报组件 -->
                <div id="poster-box">
                    <poster-box :show="show" :data="data" @close-form="close" v-cloak></poster-box>
                </div>
            ';
        }

        return $html;
    }

    public static function aside_html(){
        $aside_show = b2_get_option('template_aside','aside_show');
        if(!$aside_show) return;
        $aside_user = b2_get_option('template_aside','aside_user');
        $aside_mission = b2_get_option('template_aside','aside_mission');
        $aside_message = b2_get_option('template_aside','aside_message');
        $aside_dmsg = b2_get_option('template_aside','aside_dmsg');
        $aside_qrcode = b2_get_option('template_aside','aside_qrcode');
        $aside_vip = b2_get_option('template_aside','aside_vip');

        global $wp;
        $current_url = B2_HOME_URI.'/'.add_query_arg(array(),$wp->request);

        $qrhtml = '<li class="b2-radius"><img :src="getQrcode(\''.$current_url.'\')"><p>'.__('扫码打开当前页','b2').'</p></li>';
        if(is_array($aside_qrcode)){
            foreach ($aside_qrcode as $k => $v) {
                if($v['qrcode_img']){
                    $qrhtml .= '<li class="b2-radius"><img src="'.$v['qrcode_img'].'"><p>'.$v['qrcode_desc'].'</p></li>';
                }
            }
        }

        $dmsg_url = b2_get_custom_page_url('directmessage');

        $shop_open = b2_get_option('shop_main','shop_open');
        $audit = is_audit_mode();

        $chat_open = b2_get_option('template_aside','aside_chat_type');
        ?>
        <div class="aside-container" @click.stop="" ref="asideContainer">
            <div class="aside-bar">
                <div class="bar-middle" v-cloak>
                    <div class="bar-top" v-if="showBox" @click="showAc(false)" v-cloak>
                        <div>
                            ❯
                        </div>
                    </div>
                    <?php if($aside_vip && !$audit){ ?>
                        <div class="gdd-quick-link-buy-vip">
                            <a target="_blank" href="<?php echo b2_get_custom_page_url('vips'); ?>">
                                <div class="gdd-quick-link-buy-vip__hover-block">
                                    <img src="<?php echo B2_THEME_URI.'/Assets/fontend/images/vip-youce.svg'; ?>"> 
                                    <p><?php echo __('解锁会员权限','b2'); ?></p>
                                </div>
                            </a> 
                            <div class="gdd-quick-link-buy-vip__popover">
                                <div>
                                    <p class="gdd-quick-link-buy-vip__popover--title"><?php echo __('开通会员','b2'); ?></p> 
                                    <p class="gdd-quick-link-buy-vip__popover--desc"><?php echo __('解锁海量优质VIP资源','b2'); ?></p> 
                                    <a target="_blank" href="<?php echo b2_get_custom_page_url('vips'); ?>"><p class="gdd-quick-link-buy-vip__popover--btn"><?php echo __('立刻开通','b2'); ?></p></a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="bar-normal">
                        <?php if($aside_user && !$audit) { ?>
                            <div :class="['bar-item',{'active':showType.user}]" @click="show('user')">
                                <?php echo b2_get_icon('b2-user-heart-line'); ?>
                                <span class="bar-item-desc"><?php echo __('个人中心','b2'); ?></span>
                            </div>
                        <?php } ?>
                        <?php if($shop_open && !$audit) { ?>
                            <div :class="['bar-item',{'active':showType.cart}]" @click="show('cart')">
                                <?php echo b2_get_icon('b2-shopping-cart-2-line'); ?>
                                <b v-show="carts.count" class="bar-mark"></b>
                                <span class="bar-item-desc"><?php echo __('购物车','b2'); ?></span>
                            </div>
                        <?php } ?>
                        <?php if($shop_open && !$audit) { ?>
                            <div :class="['bar-item',{'active':showType.coupon}]" @click="show('coupon')">
                                <?php echo b2_get_icon('b2-coupon-2-line'); ?>
                                <span class="bar-item-desc"><?php echo __('优惠劵','b2'); ?></span>
                            </div>
                        <?php } ?>
                        <?php if($aside_mission && !$audit) { ?>
                            <div :class="['bar-item bar-mission',{'active':showType.mission}]" @click="show('mission')">
                                <?php echo b2_get_icon('b2-gift-2-line'); ?>
                                <span class="bar-item-desc"><?php echo __('今日签到','b2'); ?></span>
                            </div>
                        <?php } ?>
                        <?php if($aside_dmsg && !$audit) { ?>
                            <div :class="['bar-item',{'active':showType.dmsg}]" @click="show('dmsg','<?php echo $dmsg_url;?>')">
                                <b v-show="dmsg.count" class="bar-mark"></b>
                                <?php echo b2_get_icon('b2-mail-send-line'); ?>
                                <span class="bar-item-desc" v-if="dmsg.count"><?php echo __('有新私信','b2'); ?></span>
                                <span class="bar-item-desc" v-else><?php echo __('私信列表','b2'); ?></span>
                            </div>
                        <?php } ?>
                        <!-- <?php if($aside_message && !$audit) { ?>
                            <div :class="['bar-item',{'active':showType.msg}]" @click="show('msg','<?php echo b2_get_custom_page_url('message');?>')">
                                <?php echo b2_get_icon('b2-notification-3-line'); ?>
                                <b v-show="msg.count" class="bar-mark" v-text="msg.count"></b>
                                <span class="bar-item-desc" v-if="msg.count"><?php echo __('有新消息','b2'); ?></span>
                                <span class="bar-item-desc" v-else><?php echo __('消息中心','b2'); ?></span>
                            </div>
                        <?php } ?> -->
                        <?php if (1 === B2_ASIDE_SEARCH) { ?>
                            <div class="bar-item" @click="showSearch()">
                                <?php echo b2_get_icon('b2-search-line'); ?>
                                <span class="bar-item-desc"><?php echo __('搜索','b2'); ?></span>
                            </div>
                        <?php } ?>

                        <?php do_action('b2_asidecontainer'); ?>
                    </div>
                    <div class="bar-footer">
                        <?php if($chat_open !== 'none'){ ?>
                            <div class="bar-item" @click="chat">
                                <?php echo b2_get_icon('b2-customer-service-2-line1'); ?>
                                <span class="bar-item-desc"><?php echo __('客服','b2'); ?></span>
                            </div>
                        <?php } ?>
                        <div class="bar-item bar-qrcode">
                            <?php echo b2_get_icon('b2-qr-code-fill'); ?>
                            <div class="bar-item-desc bar-qrcode-box">
                                <ul><?php echo $qrhtml; ?></ul>
                            </div>
                        </div>
                        <div class="bar-item" @click="goTop">
                            <?php echo b2_get_icon('b2-rocket-2-line'); ?>
                            <span class="bar-item-desc"><?php echo __('返回顶部','b2'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bar-user-info" ref="asideContent" v-cloak>
                <div class="bar-box bar-mission" v-if="showType.mission && mission.data !== ''">
                    <div v-cloak>
                        <div :class="['bar-user-info-row bar-mission-action',{'cur':mission.data.mission.credit === ''}]" @click="mission.mission()" v-if="mission.data !== ''">
                            <div class="" v-if="mission.locked"><?php echo b2_get_icon('b2-coin-line').__('幸运之星正在降临...','b2'); ?></div>
                            <div class="" v-else-if="!mission.data.mission.credit"><?php echo b2_get_icon('b2-coin-line').__('点击领取今天的签到奖励！','b2'); ?></div>
                            <div class="" v-else><?php echo b2_get_icon('b2-coin-line').sprintf(__('恭喜！您今天获得了%s积分','b2'),'<b>{{mission.data.mission.credit}}</b>'); ?></div>
                        </div>
                        <div class="bar-user-info-row">
                            <div class="user-w-qd-list-title">
                                <p :class="mission.type == 'today' ? 'picked' : ''" @click="mission.type = 'today'"><?php echo __('今日签到','b2'); ?></p>
                                <p :class="mission.type == 'always' ? 'picked' : ''" @click="mission.type = 'always'"><?php echo __('连续签到','b2'); ?></p>
                            </div>
                            <div class="mission-today-list" v-cloak>
                                <ul v-if="mission.type === 'today'">
                                    <li v-for="item in mission.data.mission_today_list.data">
                                        <a :href="item.user.link" class="user-link-block avatar-parent"><img :src="item.user.avatar" class="b2-radius avatar"><span v-if="item.user.user_title" v-html="item.user.verify_icon"></span></a>
                                        <div class="user-mission-info">
                                            <div class="user-mission-info-left">
                                                <a :href="item.user.link"><p v-text="item.user.name"></p></a>
                                                <p v-html="item.date"></p>
                                            </div>
                                            <div class="user-mission-info-right">
                                                <span class="user-money"><?php echo b2_get_icon('b2-coin-line'); ?>{{item.credit}}</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <ul v-else>
                                    <li v-for="item in mission.data.mission_always_list.data">
                                        <a :href="item.user.link" class="user-link-block avatar-parent"><img :src="item.user.avatar" class="b2-radius avatar"><span v-if="item.user.user_title" v-html="item.user.verify_icon"></span></a>
                                        <div class="user-mission-info">
                                            <div class="user-mission-info-left">
                                                <a :href="item.user.link"><p v-text="item.user.name"></p></a>
                                                <p v-html="item.date"></p>
                                            </div>
                                            <div class="user-mission-info-right">
                                                <?php echo __('连续','b2'); ?>{{item.count}}<?php echo __('天','b2'); ?>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="bar-user-info-row" style="padding:5px 10px">
                            <a href="<?php echo b2_get_custom_page_url('mission'); ?>"><?php echo __('查看所有','b2'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="bar-box" v-if="showType.coupon">
                    <div class="bar-user-info-row">
                        <div class="new-dmsg-title"><?php echo __('我的优惠劵','b2'); ?></div>
                    </div>
                    <div class="bar-user-info-row aside-carts-list" v-if="coupon.count > 0">
                        <ul>
                            <li v-for="item in coupon.data">
                                <div class="shop-coupon-item">
                                    <div :class="'stamp b2-radius ' + couponClass(item)">
                                        <div class="par">
                                            <p v-if="couponClass(item) == 'stamp01'" v-text="'<?php echo __('限制商品','b2'); ?>'"></p>
                                            <p v-else-if="couponClass(item) == 'stamp02'" v-text="'<?php echo __('限制商品分类','b2'); ?>'"></p>
                                            <p v-else v-text="'<?php echo __('不限制使用','b2'); ?>'"></p>
                                            <sub class="sign"><?php echo B2_MONEY_SYMBOL; ?></sub><span v-text="item.money"></span><sub><?php echo __('优惠劵','b2'); ?></sub>
                                            <div class="coupon-date">
                                                <div>
                                                    <div class="" v-if="item.expiration_date.expired"><?php echo __('使用时效：','b2'); ?><span><?php echo __('无法使用','b2'); ?></span></div>
                                                    <div class="coupon-desc" v-else-if="item.expiration_date.date != 0"><?php echo __('使用时效：','b2'); ?><p><span v-text="item.expiration_date.expired_date"></span><?php echo __('之前','b2'); ?></p></div>
                                                    <div class="coupon-desc" v-else><?php echo __('使用时效：','b2'); ?><?php echo __('永久有效','b2'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <i class="coupon-bg"></i>
                                    </div>
                                    <div class="coupon-info b2-radius" v-show="showCouponInfo !== [] && showCouponInfo[item.id] == true" v-cloak>
                                        <div class="shop-coupon-title">
                                            <div class="coupon-title"><span><?php echo __('优惠劵ID：','b2'); ?>
                                                </span><span class="coupon-id" v-text="item.id"></span>
                                            </div>
                                            <span class="close-coupon-info" @click="couponMoreInfo(item.id)">×</span>
                                        </div>
                                        <div class="">
                                            <span class="coupon-title" v-if="couponClass(item) == 'stamp01'"><?php echo __('限制以下商品使用：','b2'); ?></span>
                                            <span class="coupon-title" v-else-if="couponClass(item) == 'stamp02'"><?php echo __('限制以下商品分类使用：','b2'); ?></span>
                                            <span class="coupon-title" v-else><?php echo __('不限制使用：','b2'); ?></span>
                                            <div class="" v-if="couponClass(item) == 'stamp01'">
                                                <a :href="it.link" target="_blank" v-for="it in item.products" ><img :src="it.image" :title="it.name"/></a> 
                                            </div>
                                            <div class="" v-else-if="couponClass(item) == 'stamp02'">
                                                [<a :href="ct.link" target="_blank" v-for="ct in item.cats">{{ct.name}}</a>] 
                                            </div>
                                            <div class="" v-else>
                                                <?php echo __('所有商品和商品类型均可使用','b2'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bar-coupon-button">
                                    <button class="text" @click="deleteCoupon(item.id)"><?php echo __('删除','b2'); ?></button>
                                    <button class="text" @click="couponMoreInfo(item.id)"><?php echo __('详情','b2'); ?></button>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="bar-user-info-row aside-cart-empty" v-else v-cloak>
                        <?php echo __('没有优惠劵可用!','b2'); ?>
                        <p><?php echo b2_get_icon('b2-notification-badge-line'); ?></p>
                    </div>
                </div>
                <div class="bar-box" v-if="showType.cart">
                    <div class="bar-user-info-row">
                        <div class="new-dmsg-title"><?php echo __('购物车','b2'); ?></div>
                    </div>
                    <div class="bar-user-info-row aside-carts-list" v-if="carts.count > 0">
                        <ul>
                            <li v-for="item in carts.data">
                                <div class="aside-carts-title">
                                    <img :src="item.thumb" class="b2-radius">
                                    <div>
                                        <a :href="item.link" v-html="item.title"></a>
                                        <template v-if="item.desc.length > 0">
                                            <div v-for="(_item,i) in item.desc" class="shop-item-desc">
                                                <span v-text="_item.name"></span>:
                                                <span v-text="_item.value"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="aside-carts-price">
                                    <div class="aside-carts-price-left"><span v-text="'<?php echo B2_MONEY_SYMBOL; ?>'+item.price.current_price"></span>×<span v-text="item.count"></span></div>
                                    <div class="aside-carts-delete" @click="deleteCarts(item.id)"><?php echo __('删除','b2'); ?></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="bar-user-info-row aside-cart-empty" v-else v-cloak>
                        <?php echo __('购物车空空如也!','b2'); ?>
                        <p><?php echo b2_get_icon('b2-notification-badge-line'); ?></p>
                    </div>
                    <div class="bar-user-info-row bar-dmsg-write">
                        <a class="text" href="javascript:void(0)" @click="deleteCarts('all')"><?php echo __('清空购物车','b2'); ?></a>
                        <a href="<?php echo b2_get_custom_page_url('carts'); ?>"><?php echo __('前往结算','b2'); ?></a>
                    </div>
                </div>
                <div class="bar-box" v-if="showType.dmsg && dmsg.count > 0">
                    <div class="bar-user-info-row">
                        <div class="new-dmsg-title" v-if="dmsg.count"><?php echo __('您有新的私信','b2'); ?></div>
                        <div class="new-dmsg-title" v-else><?php echo __('没有新私信','b2'); ?></div>
                    </div>
                    <div class="bar-user-info-row">
                        <ul v-if="dmsg.data.length > 0" class="bar-dmsg-list">
                            <li v-for="item in dmsg.data" @click="jumpTo('<?php echo $dmsg_url;?>/to/'+item.from.id)">
                                <img class="avatar b2-radius" :src="item.from.avatar">
                                <div class="new-dmsg-content">
                                    <h2 v-text="item.from.name"></h2>
                                    <div class="b2-radius jt" v-html="item.content"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="bar-user-info-row bar-dmsg-write">
                        <a class="text" @click="b2Dmsg.show = true;b2Dmsg.select = 'select'" href="javascript:void(0)"><?php echo b2_get_icon('b2-quill-pen-line').__('写新私信','b2'); ?></a>
                        <a href="<?php echo $dmsg_url; ?>"><?php echo __('查看全部','b2'); ?></a>
                    </div>
                </div>
                <div class="bar-box" v-if="showType.user">
                    <template v-if="b2token">
                        <div class="bar-user-info-row">
                            <div class="user-w-avatar">
                                <a :href="userData.link" class="avatar-parent"><img :src="userData.avatar" v-if="userData.avatar" class="avatar b2-radius"/></a>
                            </div>
                            <div class="user-w-name">
                                <a :href="userData.link"><h2 v-text="userData.name"></h2><span v-html="userData.verify_icon" v-if="userData.verify"></span></a>
                                <div class="user-w-lv">
                                    <div v-html="userData.lv.vip.icon" v-if="userData.lv.vip.icon"></div><div v-html="userData.lv.lv.icon" v-if="userData.lv.lv.icon"></div>
                                </div>
                            </div>
                            <div class="user-w-tj">
                                <div>
                                    <p><?php echo __('文章','b2'); ?></p>
                                    <span v-text="userData.post_count"></span>
                                </div>
                                <div>
                                    <p><?php echo __('评论','b2'); ?></p>
                                    <span v-text="userData.comment_count"></span>
                                </div>
                                <div>
                                    <p><?php echo __('关注','b2'); ?></p>
                                    <span v-text="userData.following"></span>
                                </div>
                                <div>
                                    <p><?php echo __('粉丝','b2'); ?></p>
                                    <span v-text="userData.followers"></span>
                                </div>
                            </div>
                        </div>
                        <div class="bar-user-info-row my-order-button">
                            <a :href="userData.link"><?php echo b2_get_icon('b2-user-heart-line').__('个人中心','b2'); ?></a>
                        </div>

                        
                        <div class="bar-user-info-row my-order-button">
                            <a :href="userData.link+'/orders'"><?php echo b2_get_icon('b2-file-list-2-line').__('我的订单','b2'); ?></a>
                        </div>
                        <?php if(b2_get_option('verify_main','verify_allow')){ ?>
                            <div class="bar-user-info-row">
                                <div class="bar-user-info-row-title"><a href="<?php echo b2_get_custom_page_url('verify'); ?>"><span><?php echo __('认证','b2'); ?></span><span>❯</span></a></div>
                                <div class="user-w-rw">
                                    <p v-if="userData.verify">
                                        <span class="aside-verify"><b v-text="userData.verify"></b></span>
                                    </p>
                                    <p v-else><?php echo __('未认证','b2'); ?></p>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="bar-user-info-row">
                            <div class="bar-user-info-row-title"><a href="<?php echo b2_get_custom_page_url('task'); ?>"><span><?php echo __('任务','b2'); ?></span><span>❯</span></a></div>
                            <div class="user-w-rw">
                                <div class="bar-user-next-lv">
                                    <div class="bar-lv-bar" :style="'width:'+userData.task+'%'"></div>
                                    <span><?php echo __('今日任务完成','b2'); ?></span>
                                    <span v-text="userData.task+'%'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="bar-user-info-row">
                            <div class="bar-user-info-row-title"><a href="<?php echo b2_get_custom_page_url('gold'); ?>"><span><?php echo __('升级','b2'); ?></span><span>❯</span></a></div>
                            <div class="user-w-rw">
                                <div class="bar-user-next-lv">
                                    <div class="bar-lv-bar" :style="'width:'+userData.lv.lv.lv_ratio+'%'"></div>
                                    <span v-text="userData.lv.lv.name+'→'+userData.lv.lv.lv_next_name"></span>
                                    <span v-text="userData.lv.lv.lv_ratio+'%'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="bar-user-info-row">
                            <div class="bar-user-info-row-title"><a href="<?php echo b2_get_custom_page_url('gold'); ?>"><span><?php echo __('财富','b2'); ?></span><span>❯</span></a></div>
                            <div class="user-w-gold">
                                <div class="user-money"><a href="<?php echo b2_get_custom_page_url('gold'); ?>"><span><?php echo B2_MONEY_SYMBOL; ?></span><b><?php echo B2_MONEY_NAME.'：'; ?>{{userData.money}}</b></a></div>
                                <div class="user-credit"><a href="<?php echo b2_get_custom_page_url('gold'); ?>"><span><?php echo b2_get_icon('b2-coin-line'); ?></span><b><?php echo __('积分：','b2'); ?>{{userData.credit}}</b></a></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <?php
        if(is_singular()){
            global $post;
            if(isset($post->ID)){
                $seo = Seo::single_meta($post->ID);
                echo '
                    <script type="application/ld+json">
                        {
                            "@context": "https://ziyuan.baidu.com/contexts/cambrian.jsonld",
                            "@id": "'.$seo['url'].'",
                            "appid": "否",
                            "title": "'.$seo['title'].'",
                            "images": ["'.$seo['image'].'"],
                            "description": "'.$seo['description'].'",
                            "pubDate": "'.get_the_time('Y-m-d\TH:i:s',$post->ID).'",
                            "upDate": "'.get_the_modified_time('Y-m-d\TH:i:s',$post->ID).'"
                        }
                    </script>
                ';
            }
            ?>
            
            <?php
        }
    }
}