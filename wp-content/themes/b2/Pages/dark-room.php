<?php
/**
 * 小黑屋
 */
get_header();
?>
<div class="b2-single-content wrapper">
    <div id="dark-room" class="content-area wrapper" ref="darkRoom">
        <main id="main" class="site-main b2-radius box">
            <div class="dark-room-header">
                <img src="<?php echo B2_THEME_URI.'/Assets/fontend/images/dark-room.jpg'; ?>">
                <div class="dark-title">
                    <h1><?php echo __('小黑屋','b2'); ?></h1>
                    <p><?php echo __('如果在这里，看到了你的名字，说明你已经被关进小黑屋了！','b2'); ?></p>
                </div>
            </div>
            <div class="dark-room-bar">
                <div class="dark-bar-l">
                    <template v-if="!type">
                        <?php echo sprintf(__('目前有%s个人%s在小黑屋里面壁思过','b2'),'<b>{{data.count}}','</b>'); ?>
                    </template>
                    <template v-else-if="type == 'ls'">
                        <?php echo sprintf(__('目前有%s个人%s在临时禁闭','b2'),'<b>{{data.count}}','</b>'); ?>
                    </template>
                    <template v-else>
                        <?php echo sprintf(__('目前有%s个人%s被永久关禁闭','b2'),'<b>{{data.count}}','</b>'); ?>
                    </template>
                </div>
                <div class="dark-bar-r">
                    <a href="javascript:void(0)" @click="getDarkRoomUsers()" :class="type === undefined ? 'b2-color' : ''"><?php echo __('所有禁闭','b2'); ?></a>
                    <a href="javascript:void(0)" @click="getDarkRoomUsers('ls')" :class="type === 'ls' ? 'b2-color' : ''"><?php echo __('临时禁闭','b2'); ?></a>
                    <a href="javascript:void(0)" @click="getDarkRoomUsers('yy')" :class="type === 'yy' ? 'b2-color' : ''"><?php echo __('永久禁闭','b2'); ?></a>
                </div>
            </div>
            <div class="dark-room-list">
                <div class="button empty b2-loading empty-page text" v-if="data == ''"></div>
                <ul v-else-if="data.data.length > 0" v-cloak>
                    <li v-for="item in data.data">
                        <div class="dark-user">
                            <div>
                                <img :src="item.user.avatar" class="avatar" /><a :href="item.user.link" v-text="item.user.name" class="b2-color" target="_blank"></a>
                            </div>
                        </div>
                        <div class="dark-why">
                            <div class="dark-user-desc">
                                <span><?php echo sprintf(__('在 %s 被关小黑屋，','b2'),'<span v-html="item.start_date"></span>'); ?></span>
                                <template v-if="item.days == 0">
                                    <?php echo __('永久禁闭，不会释放！','b2'); ?>
                                </template>
                                <template v-else>
                                    <?php echo sprintf(__('将在 %s 释放！','b2'),'<span v-html="item.end_date"></span>'); ?>
                                </template>
                            </div>
                            <p><span class="red"><?php echo __('禁闭原因：','b2'); ?></span>{{item.why}}</p>
                        </div>
                    </li>
                </ul>
                <div v-else v-cloak>
                    <?php echo B2_EMPTY;?>
                </div>
                <div class="b2-pd" v-if="pages > 1" v-cloak>
                    <pagenav-new type="p" :paged="paged" :pages="pages" :opt="opt" api="getDarkRoomUsers" @return="getMoreRoomUsers"></pagenav-new>
                </div>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();