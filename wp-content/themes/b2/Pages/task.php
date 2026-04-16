<?php
/**
 * 任务
 */
get_header();
?>
<div class="b2-single-content wrapper">
    <div id="task" class="content-area wrapper" ref="task">
        <main id="main" class="site-main b2-radius box">
            <div class="task-title" style="background-image:url(<?php echo B2_THEME_URI.'/Assets/fontend/images/task_bg.jpg';?>)">
                <?php echo __('任务中心','b2'); ?>
            </div>
            <div class="button empty b2-loading empty-page text" v-if="taskData === ''"></div>
            <div v-else-if="taskData.length == 0" v-cloak>
                <?php echo B2_EMPTY; ?>
            </div>
            <div v-else v-cloak>
                <div class="task-box">
                    <div class="task-day-title">
                        <?php echo __('每日任务','b2'); ?>
                    </div>
                    <div class="task-day-list">
                        <ul>
                            <li v-for="(item,name,index) in taskData.task" v-if="item.credit != 0">
                                <a :href="item.url" class="link-block" v-if="item.url" @click="mission(name)"></a>
                                <div>
                                    <i :class="'b2font '+item.icon"></i><span v-text="item.name"></span><span class="task-finish">(<b v-text="item.finish"></b>/<b v-text="item.times"></b>)</span>
                                </div>
                                <div class="task-box-r">
                                    <span><?php echo __('奖励：','b2').b2_get_icon('b2-coin-line'); ?>+<b v-text="item.credit"></b></span><span v-if="item.finish != item.times" :class="'task-finish-icon-go '+(!item.url ? 'nolink' : '')">❯</span>
                                    <span v-else class="task-finish-icon"><?php echo b2_get_icon('b2-arrow-right-s-line'); ?></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="task-box" ref="userTask">
                    <div class="task-day-title">
                        <?php echo __('新手任务','b2'); ?>
                    </div>
                    <div class="task-day-list">
                        <ul>
                            <li v-for="item in taskData.task_user">
                                <a :href="item.url" class="link-block" v-if="item.url"></a>
                                <div>
                                    <i :class="'b2font '+item.icon"></i><span v-text="item.name"></span><span class="task-finish">(<b v-text="item.finish"></b>/<b v-text="item.times"></b>)</span>
                                </div>
                                <div class="task-box-r">
                                    <span><?php echo __('奖励：','b2').b2_get_icon('b2-coin-line'); ?>+<b v-text="item.credit"></b></span><span v-if="item.finish != item.times" :class="'task-finish-icon-go '+(!item.url ? 'nolink' : '')">❯</span>
                                    <span v-else class="task-finish-icon"><?php echo b2_get_icon('b2-arrow-right-s-line'); ?></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();