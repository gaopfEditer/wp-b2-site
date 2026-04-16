<?php
use B2\Modules\Common\Post;
use B2\Modules\Common\Newsflashes;
/**
 * 文章内容页
 */
$post_id = get_the_id();
$post_meta = Post::post_meta($post_id);
$thumb = get_the_post_thumbnail_url($post_id,'full');

$data = Newsflashes::get_newsflashes_item_data($post_id);

?>
<article class="single-article b2-radius box">

    <header class="entry-header">
        <div class="">
            <?php echo B2\Modules\Templates\Modules\Posts::get_post_cats('target="__blank"',$post_meta,array('cats'),'post_3'); ?>
        </div>
        <h1><?php echo $data['title']; ?></h1>
        <div id="post-meta">
            <ul class="post-meta">
                <?php if(isset($data['tag']['link'])){ ?> 
                    <li class="single-tags">
                        <span class="b2-radius"><a href="<?php echo $data['tag']['link']; ?>" target="_blank"><?php echo $data['tag']['name']; ?></a></span>
                    </li>
                <?php } ?>
                <li class="single-date">
                    <span><?php echo $data['date']['date'].__('日','b2'); ?></span>
                </li>
                <li class="single-eye">
                    <span><?php echo b2_get_icon('b2-eye-fill'); ?><b v-text="postData.views"></b></span>
                </li>
            </ul>
            <div class="post-user-info">
                <div class="post-meta-left">
                    <a class="link-block" href="<?php echo $post_meta['user_link']; ?>"></a>
                    <div class="avatar-parent">
                    <?php echo b2_get_img(array('src'=>$post_meta['user_avatar'],'class'=>array('avatar','b2-radius'))); ?>
                    <?php echo $post_meta['user_title'] ? $post_meta['verify_icon'] : ''; ?></div>
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
        </div>
    </header>
    <div class="entry-content">
        <?php unset($post_meta); do_action('b2_single_post_content_before'); ?>

        <?php if($thumb) { ?>
            <div class="newsflashes-thumb">
                <?php echo b2_get_img(array('src'=>$thumb)); ?>
            </div>
        <?php } ?>

        <?php the_content(); ?>

        <?php 
            if($data['from']){
                echo '<p class="b2-hover"><a class="news-from" href="'.$data['from'].'" target="_blank" ref="nofollow">'.b2_get_icon('b2-external-link-line').__('原文连接','b2').'</a></p>';
            }
        ?>
        <?php do_action('b2_single_post_content_after'); ?>
    </div>

    <?php do_action('b2_single_article_after'); ?>
</article>