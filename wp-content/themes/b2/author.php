<?php
use B2\Modules\Common\User;

$user_page = get_query_var('b2_user_page');
$user_id =  get_query_var('author');
$link = get_author_posts_url($user_id);

if(!$user_page){
    $user_page = 'index';
}

get_header();

$user_data = get_userdata($user_id);

if(!isset($user_data->display_name)){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}

$user_lv = User::get_user_lv($user_id);
$user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
$user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
$tab = b2_custom_user_arg();
$newsflashes_slug = b2_get_option('normal_custom','custom_newsflashes_link');

wp_localize_script( 'b2-author', 'b2_author',array(
    'author_id'=>$user_id
));

$title = get_user_meta($user_id,'b2_title',true);

?>
<div id="author" class="author wrapper">
    <div class="box b2-radius author-header">
        <div class="mask-wrapper" :style="'background-image:url('+cover+')'">
            <div class="user-cover-button" v-show="admin || self" v-cloak>
                <label class="empty button" for="cover-input"><?php echo b2_get_icon('b2-image-fill'); echo '<span>'.__('上传封面图片','b2').'</span>'; ?></label>
                <input id="cover-input" type="file" class="b2-hidden-always" ref="fileInput" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event,'cover')" :disabled="locked">
            </div>
        </div>
        <div class="user-panel">
            <div :style="'background-image:url('+avatar+')'" class="avatar">
                <label class="editor-avatar" for="avatar-input" v-show="admin || self" v-cloak><?php echo b2_get_icon('b2-image-fill'); echo '<span>'.__('修改我的头像','b2').'</span>'; ?></label>
                <input id="avatar-input" type="file" class="b2-hidden-always" ref="fileInput" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event,'avatar')" :disabled="locked">
            </div>
            <div class="user-panel-info">
                <div class="">
                    <h1><span id="userDisplayName"><?php echo $user_data->display_name; ?></span><span class="user-page-lv"><?php echo $user_vip; ?><?php echo $user_lv; ?></span></h1>
                    <p><?php echo $user_data->description ? str_replace(array('{{','}}'),'',wptexturize(sanitize_textarea_field(esc_attr($user_data->description)))) : __('这个人很懒，什么都没有留下！','b2'); ?></p>
                </div>
                <div class="user-panel-editor-button">
                    <div class="user-follow" v-show="!self && avatar" v-cloak>
                        <button class="" v-if="followed == false" @click="followingAc()"><?php echo b2_get_icon('b2-add-line').'<span>'.__('关注Ta','b2').'</span>'; ?></button>
                        <button class="author-has-follow" v-else @click="followingAc()"><?php echo __('取消关注','b2'); ?></button>
                        <button class="empty" @click="dmsg()"><?php echo b2_get_icon('b2-mail-send-line').'<span>'.__('发私信','b2').'</span>'; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="author-table mg-t">
        <div class="author-page-right">
            <div class="author-page-right-in box b2-radius">
                <div class="user-sidebar">
                    <div class="user-sidebar-info <?php echo $user_page === 'index' ? 'active' : ''; ?>">
                        <a href="<?php echo $link; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-user-heart-line b2-light b2-color').__('概览','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
                <div class="user-sidebar">
                    <div class="user-sidebar-info <?php echo $user_page === 'post' || $user_page === 'comments' || $user_page === $newsflashes_slug ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/post'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-quill-pen-line b2-light b2-color').__('发布的','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
               
                <div :class="'user-sidebar h0 '+(userData.admin || userData.self ? 'show' : '')" v-cloak>
                    <div class="user-sidebar-info <?php echo $user_page === 'orders' ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/orders'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-file-list-2-line b2-light b2-color').__('订单','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
               
                <div :class="'user-sidebar h0 '+(userData.admin || userData.self ? 'show' : '')" v-cloak>
                    <div class="user-sidebar-info <?php echo $user_page === 'myinv' ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/myinv'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-hand-heart-line b2-light b2-color').__('邀请码','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
                <div class="user-sidebar">
                    <div class="user-sidebar-info <?php echo $user_page === 'following' ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/following'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-heart-add-line b2-light b2-color').__('关注','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
                <div class="user-sidebar">
                    <div class="user-sidebar-info <?php echo $user_page === 'followers' ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/followers'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-hearts-line b2-light b2-color').__('粉丝','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
                <div class="user-sidebar">
                    <div class="user-sidebar-info <?php echo $user_page === 'collections' ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/collections'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-star-line b2-light b2-color').__('收藏','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
                <div :class="'user-sidebar h0 '+(userData.admin || userData.self ? 'show' : '')" v-cloak>
                    <div class="user-sidebar-info <?php echo $user_page === 'settings' ? 'active' : ''; ?>">
                        <a href="<?php echo $link.'/settings'; ?>" class="link-block"></a>
                        <p><?php echo b2_get_icon('b2-settings-3-line b2-light b2-color').__('设置','b2'); ?></p>
                        <div class="author-sidebar-down">
                            <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="author-page-left">
            <?php 
                
                $arg = array(
                    'post'=>__('文章','b2'),
                    $newsflashes_slug=>b2_get_option('normal_custom','custom_newsflashes_name'),
                    'comments'=>__('评论','b2'),
                );

                if(!b2_get_option('newsflashes_main','newsflashes_open')){
                    unset($arg[$newsflashes_slug]);
                }
            if(isset($arg[$user_page])) { ?>
                <ul class="author-links box b2-radius mg-b">
                <?php 
                    foreach ($arg as $k => $v) {
                        echo '<li class="'.($user_page == $k ? 'picked' : '').' user-tab-'.$k.'"><a class="b2-radius" href="'.$link.'/'.$k.'">'.$v.'</a></li>';
                    }
                ?>
                </ul>
            <?php } ?>
            <div class="author-page">
                <?php 
                    if($user_page){
                        if($user_page == $newsflashes_slug){
                            get_template_part('User/newsflashes');
                        }else{
                            get_template_part('User/'.$user_page);
                        }
                        
                    }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();