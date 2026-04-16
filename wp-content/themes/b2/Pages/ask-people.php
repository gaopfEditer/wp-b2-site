<?php
use B2\Modules\Common\Ask;
/**
 * 问答用户中心
 */
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$user_id){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
} 

$user_data = get_userdata($user_id);
if(!$user_data){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
} 

get_header();

$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

$count = b2_get_option('ask_main','ask_page_count');

?>
<div class="b2-single-content wrapper ask-people b2-infomation">
    <div class="content-area wrapper">
        <main id="main" class="ask-archive" data-people="<?php echo $user_id; ?>" ref="askarchive" data-paged="<?php echo $paged; ?>" data-count="<?php echo $count; ?>">
            <div class="i-p-top">
                <div class="i-p-top-l">
                    <?php echo b2_get_img(array('src'=>get_avatar_url($user_id,array('size'=>160)),'class'=>array('people-avatar'))); ?>
                    <h1><?php echo $user_data->display_name; ?><p><?php echo sprintf(__('%s前往个人中心%s','b2'),'<a href="'.get_author_posts_url($user_id).'" target="_blank">','</a>'); ?></p></h1>
                </div>
                <div class="i-p-top-right" v-if="authorData != '' && !authorData.self" v-cloak>
                    <div class="user-follow" v-cloak>
                        <button class="" v-if="authorData.followed == false" @click="followingAc()"><?php echo b2_get_icon('b2-add-line').'<span>'.__('关注Ta','b2').'</span>'; ?></button>
                        <button class="author-has-follow" v-else @click="followingAc()"><?php echo __('取消关注','b2'); ?></button>
                        <button class="empty" @click="dmsg()"><?php echo b2_get_icon('b2-mail-send-line').'<span>'.__('发私信','b2').'</span>'; ?></button>
                    </div>
                </div>
            </div>
            <div class="ask-people-tab">
                <span :class="type == 'ask' ? 'picked' : ''" @click="type = 'ask'"><?php echo __('我的提问','b2'); ?></span>
                <span :class="type == 'answer' ? 'picked' : ''" @click="type = 'answer'"><?php echo __('我的回答','b2'); ?></span>
            </div>
            <div class="box b2-radius" v-show="type == 'ask'">
                <?php get_template_part( 'TempParts/Ask/archive'); ?>
                <pagenav-new class="ask-list-nav" ref="infonav" navtype="post" :pages="opt['pages']" type="p" box=".ask-list-box" :opt="opt" :api="api" :rote="false" url="<?php echo b2_get_custom_page_url('ask-people'); ?>" title="<?php echo $name; ?>" @return="get"></pagenav-new>
            </div>
            <div class="box b2-radius" v-show="type == 'answer'">
                <?php get_template_part( 'TempParts/Ask/answer-archive'); ?>
                <pagenav-new class="ask-list-nav" ref="answer" navtype="post" :pages="aopt['pages']" type="p" box=".ask-list-box" :opt="aopt" :api="aapi" :rote="false" url="<?php echo b2_get_custom_page_url('ask-people'); ?>" title="<?php echo $name; ?>" @return="getAnswer"></pagenav-new>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();