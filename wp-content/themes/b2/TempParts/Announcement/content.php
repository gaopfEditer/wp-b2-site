<?php
/**
 * 公告页内容模板
 */

$is_single = is_singular();
?>
<article class="announcement-box mg-b b2-pd b2-radius box">
    <h2 class="title"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h2>
    <div class="announcement-date mg-t mg-b"><?php echo get_the_date(); ?></div>
    <div class="entry-content">

        <?php if($is_single){ ?>

            <p><?php the_content(); ?></p>

        <?php }else{ ?>

            <blockquote><?php echo b2_get_excerpt(get_the_ID()); ?></blockquote>

            <div class="announcement-read-more mg-t"><a href="<?php echo get_permalink(); ?>" class=""><?php echo __('详情','b2'); ?></a></div>

        <?php } ?>

    </div>
</article>