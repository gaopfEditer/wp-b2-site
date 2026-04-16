<?php
use B2\Modules\Common\Post;
/**
 * 文章内容页
 */
$post_id = get_the_id();
$post_meta = Post::post_meta($post_id);
$excerpt = get_post_field('post_excerpt');

$parent = get_post_field( 'post_parent',$post_id);

$parent_title = get_the_title($parent);
$parent_link = get_permalink($parent);

?>
<div class="b2-radius box mg-b single-answer-parent">
    <div class="single-answer-type"><?php echo __('问题','b2'); ?></div>
    <div class="single-answer-title"><a href="<?php echo $parent_link; ?>" target="_blank"><?php echo $parent_title ; ?></a></div>
    <div class="single-answer-desc"><a href="<?php echo $parent_link; ?>" target="_blank"><?php echo b2_get_des($parent,100);?></a></div>
</div>
<article class="single-article b2-radius box">

    <?php do_action('b2_single_article_before'); ?>
    
    <header class="entry-header">
        <h1 class="ask-list-b"><?php echo get_the_title(); ?></h1>
        <div id="post-meta">
            <?php if(!is_audit_mode()){ ?>
                <div class="post-user-info">
                    <div class="post-meta-left">
                        <a class="link-block" href="<?php echo b2_get_custom_page_url('ask-people').'?id='.$post_meta['user_id']; ?>" target="_blank"></a>
                        <div class="avatar-parent"><img class="avatar b2-radius" src="<?php echo $post_meta['user_avatar']; ?>" /><?php echo $post_meta['user_title'] ? $post_meta['verify_icon'] : ''; ?></div>
                        <div class="post-user-name"><b><?php echo $post_meta['user_name']; ?></b><span class="user-title"><?php echo $post_meta['user_title']; ?></span></div>
                    </div>
                    <div class="post-meta-right">
                        <div class="" v-if="self == false" v-cloak>
                            <button @click="followingAc" class="author-has-follow" v-if="following"><?php echo __('取消关注','b2'); ?></button>
                            <button @click="followingAc" v-else><?php echo b2_get_icon('b2-add-line').__('关注','b2'); ?></button>
                            <button class="empty" @click="dmsg()"><?php echo __('私信','b2'); ?></button>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="post-meta-row">
                <ul class="post-meta">
                    <li class="single-date">
                        <span><?php echo $post_meta['date']; ?></span>
                    </li>
                    <li class="single-like">
                        <span><?php echo b2_get_icon('b2-heart-fill'); ?><b v-text="postData.up"></b></span>
                    </li>
                    <li class="single-eye">
                        <span><?php echo b2_get_icon('b2-eye-fill'); ?><b v-text="postData.views"></b></span>
                    </li>
                    <li class="single-edit green" v-if="postData.post_status == 'pending'" v-cloak>
                        <span><?php echo __('待审','b2'); ?></span>
                    </li>
                </ul>
                
                <div class="single-button-download red" v-cloak v-if="postData.can_edit">
                    <a href="<?php echo $parent_link.'?answer_id='.$post_id; ?>" target="_blank"><?php echo __('编辑','b2'); ?></a>
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

        <?php
            global $page, $numpages, $multipage, $more;
            echo b2_pagenav(array('pages'=>$numpages,'paged'=>$page),true);
		?>
        <?php do_action('b2_single_post_content_after'); ?>
    </div>

    <?php do_action('b2_single_article_after'); ?>
</article>