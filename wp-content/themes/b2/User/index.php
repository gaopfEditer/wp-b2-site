<?php
    use B2\Modules\Common\User;

    $user_id =  get_query_var('author');
    $user_data = get_userdata($user_id);

    $fans = get_user_meta($user_id,'zrz_followed',true);
    $fans = is_array($fans) ? count($fans) : 0;
    $follow = get_user_meta($user_id,'zrz_follow',true);
    $follow = is_array($follow) ? count($follow) : 0;

    $url = get_author_posts_url($user_id);

    $open_shop = b2_get_option('shop_main','shop_open');
    $newsflashes_open = b2_get_option('newsflashes_main','newsflashes_open');
    $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
?>
<div id="author-index">
    <div class="user-info box b2-radius mg-b">
        <p class="b2-pd">
            <span class="user-info-title"><?php echo __('昵称：','b2'); ?></span><span v-text="userData.name"></span>
        </p>
        <?php if(b2_get_option('verify_main','verify_allow')){ ?>
            <p class="b2-pd user-verify">
                <span class="user-info-title"><?php echo __('认证：','b2'); ?></span>
                <span v-if="userData.user_title"><b v-html="userData.verify_icon"></b><span v-text="userData.user_title"></span></span>
                <span v-else class="b2-hover">
                    <?php echo __('未认证','b2'); ?>
                    <a href="<?php echo b2_get_custom_page_url('verify'); ?>" target="_blank" v-if="userData.self"><?php echo __('前往认证','b2'); ?></a>
                </span>
            </p>
        <?php } ?>
        <p class="b2-pd">
            <span class="user-info-title"><?php echo __('描述：','b2'); ?></span>
            <span v-show="userData.self || userData.admin" v-cloak><?php echo sprintf(__('本站第%s号会员','b2'),'<b v-text="userData.id"></b>'); ?></span> <?php echo sprintf(__('入驻本站%s天','b2'),'<b>'.b2_time_days($user_data->user_registered).'</b>'); ?>
        </p>
        <p class="b2-pd">
            <span class="user-info-title"><?php echo __('性别：','b2'); ?></span>
            <span v-if="userData.sex == 1"><?php echo b2_get_icon('b2-men-line').__('男','b2'); ?></span>
            <span v-else><?php echo b2_get_icon('b2-women-line').__('女','b2'); ?></span>
        </p>
        <p class="b2-pd">
            <span class="user-info-title"><?php echo __('网址：','b2'); ?></span>
            <span v-text="userData.url" v-if="userData.url"></span>
            <span v-else class="b2-hover">
                <?php echo __('没有网址','b2'); ?>
                <a href="<?php echo $url.'/settings'; ?>" v-if="userData.self"><?php echo __('前往设置','b2'); ?></a>
            </span>
        </p>
        <p class="b2-pd">
            <span class="user-info-title"><?php echo __('简介：','b2'); ?></span>
            <span v-html="userData.desc" v-if="userData.desc"></span>
            <span class="b2-hover" v-else>
                <?php echo __('没有描述','b2'); ?>
                <a href="<?php echo $url.'/settings'; ?>" v-if="userData.self"><?php echo __('前往设置','b2'); ?></a>
            </span>
        </p>
    </div>
    <div class="user-sidebar-gold box b2-radius mg-b" v-show="userData.admin || userData.self">
        <h2><?php echo __('财富','b2'); ?></h2>
        <div class="user-money-and-credit">
            <div class="user-sidebar-money"><div v-html="userData.money"></div></div>
            <div class="user-sidebar-credit"><div v-html="userData.credit"></div></div>
        </div>
    </div>
    <ul class="user-sidebar-count box b2-radius">
        <li>
            <?php echo b2_get_icon('b2-article-line'); ?>
            <div>
                <div class="user-item-name"><?php echo __('发布的文章','b2'); ?></div>
                <p class="user-item-count"><?php echo count_user_posts($user_id, 'post' ); ?></p>
                <div class="user-item-desc"><?php echo __('在本站的投稿','b2'); ?></div>
                <a href=""></a>
            </div>
        </li>
        <?php if((int)$newsflashes_open !== 0) { ?>
        <li>
            <?php echo b2_get_icon('b2-flashlight-line'); ?>
            <div>
                <div class="user-item-name"><?php echo sprintf(__('发布的%s','b2'),$newsflashes_name); ?></div>
                <p class="user-item-count"><?php echo count_user_posts($user_id, 'newsflashes' ); ?></p>
                <div class="user-item-desc"><?php echo sprintf(__('在本站发布的%s','b2'),$newsflashes_name); ?></div>
                <a href=""></a>
            </div>
        </li>
        <?php } ?>
        <li>
            <?php echo b2_get_icon('b2-chat-smile-2-line'); ?>
            <div>
                <div class="user-item-name"><?php echo __('提交的评论','b2'); ?></div>
                <p class="user-item-count"><?php echo B2\Modules\Common\Comment::get_user_comment_count($user_id); ?></p>
                <div class="user-item-desc"><?php echo __('在本站提交的评论','b2'); ?></div>
                <a href=""></a>
            </div>
        </li>
        <li>
            <?php echo b2_get_icon('b2-heart-add-line'); ?>
            <div>
                <div class="user-item-name"><?php echo __('关注','b2'); ?></div>
                <p class="user-item-count"><?php echo $follow; ?></p>
                <div class="user-item-desc"><?php echo __('关注的人数','b2'); ?></div>
                <a href=""></a>
            </div>
        </li>
        <li>
            <?php echo b2_get_icon('b2-heart-pulse-line'); ?>
            <div>
                <div class="user-item-name"><?php echo __('粉丝','b2'); ?></div>
                <p class="user-item-count"><?php echo $fans; ?></p>
                <div class="user-item-desc"><?php echo __('粉丝人数','b2'); ?></div>
                <a href=""></a>
            </div>
        </li>
        <li>
            <?php echo b2_get_icon('b2-star-line'); ?>
            <div>
                <div class="user-item-name"><?php echo __('收藏的文章','b2'); ?></div>
                <p class="user-item-count"><?php echo User::get_user_collection_count($user_id,'post'); ?></p>
                <div class="user-item-desc"><?php echo  __('收藏的文章数量','b2'); ?></div>
                <a href=""></a>
            </div>
        </li>
        <?php if((int)$newsflashes_open !== 0) { ?>
        <li>
            <?php echo b2_get_icon('b2-flashlight-line'); ?>
            <div>
                <div class="user-item-name"><?php echo sprintf(__('收藏的%s','b2'),$newsflashes_name); ?></div>
                <p class="user-item-count"><?php echo User::get_user_collection_count($user_id,'newsflashes'); ?></p>
                <div class="user-item-desc"><?php echo sprintf(__('收藏的%s数量','b2'),$newsflashes_name); ?></div>
                <a href=""></a>
            </div>
        </li>
        <?php } ?>
        <?php if((int)$open_shop !== 0){ ?>
        <li>
            <?php echo b2_get_icon('b2-shopping-bag-line'); ?>
            <div>
                <div class="user-item-name"><?php echo __('收藏的商品','b2'); ?></div>
                <p class="user-item-count"><?php echo User::get_user_collection_count($user_id,'shop'); ?></p>
                <div class="user-item-desc"><?php echo __('收藏的商品数量','b2'); ?></div>
                <a href=""></a>
            </div>
        </li>
        <?php } ?>
    </ul>
</div>