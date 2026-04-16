<?php
use B2\Modules\Common\User;

$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$user_id){
    wp_redirect(B2_HOME_URI.'/404');
    exit;
} 

$user_data = get_userdata($user_id);
if(!$user_data){
    wp_redirect(B2_HOME_URI.'/404');
    exit;
} 

/**
 * 圈子用户中心
 */
get_header();


?>
<div class="b2-single-content wrapper circle-people">
    <div id="circle-people" class="content-area wrapper">
        <main id="main" class="site-main b2-radius ">
            <div class="c-p-top" data-people="<?php echo $user_id; ?>">
                <div class="c-p-top-l">
                    <?php echo b2_get_img(array('src'=>get_avatar_url($user_id,array('size'=>160)),'class'=>array('people-avatar'))); ?>
                    <h1><?php echo $user_data->display_name; ?><p><?php echo sprintf(__('%s前往个人中心%s','b2'),'<a href="'.get_author_posts_url($user_id).'" target="_blank">','</a>'); ?></p></h1>
                </div>
                <div class="c-p-top-right" v-if="data != '' && !data.self" v-cloak>
                    <div class="user-follow" v-cloak>
                        <button class="" v-if="data.followed == false" @click="followingAc()"><?php echo b2_get_icon('b2-add-line').'<span>'.__('关注Ta','b2').'</span>'; ?></button>
                        <button class="author-has-follow" v-else @click="followingAc()"><?php echo __('取消关注','b2'); ?></button>
                        <button class="empty" @click="dmsg()"><?php echo b2_get_icon('b2-mail-send-line').'<span>'.__('发私信','b2').'</span>'; ?></button>
                    </div>
                </div>
            </div>
            <?php get_template_part( 'TempParts/circle/circle-topic-list');?>
        </main>
    </div>
</div>
<?php
get_footer();