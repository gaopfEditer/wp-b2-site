<?php
use B2\Modules\Common\User;
/**
 * 私信页面
 */
get_header();

$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$to = get_query_var('b2_to') ? get_query_var('b2_to') : false;
?>
<div class="b2-single-content wrapper">
<?php
if(!$to){
?>
<div id="primary" class="content-area dmsg-page" ref="dmsgPage" data-paged="<?php echo $paged; ?>" data-url="<?php echo b2_get_custom_page_url('directmessage'); ?>">
    <main id="main" class="site-main box b2-radius">
        <div class="dmsg-header">
            <h2><?php echo __('往来私信','b2'); ?></h2>
            <button class="empty" @click="showDmsgBox()"><?php echo __('发私信','b2'); ?></button>
        </div>
        <div class="button empty b2-loading empty-page text" v-if="!list && locked == false"></div>
        <div class="dmsg-list" v-else-if="list.length > 0 && locked == false" v-cloak>
            <ul>
                <li v-for="(item,index) in list" :id="'dmsg-'+item.id" @click="jump(item.type == 'from' ? item.to.id : item.from.id)">
                    <img :src="item.type == 'from' ? item.to.avatar : item.from.avatar" class="b2-radius avatar"/>
                    <div class="dmsg-row">
                        <div class="dmsg-row-top">
                            <a :href="item.type == 'from' ? item.to.link : item.from.link" v-text="item.type == 'from' ? item.to.name : item.from.name"></a>
                            <span v-html="item.date"></span>
                        </div>
                        <div class="dmsg-row-bottom">
                            <div class="dmsg-content b2-radius" v-html="item.content"></div>
                            <!-- <a href="javascript:void(0)" @click="deleteDmsg(item.id)"><?php echo __('删除','b2'); ?></a> -->
                            <div class="dmsg-tools"><span class="unread" v-if="item.status == 0 && item.type !== 'from'" v-cloak><?php echo __('有未读消息','b2'); ?></span></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="" v-cloak v-else-if="locked == false">
            <?php echo B2_EMPTY; ?>
        </div>
        <page-nav paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="opt" :api="api" url="<?php echo b2_get_custom_page_url('directmessage'); ?>" title="<?php echo __('私信','b2'); ?>" @return="get"></page-nav>
    </main>
</div><?php
}else{
    $user_data = User::get_user_public_data($to);
?>
<div id="primary" class="content-area dmsg-page-to" ref="mydmsg" data-id="<?php echo $to; ?>" data-paged="<?php echo $paged; ?>">
    <main id="main" class="site-main box b2-radius">
        <div class="dmsg-header to">
            <h2><?php echo __('您与','b2').'<a href="'.$user_data['link'].'">'.$user_data['name'].'</a>'.__('共有','b2'); ?><span v-text="count"></span><?php echo __('条对话','b2'); ?></h2>
            <a href="<?php echo b2_get_custom_page_url('directmessage'); ?>"><?php echo __('返回私信列表','b2'); ?></a>
        </div>
        <div class="button empty b2-loading empty-page text" v-if="!list && locked == false"></div>
        <div class="dmsg-to-box" v-else-if="list.length > 0 && locked == false" v-cloak>
            <ul>
                <li v-for="(item,index) in list" :class="item.type == 'from' ? 'dmsg-self' : 'dmsg-to'">
                    <div class="my-dmsg-list" v-if="item.type == 'from'">
                        <div class="my-dmsg-info">
                            <div class="dmsg-meta"><span v-html="item.date"></span><span v-text="item.from.name"></span></div>
                            <div class="my-dmsg-content" v-html="item.content"></div>
                        </div>
                        <img class="avatar b2-radius" :src="item.from.avatar"/>
                    </div>
                    <div class="my-dmsg-list" v-else>
                        <img class="avatar b2-radius" :src="item.from.avatar" />
                        <div class="my-dmsg-info">
                            <div class="dmsg-meta"><span v-text="item.from.name"></span><span v-html="item.date"></span></div>
                            <div class="my-dmsg-content" v-html="item.content"></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="" v-cloak v-else-if="locked == false">
            <?php echo B2_EMPTY; ?>
        </div>
        <page-nav paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="opt" :api="api" url="<?php echo b2_get_custom_page_url('directmessage').'/to/'.$to; ?>" title="<?php echo __('私信','b2'); ?>" @return="get"></page-nav>
    </main>
    <div class="box dmsg-to-textarea">
        <textarea id="textarea" placeholder="<?php sprintf(__('给%s发私信','b2'),$user_data['name']); ?>" class="dmsg-textarea" v-model="content"></textarea>
        <div class="dmsg-to-textarea-button">
            <button :class="sendLocked ? 'b2-loading' : ''" :disabeld="locked" @click="send()"><?php echo __('发送','b2'); ?></button>
        </div>
    </div>
</div>
<?php
}
?>
<?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();
