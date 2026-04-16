<?php
/**
 * 财富页面
 */
get_header();
$user_id = isset($_GET['u']) ? (int)$_GET['u'] : 0;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$type = get_query_var('b2_gold_type') ? get_query_var('b2_gold_type') : 'credit';

$tx_allow = b2_get_option('normal_gold','gold_tx');
$tx_gold_money = (float)b2_get_option('normal_gold','gold_money');
$tx_gold_tc = (float)b2_get_option('normal_gold','gold_tc');
$circle_name = b2_get_option('normal_custom','custom_circle_name');
$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
?>
<div class="b2-single-content wrapper">
    <div id="gold" class="content-area gold-page wrapper">
		<main id="main" class="site-main" ref="goldData" data-user="<?php echo $user_id; ?>" data-paged="<?php echo $paged; ?>" data-type="<?php echo $type; ?>" data-url="<?php echo b2_get_custom_page_url('gold'); ?>">
            <div class="custom-page-title box b2-radius b2-pd mg-b">
                <div class="gold-header">
                    <div class="gold-header-title">
                        <?php echo __('我的财富','b2'); ?>
                    </div>
                    <div class="gold-more">
                        <a href="<?php echo b2_get_custom_page_url('gold-top'); ?>" target="_blank"><?php echo __('财富排行 ❯','b2'); ?></a>
                    </div>
                </div>
                <div class="gold-info mg-t">
                    <div class="custom-page-row gold-row b2-radius">
                        <div><?php echo B2_MONEY_NAME; ?><span class="user-money" v-cloak v-if="data.money || data.money == 0"><?php echo B2_MONEY_SYMBOL; ?><b v-text="data.money"></b></span></div>
                        <div>
                            <?php if($tx_allow) {?><button class="empty" @click="close"><?php echo __('提现','b2'); ?></button><?php } ?>
                            <button @click="pay()"><?php echo __('充值','b2'); ?></button>
                        </div>
                    </div>
                    <div class="custom-page-row gold-row b2-radius">
                        <div><span><?php echo __('积分','b2'); ?></span><span class="user-credit" v-cloak v-if="data.credit || data.credit == 0"><?php echo b2_get_icon('b2-coin-line'); ?><b v-text="data.credit"></b></span></div>
                        <div>
                            <span><button @click="buy()"><?php echo __('积分购买','b2'); ?></button></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="custom-page-content box b2-radius">
                <div class="custom-page-row">
                    <div class="gold-page-table">
                        <div :class="['gold-credit gold-table',{'picked':opt.gold_type === 0}]" @click="change(0)">
                            <?php echo __('积分记录','b2'); ?>
                        </div>
                        <div :class="['gold-money gold-table',{'picked':opt.gold_type === 1}]" @click="change(1)">
                            <?php echo sprintf(__('%s记录','b2'),B2_MONEY_NAME); ?>
                        </div>
                    </div>
                    <div class="button empty b2-loading empty-page text gold-bor" v-if="msg === ''"></div>
                    <div class="gold-bor" v-else-if="msg.length == 0" v-cloak>
                        <?php echo B2_EMPTY; ?>
                    </div>
                    <div class="gold-page-list" v-else v-cloak>
                        <div class="gold-header" style="color:#8590A6">
                            <div class="gold-list-row-1">
                                <?php echo __('时间','b2'); ?>
                            </div>
                            <div class="gold-list-row-2">
                                <?php echo __('类型','b2'); ?>
                            </div>
                            <div class="gold-list-row-3">
                                <?php echo __('数额','b2'); ?>
                            </div>
                            <div class="gold-list-row-4">
                                <?php echo __('总额','b2'); ?>
                            </div>
                            <div class="gold-list-row-5">
                                <?php echo __('描述','b2'); ?>
                            </div>
                        </div>
                        <ul>
                            <template v-for="item in msg">
                                <?php do_action('b2_gold_list_html');?>
                                <!-- 注册提示 -->
                                <li>
                                    <div class="gold-list-row-1">
                                    <span v-html="item.date"></span>
                                    </div>
                                    <div class="gold-list-row-2" v-text="item.type_text"></div>
                                    <div class="gold-list-row-3">
                                        <span v-text="item.no" :class="item.no > 0 ? 'green' : 'red'"></span>
                                    </div>
                                    <div class="gold-list-row-4">
                                        <span v-text="item.total"></span>
                                    </div>
                                    <div class="gold-list-row-5" v-html="msgContent(item)"></div>
                                </li>
                           
                            </template>
                        </ul>
                    </div>
                    <pagenav-new ref="goldNav" :pages="opt.pages" type="p" :opt="opt" :api="api" rote="true" @return="get"></pagenav-new>

                    <!-- <page-nav ref="goldNav" paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="opt" :api="api" :url="url" title="<?php echo __('财富管理','b2'); ?>" @return="get"></page-nav> -->
                </div>
            </div>
            <div :class="['modal','ds-box gold-box',{'show-modal':show}]" v-cloak>
                <div class="modal-content b2-radius">
                    <div class="pay-box-title">
                        <div class="pay-box-left ds-pay-title">
                            <?php echo __('提现','b2'); ?>
                        </div>
                        <div class="pay-box-right">
                            <span class="pay-close" @click="close()">×</span>
                        </div>
                    </div>
                    <div class="pay-content" v-if="!success">
                        <p class="tx-title"><?php echo sprintf(__('提现时本站会扣除%s的手续费','b2'),($tx_gold_tc*100).'%'); ?></p>
                        <p class="tx-ye"><span><?php echo sprintf(__('当前%s%s','b2'),B2_MONEY_NAME,B2_MONEY_SYMBOL.'<b v-text="data.money"></b>'); ?></span></p>
                        <input type="text" placeholder="请输入提现金额" onkeypress="validate(event)" v-model="money">
                        <p class="tx-desc"><?php echo sprintf(__('最小提现金额%s','b2'),B2_MONEY_SYMBOL.$tx_gold_money); ?></p>
                        <p class="tx-submit"><button @click="tx" :disabled="locked" :class="locked ? 'b2-loading' : ''"><?php echo __('提交申请','b2'); ?></button></p>
                    </div>
                    <div class="pay-box-content" v-else>
                        <div class="pay-check">
                            <div class="green"><?php echo b2_get_icon('b2-check-double-line'); ?></div>
                            <h2>....<?php echo __('申请成功','b2'); ?>....</h2>
                            <p><?php echo __('请确保前端个人中心已经上传了收款码，否则不能到账！','b2'); ?></p>
                            <div class="pay-check-button"><button @click="refresh()"><?php echo __('确定','b2'); ?></button></div>
                        </div>
                    <div>
                </div>
            </div>
		</main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();