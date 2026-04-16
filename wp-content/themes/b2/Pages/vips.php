<?php
/**
 * vip页面
 */
get_header();
?>
<div id="vips" v-cloak>
    <div class="vip-top">
        <div class="content-area wrapper">
            <?php do_action('b2_vip_page_top'); ?>
            <!-- <h1><?php echo B2\Modules\Templates\Header::logo();?></h1>
            <p><?php echo B2_BLOG_DESC;?></p> -->
            <div class="vip-count">
                <ul>
                    <li v-for="(item,index) in data.count" v-if="data.data" v-cloak>
                        <div class="vip-in b2-radius box">
                            <span v-text="item.name" :style="'color:'+data.data[index].color"></span>
                            <span class="vip-in-number"><b><?php echo __('已加入','b2'); ?></b>{{parseInt(item.count)}}<b><?php echo __('人','b2'); ?></b></span>
                            <button class="empty" @click="join(item.lv,item.name,data.data[index].price)"><span v-if="data.data[index].allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></button>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="vip-current" v-if="data.user">
                <span v-if="data.user == 'guest'">
                    <?php echo __('您当前为游客，请登录以后操作','b2'); ?>
                </span>
                <span v-else-if="!data.user.vip.lv">
                    <?php echo __('您当前为普通用户，推荐升级成高级会员！','b2'); ?>
                </span>
                <div v-else>
                    <?php echo sprintf(__('您当前为%s用户，有效期至：%s','b2'),'<span v-html="data.user.vip.icon"></span>','<span v-text="data.user.time === \'long\' ? \''.__('终身','b2').'\' : data.user.time"></span>'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="content-area vip-page wrapper">
        <main id="main" class="site-main b2-radius" ref="vips">
            <div class="vip-list">
                <div class="vip-item" v-for="(item,index) in data.data">
                    <div class="vip-list-in box b2-radius">
                        <h2 v-text="item.name" :style="'color:'+item.color"></h2>
                        <div class="vip-price">
                            <div class="vip-price-money"><?php echo B2_MONEY_SYMBOL; ?><span v-text="item.price"></span></div>
                            <div class="vip-price-day">/<span v-if="item.time > 0">{{item.time}}<?php echo __('天','b2'); ?></span><span v-else><?php echo __('终身','b2'); ?></span></div>
                        </div>
                        <div class="vip-row">
                            <ul>
                                <li :class="['vip-allow',{'allow':item.allow_read === '1'}]">
                                    <div><span><?php echo __('查看所有隐藏内容','b2'); ?></span></div>
                                    <div>
                                        <span v-if="item.allow_read === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                <li :class="['vip-allow',{'allow':item.allow_download === '1'}]">
                                    <div>
                                        <template v-if="item.allow_download === '1'">
                                            <?php echo __('免费下载所有资源','b2'); ?>
                                            <span v-if="item.allow_download_count < 9999">（{{item.allow_download_count}}<?php echo __('次/天','b2'); ?>）</span>
                                            <span v-else>（<?php echo __('无限制次数','b2'); ?>）</span>
                                        </template>
                                        <template v-else>
                                            <?php echo __('免费下载所有资源','b2'); ?>
                                        </template>
                                    </div>
                                    <div>
                                        <span v-if="item.allow_download === '1'"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                <li :class="['vip-allow',{'allow':item.allow_videos === '1'}]">
                                    <template v-if="item.allow_videos === '1'">
                                        <div><?php echo __('免费查看所有付费视频','b2'); ?></div>
                                        <div><?php echo b2_get_icon('b2-check-line'); ?></div>
                                    </template>
                                    <template v-else>
                                        <div><?php echo __('免费查看所有付费视频','b2'); ?></div>
                                        <div><?php echo b2_get_icon('b2-close-line'); ?></div>
                                    </template>
                                </li>
                                <li v-for="role in item.user_role" :class="['vip-allow',{'allow':role.allow}]">
                                    <div><span>{{role.name}}</span></div>
                                    <div>
                                        <span v-if="role.allow"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                <li class="vip-allow allow">
                                    <div><?php echo __('享受专属打折商品','b2'); ?></div>
                                    <div><?php echo b2_get_icon('b2-check-line'); ?></div>
                                </li>
                                <li :class="['vip-allow',{'allow':m.role == 1}]" v-if="item.more.length > 0" v-for="(m,_i) in item.more" :key="_i">
                                    <div><span v-text="m.text"></span></div>
                                    <div>
                                        <span v-if="m.role == 1"><?php echo b2_get_icon('b2-check-line'); ?></span>
                                        <span v-else><?php echo b2_get_icon('b2-close-line'); ?></span>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                        <!-- :disabled="data.user.time === 'long' && item.time == 0" -->
                        <div class="vip-buy"><button @click="join('vip'+index,item.name,item.price)" ><span v-if="item.allow_buy === false"><?php echo __('续签会员','b2'); ?></span><span v-else><?php echo __('立刻加入','b2'); ?></span></button></div>
                    </div>
                </div>
            </div>
            <div class="vip-footer">
                <h2><?php echo __('常见问题','b2'); ?></h2>
                <p><?php echo __('FAQ','b2'); ?></p>
                <div class="vip-faq box">
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('开通VIP的好处？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('VIP会员根据等级在相应的有效期内享有本站所有资源免费下载资源的权力，免费查看隐藏内容的权力，免费查看视频的权力，同时本站商品还会获得打折价格，并且拥有其他特殊的权力。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('VIP资源需要单独购买吗？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('不同的VIP拥有不同的权限，通常VIP会员拥有免费资格享受各种资源的权力，但不排除某些特殊情况。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('VIP会员是否无限次下载资源？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('在遵守VIP会员协议前提下，VIP会员在会员有效期内可以下载所有免费和VIP资源。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('是否可以与他人分享VIP会员账号？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('一个VIP账号仅限一个人使用，禁止与他人分享账号，一经发现做永久封号处理。','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('是否可以申请退款？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('VIP会员属于虚拟服务，付款后不能够申请退款。如付款前有任何疑问，联系站长处理','b2'); ?></p>
                    </div>
                    <div class="vip-faq-list" @click.stop="showAc($event)">
                        <h2><?php echo __('遇到付款失败，付款后没有生效怎么办？','b2'); ?></h2>
                        <p class="b2-hidden"><?php echo __('理论上来说正常付款后不会出现此类问题，但是也会有部分用户因为网络等原因导致在付款的过程中会有一些小插曲，如果出现类似问题，大可不必惊慌，本站所有支付都会生成订单，不管成功还是失败，所以如果真正遇到网络问题导致付款失败您又不知道是否成功，请查看自己的个人中心的订单管理，截图联系管理员处理。','b2'); ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();