<?php
/**
 * 推广页面
 */
get_header();
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;
$circle_name = b2_get_option('normal_custom','custom_circle_name');
$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
?>
<div class="b2-single-content wrapper">
    <div id="distribution-page" class="content-area message-page" ref="distribution" data-paged="<?php echo $paged; ?>">
        <main class="site-main box b2-radius">
            <div class="dmsg-header">
                <h2 class="distribution-title">
                    <?php echo __('推广中心','b2'); ?><span v-if="money == '****'" v-cloak><?php echo __('您不是我们的合作伙伴，无法使用推广功能！','b2'); ?></span>
                </h2>
            </div>
            <div class="distribution-total">
                <div class="distributon-row b2-pd">
                    <h2><?php echo __('累积收益','b2'); ?></h2>
                    <span class="number"><?php echo B2_MONEY_SYMBOL; ?><b v-text="money">0</b></span>
                    <p><?php echo __('累积收益 = 自己的推广收益 x 收益比例 + 合作伙伴的推广收益 x 收益比例。','b2'); ?></p>
                </div>
                <div class="distributon-row b2-pd">
                    <h2><?php echo __('我的推广码','b2'); ?></h2>
                    <input type="text" disabled="true" v-model="ref"></span>
                    <p><?php echo sprintf(__('当您在本站发布文章、%s、%s等内容的时候，会自动生成分享连接（或者二维码），这个连接（二维码）自带您的推广码，可以分享给别人进行推广。您也可以将在本站的任何网址后面加 %s 分享给其他人，那么他通过这个连接注册以后就自动成为您的合作伙伴。','b2'),$newsflashes_name,$circle_name,'<code v-text="\'?ref=\'+ref"></code>'); ?></p>
                </div>
                <div class="distributon-row b2-pd">
                    <h2><?php echo __('生成推广连接','b2'); ?></h2>
                    <div class="distribution-build">
                        <p><input type="text" class="distribution-build-input" placeholder="<?php echo __('粘贴推广地址到此处','b2'); ?>" v-model="url">
                        <button @click="money == '****' ? '' : build()" :disabled="money == '****' ? true : false"><?php echo __('生成','b2'); ?></button></p>
                        <p><?php echo __('复制本站连接到此处，生成推广连接和二维码。','b2'); ?></p>
                    </div>
                </div>
            </div>
            <div class="distribution-info">
                <div class="my-distribution-orders b2-pd distributon-row">
                    <h2><?php echo __('推广订单列表','b2'); ?></h2>
                    <ul>
                        <li>
                            <div class="distribution-order-name">
                                <?php echo __('伙伴','b2'); ?>
                            </div>
                            <div class="distribution-order-products">
                                <?php echo __('商品名称','b2'); ?>
                            </div>
                            <div class="distribution-order-lv">
                                <?php echo __('伙伴层级','b2'); ?>
                            </div>
                            <div class="distribution-order-money">
                                <?php echo __('商品总价','b2'); ?>
                            </div>
                            <div class="distribution-order-ratio">
                                <?php echo __('收益比','b2'); ?>
                            </div>
                            <div class="distribution-order-my">
                                <?php echo __('收益','b2'); ?>
                            </div>
                        </li>
                        <div class="gujia" ref="gujia">
                            <?php for ($i=0; $i < 5; $i++) { ?>
                                <li>
                                    <div class="distribution-order-name">
                                        <span></span>
                                    </div>
                                    <div class="distribution-order-products">
                                        <span></span>
                                    </div>
                                    <div class="distribution-order-lv">
                                        <span></span>
                                    </div>
                                    <div class="distribution-order-money">
                                        <span></span>
                                    </div>
                                    <div class="distribution-order-ratio">
                                        <span></span>
                                    </div>
                                    <div class="distribution-order-my">
                                        <span></span>
                                    </div>
                                </li>
                            <?php } ?>
                        </div>
                        <li v-for="item in orderList" v-if="orderList !== ''" v-cloak>
                            <div class="distribution-order-name" v-if="item.from">
                                <img :src="item.from.avatar"><a :href="item.from.link" target="_blank"><span v-text="item.from.name"></span></a>
                            </div>
                            <div class="distribution-order-name" v-else>
                                <?php echo __('未记录','b2'); ?>
                            </div>
                            <div class="distribution-order-products">
                                <span v-if="item.post">
                                <a :href="item.post.link" target="_blank"><span v-text="item.post.title"></span></a>
                                </span>
                                <span v-else v-text="item.msg.replace('${from}','')"></span>
                            </div>
                            <div class="distribution-order-lv">
                                <span v-if="item.type === 'post_distribution_lv1'">
                                    <?php echo __('一级伙伴','b2'); ?>
                                </span>
                                <span v-if="item.type === 'post_distribution_lv2'">
                                    <?php echo __('二级伙伴','b2'); ?>
                                </span>
                                <span v-if="item.type === 'post_distribution_lv3'">
                                    <?php echo __('三级伙伴','b2'); ?>
                                </span>
                            </div>
                            <div class="distribution-order-money">
                                <span v-text="'<?php echo B2_MONEY_SYMBOL; ?>'+item.value.money"></span>
                            </div>
                            <div class="distribution-order-ratio">
                                <span v-text="ratio(item.value.ratio)+'%'"></span>
                            </div>
                            <div class="distribution-order-my">
                                <span v-text="'<?php echo B2_MONEY_SYMBOL; ?>'+item.no"></span>
                            </div>
                        </li>
                        <div class="partner-none" v-if="orderList !== '' && orderList.length == 0" v-cloak>
                            <?php echo __('没有推广订单','b2'); ?>
                        </div>
                    </ul>
                    <pagenav-new ref="goldNav" :pages="opt.pages" type="p" :opt="opt" :api="api" :rote="false" @return="get"></pagenav-new>
                    <!-- <page-nav ref="goldNav" paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="opt" :api="api" url="<?php echo b2_get_custom_page_url('distribution'); ?>" title="<?php echo __('推广中心','b2'); ?>" @return="get"></page-nav> -->
                </div>
                <div class="b2-pd distributon-row my-distribution-partner">
                    <h2><?php echo __('我的合作伙伴','b2'); ?></h2>
                    <ul class="gujia" ref="partnergujia">
                        <?php
                            for ($i=0; $i < 6; $i++) { 
                        ?>
                        <li>
                            <div class="gujia-avatar"></div>
                            <div class="gujia-name">
                                <div></div>
                                <div></div>
                            </div>
                        </li>
                        <?php
                            }
                        ?>
                    </ul>
                    <div class="partner-none" v-if="partnerList !== '' && partnerList.length == 0" v-cloak>
                        <?php echo __('没有合作伙伴','b2'); ?>
                    </div>
                    <ul v-cloak>
                        <li v-if="partnerList !== ''" v-cloak v-for="item in partnerList">
                            <a :href="item.link" target="_blank"><img :src="item.avatar" /></a>
                            <a :href="item.link" class="partner-user" target="_blank">
                                <span v-text="item.name"></span>
                                <span v-if="item.partner_lv === 'lv1'" class="partner-lv">
                                    <?php echo __('一级伙伴','b2'); ?>
                                </span>
                                <span v-else-if="item.partner_lv === 'lv2'" class="partner-lv">
                                    <?php echo __('二级伙伴','b2'); ?>
                                </span>
                                <span v-else-if="item.partner_lv === 'lv3'" class="partner-lv">
                                    <?php echo __('三级伙伴','b2'); ?>
                                </span>
                            </a>
                        </li>
                    </ul>
                    <pagenav-new v-show="partnerList !== ''" class="mg-t" ref="pnav" :pages="popt.pages" type="p" :opt="popt" :api="papi" :rote="false" @return="pget"></pagenav-new>
                </div>
            </div>
            <div :class="['modal','distribution-form',{'show-modal':show}]" v-cloak>
                <div class="modal-content b2-radius">
                    <span class="close-button" @click="build(0)">×</span>
                    <h2><?php echo __('下载推广二维码或复制推广连接','b2'); ?></h2>
                    <div class="distribution-form-content">
                        <div class="distribution-qrcode mg-b">
                            <div>
                                <img :src="qrcode">
                                <p><a :href="qrcode" download="<?php echo __('扫码打开.png','b2'); ?>" class="button"><?php echo __('下载二维码','b2'); ?></a></p>
                            </div>
                        </div>
                        <div class="distribution-links">
                            <div>
                                <input v-model="link" readonly id="foo">
                                <p><button class="btn" data-clipboard-action="copy" data-clipboard-target="#foo"><?php echo __('复制连接','b2'); ?></button></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();