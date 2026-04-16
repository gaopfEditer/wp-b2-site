<?php
/**
 * 圈子用户管理
 */
get_header();
$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
?>
<div class="b2-single-content wrapper">
    <div id="circle-users" class="content-area wrapper all-circles" ref="allUsers">
        <main id="main" class="site-main b2-radius box">
            <div class="gujia all-circle-users" v-if="list === ''">
                <ul>
                    <?php for ($_i=0; $_i < 3; $_i++) { ?>
                        <li>
                            <ul class="all-circles-item-list">
                                <?php for ($__i=0; $__i < 3; $__i++) { ?>
                                    <li>
                                        <div>
                                            <div class=""></div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="all-circle-users" v-else v-cloak>
                <ul class="all-circles-item-list">
                    <li v-for="item in list.list">
                        <div :class="item.role">
                            <div>
                                <div class="circle-users-avatar">
                                    <img :src="item.user_data.avatar" class="avatar"/>
                                </div>
                                <div class="circle-users-info">
                                    <h2><a :href="item.user_data.link" target="_blank"><span v-text="item.user_data.name"></span><b v-if="item.is_circle_admin" class="circle-admin-mark"><?php echo $circle_owner_name; ?></b></a></h2>
                                    <p><span class="author-vip" v-html="item.user_data.lv.vip.icon" v-if="item.user_data.lv.vip.lv"></span><span class="author-lv" v-html="item.user_data.lv.lv.icon"></span></p>
                                    <div class="circle-user-action">
                                        <button class="empty" @click="followAc(item.id)" v-if="follow[item.id]"><?php echo __('已关注','b2'); ?></button>
                                        <button class="empty" @click="followAc(item.id)" v-else><?php echo __('关注','b2'); ?></button>
                                        <button class="empty" @click="dmsg(item.id)"><?php echo __('私信','b2'); ?></button>
                                        <button class="empty" v-if="item.role == 'pending' && canEdit" @click="checkUser(item.id)"><?php echo __('审核','b2'); ?></button>
                                        <button class="empty" v-if="canEdit" @click="remove(item.id)"><?php echo __('踢出','b2'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <p class="circle-users-date"><span v-if="item.role == 'pending'"><?php echo __('申请时间：','b2'); ?></span><span v-else><?php echo __('加入时间：','b2'); ?></span><span v-text="item.date"></span></p>
                        </div>
                    </li>
                </ul>
            </div>
            <pagenav-new ref="circleUsers" type="p" :paged="opt.paged" :pages="opt.pages" :opt="opt" api="getCircleUserList" @return="getMoreUserListData" v-if="opt.pages > 1"></pagenav-new>
        </main>
    </div>
</div>
<?php
get_footer();