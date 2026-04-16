<?php
use B2\Modules\Common\User;
/**
 * 圈子用户中心
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

?>
<div class="b2-single-content wrapper infomation-people b2-infomation" data-people="<?php echo $user_id; ?>" ref="b2infomation" data-paged="<?php echo $paged; ?>">
    <div class="content-area wrapper">
        <main id="main">
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
            <div class="b2-radius box infomation-list">
                <?php get_template_part( 'TempParts/infomation/archive'); ?>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();