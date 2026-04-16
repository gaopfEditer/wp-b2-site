<?php
/**
 * 消息页面
 */
get_header();
?>
<div class="b2-single-content wrapper">
    <div id="message-page" class="content-area message-page">
        <main class="site-main box b2-radius">
            <div class="dmsg-header">
                <h2>
                    <?php echo __('消息','b2'); ?>
                </h2>
                <div class="message-header-right">
                    <?php echo sprintf(__('您有%s条新消息','b2'),'<span v-text="data.unread"></span>'); ?>
                </div>
            </div>
            <div class="message-list">
                <div class="button empty b2-loading empty-page text" v-if="data === ''"></div>
                <div v-else-if="data.data.length == 0" v-cloak>
                    <?php echo B2_EMPTY; ?>
                </div>
                <ul v-else>
                    <template v-for="(item,i) in data.data" :key="i">
                        <li class="message-li">
                            <div class="msg-date" v-text="item.date.day" v-if="i == 0 || item.date.day != data.data[i-1].date.day"></div>
                            <div class="b2flex">
                                <div class="message-icon b2-color">
                                    <div v-if="item.from.length == 1 && item.from[0]['id'] == 0">
                                        <?php echo b2_get_icon('b2-volume-up-line'); ?>
                                    </div>
                                    <div v-else>
                                        <img :src="item.from[0].avatar" class="avatar"/>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <h2><span v-text="item.type_text"></span><span class="b2dot">·</span><span class="type-text" v-text="item.date.time"></span><span class="new" v-if="item.read == 0">NEW</span></h2>
                                    <p v-html="msg(item)"></p>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
                <pagenav-new ref="msgNav" :pages="opt['pages']" type="p" :opt="opt" :api="api" rote="true" @return="get"></pagenav-new>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();