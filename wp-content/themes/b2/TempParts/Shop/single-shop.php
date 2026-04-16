<?php
use B2\Modules\Common\Shop;
$post_id = get_the_id();
$excerpt = get_post_field('post_excerpt');
$attrs = Shop::shop_single_attrs($post_id);
?>
<div id="shop-single" ref="shopRes" data-id="<?php echo $post_id; ?>">
    <div class="buy-resout mg-b box b2-radius" v-cloak v-if="resout.data && resout.commodity === 0">
        <div class="buy-resout-title shop-single-attr-title">
            <h2><?php echo __('购买结果','b2'); ?></h2>
            <p></p>
        </div>
        <div class="buy-resout-box" v-html="resout.data">
        </div>
    </div>
</div>
<?php if(!empty($attrs)){ ?>
    <div class="box b2-radius mg-b shop-single-attr">
        <div class="shop-single-attr-title"><?php echo __('商品属性','b2'); ?></div>
        <div class="shop-single-attr-data">
            <ul>
                <?php
                    foreach ($attrs as $v) {
                        echo '<li><span class="shop-single-attr-k">'.$v['k'].'：</span><span class="shop-single-attr-v">'.$v['v'].'</span></li>';
                    }
                ?>
            </ul>
        </div>
    </div>
<?php } ?>
<div class="box b2-radius">
    <div class="shop-single-attr-title"><?php echo __('商品简介','b2'); ?></div>
    <article class="single-article">
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
</div>