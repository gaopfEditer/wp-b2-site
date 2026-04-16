<?php
use B2\Modules\Common\Post;
/**
 * 文章内容页
 */
$post_id = get_the_id();
$post_meta = Post::post_meta($post_id);
$excerpt = get_post_field('post_excerpt');

?>
<article class="single-article b2-radius box">

    <?php do_action('b2_single_article_before'); ?>

    <header class="entry-header">
        <div class="mg-b">
            <?php echo B2\Modules\Templates\Modules\Posts::get_post_cats('target="__blank"',$post_meta,array('cats'),'post_3'); ?>
        </div>
        <h1><?php echo get_the_title(); ?></h1>
        <div id="post-meta">
            <ul class="post-meta">
                <li class="single-date">
                    <?php echo $post_meta['date']; ?>
                </li>
                <li class="single-like">
                    <?php echo b2_get_icon('b2-heart-fill'); ?><span v-text="postData.up"></span>
                </li>
                <li class="single-eye">
                    <?php echo b2_get_icon('b2-eye-fill'); ?><span v-text="postData.views"></span>
                </li>
            </ul>
            <div class="post-user-info">
                <div class="post-meta-left">
                    <div class="avatar-parent"><img class="avatar b2-radius" src="<?php echo $post_meta['user_avatar']; ?>" /><?php echo $post_meta['user_title'] ? $post_meta['verify_icon'] : ''; ?></div>
                    <div class="post-user-name"><a class="" href="<?php echo $post_meta['user_link']; ?>"><?php echo $post_meta['user_name']; ?></a><span class="user-title"><?php echo $post_meta['user_title']; ?></span></div>
                </div>
                <div class="post-meta-right">
                    <div class="" v-if="self == false" v-cloak>
                        <button @click="followingAc" class="author-has-follow" v-if="following"><?php echo __('取消关注','b2'); ?></button>
                        <button @click="followingAc" v-else><?php echo b2_get_icon('b2-add-line').__('关注','b2'); ?></button>
                        <button class="empty" @click="dmsg()"><?php echo __('私信','b2'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="entry-content">
        <?php do_action('b2_single_post_content_before'); ?>
        <?php if($excerpt){ ?>
            <div class="content-excerpt">
                <?php echo get_the_excerpt(); ?>
            </div>
        <?php } ?>
        <?php the_content(); ?>

        <?php do_action('b2_single_post_content_after'); ?>
    </div>

    <?php do_action('b2_single_article_after'); ?>
</article>