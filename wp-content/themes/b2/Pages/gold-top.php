<?php
/**
 * 财富排行
 */
get_header();
$user_id = isset($_GET['u']) ? (int)$_GET['u'] : 0;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$type = get_query_var('b2_gold_type') ? get_query_var('b2_gold_type') : 'credit';
?>
<div class="b2-single-content wrapper">
<div id="gold-top" class="content-area gold-page wrapper">
		<main id="main" class="site-main box b2-radius" ref="goldTop" data-user="<?php echo $user_id; ?>" data-paged="<?php echo $paged; ?>" data-type="<?php echo $type; ?>" data-url="<?php echo b2_get_custom_page_url('gold'); ?>">
            <div class="custom-page-title b2-pd">
                <div class="gold-header">
                    <div class="gold-header-title">
                        <?php echo __('财富排行','b2'); ?>
                    </div>
                    <div class="gold-more">
                        <a href="<?php echo b2_get_custom_page_url('gold'); ?>" target="_blank"><?php echo __('我的财富 ❯','b2'); ?></a>
                    </div>
                </div>
            </div>
            <div class="custom-page-content">
                <div class="button empty b2-loading empty-page text" v-if="data === ''"></div>
                <div class="gold-top-list" v-else-if="data != '' && Object.keys(data).length > 0" v-cloak>
                    <ul>
                        <li v-for="(item,index) in data">
                            <div class="gold-top-avatar">
                                <a :href="item.link" target="_blank"><img class="avatar b2-radius" :src="item.avatar" /></a>
                                <span v-if="item.user_title" v-html="item.verify_icon"></span>
                            </div>
                            <div class="gold-top-info">
                                <div class="gold-top-info-left">
                                    <h2><span v-text="'No.'+(index+1)"></span><a :href="item.link" target="_blank" v-text="item.name"></a><b v-if="item.user_title"><?php echo __('认证会员','b2'); ?></b></h2>
                                    <div class="gold-top-desc">
                                        <p v-if="item.user_title" v-text="item.user_title"></p>
                                        <p v-else-if="item.desc" v-text="item.desc"></p>
                                        <p v-else="item.desc" v-text="item.desc"></p>
                                    </div>
                                </div>
                                <div class="gold-top-credit"><span class="user-credit"><?php echo b2_get_icon('b2-coin-line'); ?>{{item.credit}}</span></div>
                            </div>
                        </li>
                    </ul>
                    <div class="gold-top-num"><?php echo __('前20名','b2'); ?></div>
                </div>
                <div v-else v-cloak>
                    <?php echo B2_EMPTY; ?>
                </div>
                <!-- <page-nav ref="goldNav" paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="opt" :api="api" :url="url" title="<?php echo __('财富排行','b2'); ?>" @return="get"></page-nav> -->
            </div>
		</main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();