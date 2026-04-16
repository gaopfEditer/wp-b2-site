<?php
/**
 * 所有圈子
 */
get_header();
$circle_name = b2_get_option('normal_custom','custom_circle_name');
$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
$circle_member = b2_get_option('normal_custom','custom_circle_member_name');
?>
<div class="b2-single-content wrapper">
    <div id="all-circles" class="content-area all-circles" ref="allCircle">
        <main id="main" class="site-main b2-radius box">
            <div class="gujia all-circles-list" v-if="list === ''">
                <div class="all-circle-tags">
                    <ul>
                    <?php for ($i=0; $i < 7; $i++) { ?>
                        <li>
                            <span></span>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
                <div class="all-circles-item">
                    <ul>
                        <?php for ($_i=0; $_i < 4; $_i++) { ?>
                            <li>
                                <div class="all-circles-tag-name"></div>
                                <ul class="all-circles-item-list">
                                    <?php for ($__i=0; $__i < 6; $__i++) { ?>
                                        <li>
                                            <div>
                                                <div class=""> </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="all-circles-list" v-else v-cloak>
                <div class="all-circle-tags">
                    <ul>
                        <li v-for="(tag,index) in list.tags">
                            <span v-text="tag" @click="go(index)"></span>
                        </li>
                    </ul>
                </div>
                <div class="all-circles-item">
                    <ul>
                        <li v-for="(item,index) in list.list">
                            <div class="all-circles-tag-name" :id="'circle-'+index">
                                <div v-text="item.tag_name"></div>
                                <button class="text" v-if="item.list.length > 6 && count[index] == 5" v-cloak @click="open(index)"><?php echo __('展开','b2').b2_get_icon('b2-arrow-down-s-line'); ?></button>
                                <button class="text" v-else-if="item.list.length > 6" v-cloak @click="open(index)"><?php echo __('收起','b2').b2_get_icon('b2-arrow-up-s-line'); ?></button>
                            </div>
                            <ul class="all-circles-item-list">
                                <li v-for="(child,_i) in item.list" v-if="_i <= count[index]">
                                    <a :href="child.link" class="link-block" target="_blank"></a>
                                    <div class="b2-radius">
                                        <div class="">
                                            <div class="circle-child-icon">
                                                <img :src="child.icon" />
                                            </div>
                                            <div class="circle-child-info">
                                                <div class="circle-child-name-box">
                                                    <h2 v-text="child.name"></h2>
                                                    <span v-if="child.type == 'free'" :class="'circle'+child.type"><?php echo sprintf(__('免费%s','b2'),$circle_name); ?></span>
                                                    <span v-if="child.type == 'money'" :class="'circle'+child.type"><?php echo sprintf(__('付费%s','b2'),$circle_name); ?></span>
                                                    <span v-if="child.type == 'lv'" :class="'circle'+child.type"><?php echo sprintf(__('私密%s','b2'),$circle_name); ?></span>
                                                </div>
                                                <div class="circle-child-admin">
                                                    <img :src="child.admin.avatar" /><span v-text="child.admin.name"></span><?php echo sprintf(__('（%s）','b2'),$circle_owner_name); ?>
                                                </div>
                                                <div class="circle-child-meta">
                                                    <span><?php echo sprintf(__('%s个%s','b2'),'<b v-text="child.user_count"></b>',$circle_member); ?></span>
                                                    <i></i>
                                                    <span><?php echo sprintf(__('%s个话题','b2'),'<b v-text="child.topic_count"></b>'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();