<?php
use B2\Modules\Common\Post;

/**
 * 文章内容页样式 post-style-5
 */

$excerpt = get_post_field('post_excerpt');

?>
<article id="post-5-list" class="b2-radius box">
    <div class="entry-content">
        <div id="video-list">
            <ul>
                <li v-for="(item,i) in videos">
                    <h2 v-show="item.h2" v-text="item.h2"></h2>
                    <div @click="select(i)">
                        <div class="video-list-play-icon b2-color"><span v-if="index == i"><?php echo b2_get_icon('b2-rhythm-line'); ?></span><span v-else><?php echo b2_get_icon('b2-play-mini-fill'); ?></span></div>
                        <div v-text="item.title"></div>
                    </div>
                </li>    
            </ul>
        </div>
    </div>
</article>

<article class="single-article b2-radius box">
    
    <?php do_action('b2_single_article_before'); ?>

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