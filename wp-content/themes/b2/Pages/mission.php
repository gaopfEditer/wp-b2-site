<?php
/**
 * 签到排行
 */
get_header();
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$type = get_query_var('b2_mission_type') ? get_query_var('b2_mission_type') : 'today';
?>
<div class="b2-single-content wrapper page">
<div id="mission" class="content-area mission-page wrapper">
		<main id="main" class="site-main" ref="missionPage" data-paged="<?php echo $paged; ?>" data-type="<?php echo $type; ?>" data-url="<?php echo b2_get_custom_page_url('mission'); ?>">

            <div class="custom-page-title box b2-radius b2-pd mg-b">
                <div class="gold-header">
                    <div class="gold-header-title">
                        <?php echo __('签到排行','b2'); ?>
                    </div>
                </div>
                <template v-if="mission !== ''">
                    <div class="custom-page-row gold-row mg-t" v-if="mission.data.mission" style="width:auto">
                        <div><span><template v-if="mission.data.mission.credit"><?php echo b2_get_icon('b2-coin-line'); ?><b v-text="mission.data.mission.credit"></b></template><b v-else><?php echo __('今日未签到','b2'); ?></b></span></div>
                        <div>
                            <button @click="mission.mission()"><?php echo __('立刻签到','b2'); ?></button>
                        </div>
                    </div>
                    <div class="mg-t" v-if="mission.data.mission">
                        <div class="mission-always-settings">
                            <div><span><?php echo __('连续签到：','b2'); ?></span><span><b v-text="mission.data.mission.always"></b><?php echo __('天','b2'); ?></span></div>
                            <div>
                                <span><button @click="tk()" class="empty" :disabled="mission.data.mission.tk.days == 0 || mission.data.mission.tk.days == 1 ? true : false"><?php echo __('填坑','b2'); ?></button></span>
                            </div>
                        </div>
                        <div class="mission-tk">
                            <h2><?php echo __('填坑说明','b2'); ?></h2>
                            <ol>
                                <li><?php echo __('如果签到某一天中断，后面的所有签到不会计算在连续签到中','b2'); ?></li>
                                <li><?php echo __('如果连续签到有中断，您可以使用填坑功能将没有签到的天数补充完整。','b2'); ?></li>
                                <li><?php echo __('填坑需要消耗积分，计算方法是：每日签到最高限x未签到的天数x倍数，比如每天签到可随机获得50至100积分，有2两天没有签到，当前倍数为3,则最终需要支付积分100x2x3=600积分。','b2'); ?></li>
                                <li v-if="mission.data.mission.current_user">
                                    <span v-if="mission.data.mission.tk.days === 0 || mission.data.mission.tk.days === 1"><?php echo __('您目前不需要填坑。','b2'); ?></span>
                                    <span v-else>
                                        <?php echo sprintf(__('您目前已经有%s天未签到，填坑倍数为%s，填坑需要支付%s积分，您当前的积分为%s。','b2'),'{{mission.data.mission.tk.days}}','{{mission.data.mission.tk.bs}}','{{mission.data.mission.tk.credit}}','{{mission.data.mission.my_credit}}'); ?>
                                    </span>
                                </li>
                            </ol>
                        </div>
                    </div>
                    <?php do_action('b2_mission_page'); ?>
                </template>
            </div>
            <div class="custom-page-content box b2-radius">
                <div class="custom-page-row">

                    <div class="gold-page-table">
                        <div :class="['gold-credit gold-table',{'picked':opt.type === 'today'}]" @click="change('today')">
                            <?php echo __('每日签到','b2'); ?>
                        </div>
                        <div :class="['gold-money gold-table',{'picked':opt.type === 'always'}]" @click="change('always')">
                            <?php echo __('连续签到排行','b2'); ?>
                        </div>
                    </div>

                    <div class="button empty b2-loading empty-page text gold-bor" v-if="list == ''"></div>
                    <div class="gold-bor" v-else-if="list == []" v-cloak>
                        <?php echo B2_EMPTY; ?>
                    </div>
                    
                    <div class="mission-page-list" v-else v-cloak>
                        <ul class="b2-radius">
                            <li v-for="item in list">
                                <div class="mission-page-user-left">
                                    <a :href="item.user.link"><img :src="item.user.avatar" class="avatar"><span class="mission-page-user-verify" v-html="item.user.verify_icon" v-if="item.user.user_title"></span></a>
                                    <div class="mission-page-user-info">
                                        <div class="user-mission-info"><a :href="item.user.link"><span v-text="item.user.name"></span></a><span v-html="item.date"></span></div>
                                        <div class="user-mission-desc" v-if="item.user.title || item.user.desc"><span v-text="item.user.title ? item.user.title : item.user.desc"></span></div>
                                    </div>
                                </div>
                                <div class="mission-page-user-right">
                                    <span v-if="opt.type == 'today'"><?php echo b2_get_icon('b2-coin-line'); ?><b v-text="item.credit"></b></span>
                                    <span v-else v-text="'<?php echo __('连续','b2'); ?>'+item.count+'<?php echo __('天','b2'); ?>'"></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <page-nav ref="missionNav" paged="<?php echo $paged; ?>" navtype="json" :pages="opt.type == 'today' ? pages.today : pages.always" type="p" :box="selecter" :opt="opt" :api="api" :url="url" title="<?php echo __('签到管理','b2'); ?>" @return="get"></page-nav>
                </div>
            </div>
		</main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();