<?php
use B2\Modules\Common\Post;
/**
 * 文章内容页
 */
$post_meta = Post::post_meta(get_the_id());
$excerpt = get_post_field('post_excerpt');

?>
<article class="single-article b2-radius box">

    <header class="entry-header">
        <div class="mg-b">
            <?php echo B2\Modules\Templates\Modules\Posts::get_post_cats('target="__blank"',$post_meta,array(),'post_3'); ?>
        </div>
        <h1><?php echo get_the_title(); ?></h1>
        <?php if($excerpt){ ?>
            <div class="content-excerpt">
                <?php echo get_the_excerpt(); ?>
            </div>
        <?php } ?>
    </header>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>
</article>