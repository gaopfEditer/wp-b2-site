<?php
$verify = b2_get_option('verify_main','verify_type');
$allow = b2_get_option('verify_main','verify_allow');
if(!$allow){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}
/**
 * 认证页面
 */
get_header();

?>
<div id="verify" class="verify" ref="verify">
    <div class="content-area vip-page wrapper">
        <main id="main" class="site-main b2-radius">
            <div class="verify-header" v-cloak>
                <div :class="['verify-number',{'picked':step >= 1}]">
                    <span>1</span>
                    <p><?php echo __('说明','b2'); ?></p>
                </div>
                <div :class="['verify-number',{'picked':step >= 2}]">
                    <span>2</span>
                    <p><?php echo __('认证信息','b2'); ?></p>
                </div>
                <div :class="['verify-number',{'picked':step >= 3 && data.status === '4'}]" v-if="step == 3 && data.status === '4'">
                    <span>3</span>
                    <p><?php echo __('认证审核中','b2'); ?></p>
                </div>
                <div :class="['verify-number',{'picked':step >= 3 && data.status == 2}]" v-else>
                    <span>3</span>
                    <p><?php echo __('认证完成','b2'); ?></p>
                </div>
            </div>
            <div class="verify-step-1" v-show="step == 1" v-cloak>
                <div class="verify-content box">
                    <div class="">
                        <img src="<?php echo b2_get_option('verify_main','verify_img'); ?>" />
                    </div>
                    <div class="verify-desc">
                        <?php echo b2_get_option('verify_main','verify_text'); ?>
                    </div>
                    <div class="verify-users">
                        <div class="verify-users-title"><span><?php echo sprintf(__('他们也在%s','b2'),B2_BLOG_NAME); ?></span></div>
                        <div class="" v-if="users === ''">
                            <div class="button empty b2-loading empty-page text"></div>
                        </div>
                        <div class="verify-none" v-else-if="users.length == 0" v-cloak>
                            <span v-cloak><?php echo __('暂无认证用户','b2'); ?></span>
                        </div>
                        <ul v-else-if="users.length > 0" v-cloak>
                            <li v-for="user in users">
                                <div class="verify-box box b2-radius">
                                    <div class="verify-box-avatar">
                                        <a :href="user.link" target="_blank"><img :src="user.avatar" class="b2-radius avatar" /><span v-html="user.verify_icon"></span></a>
                                    </div>
                                    <div class="verify-box-name"><a :href="user.link" target="_blank">{{user.name}}</a></div>
                                    <div class="verify-box-desc"><a :href="user.link" target="_blank">{{user.user_title ? user.user_title : (user.desc ? user.desc : '<?php echo __('没有称号','b2'); ?>')}}</a></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="verify-button" v-if="status !== ''" v-cloak>
                    <button @click="goStep(2)" ><?php echo __('立即申请','b2'); ?></button>
                    <!-- <p v-else class="green"><?php echo __('您已成功认证','b2'); ?></p> -->
                </div>
            </div>
            <div class="verify-step-2 box" v-show="step == 2 || step == 3" v-cloak>
                <div v-if="step == 3" class="verify-page-title">
                    <h1 v-if="data.status == 4" style="color:blue"><?php echo __('您的认证正在审核中','b2'); ?></h1>
                    <h1 v-else class="green"><?php echo __('您已认证成功','b2'); ?></h1>
                </div>
                <div class="verify-chenghao">
                    <div class="verify-shiming">
                        <h2><?php echo __('给自己一个称号：','b2'); ?></h2> 
                        <input type="text" v-model="data.title" :disabled="step == 3 ? true : false">
                        <p class="desc"><?php echo __('比如：汽车工程师、健康达人、财经分析师、二次元元老等有趣的称号（限制30个字以内）','b2'); ?></p>
                    </div>
                </div>
                <?php 
                //身份证信息
                if(in_array(1,$verify)){ ?>
                    <h2><?php echo __('实名信息','b2'); ?></h2>
                    <div class="verify-shiming">
                        <label><span><?php echo __('真实姓名：','b2'); ?></span><input class="" type="text" v-model="data.name" :disabled="step == 3 ? true : false"></label>
                        <label><span><?php echo __('身份证号码：','b2'); ?></span><input class="" type="text" v-model="data.identification" :disabled="step == 3 ? true : false"></label>
                        <div class="">
                            <label><span><?php echo __('持证件证明照：','b2'); ?></span></label>
                            <div class="shiming-cankao">
                                <label class="shiming-cankao-left" for="shiming-input">
                                    <span v-if="!data.card"><b v-if="fileLocked"><?php echo __('上传中....','b2'); ?></b><b v-else><?php echo __('点击上传','b2'); ?></b></span>
                                    <img :src="data.card" v-else/>
                                </label>
                                <div class="shiming-cankao-right">
                                    <img src="<?php echo B2_THEME_URI.'/Assets/fontend/images/shiming.png'; ?>">
                                </div>
                            </div>
                            <input id="shiming-input" type="file" class="b2-hidden-always" ref="fileInput" @change="getFile($event)" accept="image/jpg,image/jpeg,image/png,image/gif" :disabled="step == 3 ? true : false">
                        </div>
                    </div>
                <?php } 
                //关注公众号
                if(in_array(2,$verify)){ ?>
                    <h2><?php echo __('关注公众号','b2'); ?></h2>
                    <div class="verify-shiming" ref="mp">
                        <div class="verify-mp">
                            <div class="verify-mp-right">
                                <div class="verify-qrcode"><span v-if="!show" @click="showQrcode()"><?php echo __('点击获取二维码','b2'); ?></span><img :src="mp.qrcode" v-if="mp.length != 0"/></div>
                                <p class="mp-status"><?php echo __('状态：','b2'); ?>
                                    <span v-if="mp.status == 1" class="green"><?php echo __('已关注','b2'); ?></span>
                                    <span v-if="mp.status == 0" class="red"><?php echo __('未关注','b2'); ?></span>
                                    <span v-if="mp.status == 2" class="red"><?php echo __('已入黑名单','b2'); ?></span>
                                    <br><span v-show="show">({{seconds}}<?php echo __('秒后重新获取','b2'); ?>)</span>
                                </p>
                                <p v-if="mp.is_weixin" v-cloak><?php echo __('请长按二维码进入公众号','b2'); ?></p>
                            </div>
                            <div class="verify-mp-left">
                                <p class="desc"><?php echo sprintf(__('请扫码关注我们的公众号，%s如果取消关注将会拉黑名单，不再允许认证，认证也会失效%s。','b2'),'<span class="red">','</span>'); ?></p>
                                <p class="desc"><?php echo sprintf(__('如果您已在黑名单里，希望再次认证，请联系管理员。','b2'),'<span class="red">','</span>'); ?></p>
                                <p class="desc"><?php echo sprintf(__('如果您没有在站点绑定我们的微信登录，关注之后将会自动绑定微信登录，您可以使用微信扫码登录网站。','b2'),'<span class="red">','</span>'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php }
                //付费
                if(in_array(3,$verify)){ ?>
                    <h2><?php echo __('支付费用','b2'); ?></h2>
                    <div class="verify-shiming">
                        <div class="verify-money">
                            <div class="verify-money-mumber">
                                <div><p><?php echo B2_MONEY_SYMBOL.b2_get_option('verify_main','verify_money'); ?></p></div>
                                <button @click="pay('<?php echo b2_get_option('verify_main','verify_money'); ?>')" v-if="!data.money || data.money == '0'"><?php echo __('支付','b2'); ?></button>
                                <p v-else><span class="green"><?php echo __('已支付','b2'); ?></span></p>
                            </div>
                            <div class="verify-money-desc"><p class="desc"><?php echo b2_get_option('verify_main','verify_money_text'); ?></p></div>
                        </div>
                    </div>
                <?php } ?>
                <div class="verify-submit">
                    <button @click="submitVerify()" v-if="step == 2"><?php echo __('提交申请','b2'); ?></button>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();