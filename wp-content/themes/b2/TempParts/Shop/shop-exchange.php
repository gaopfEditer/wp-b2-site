<?php
use B2\Modules\Common\Shop;
/**
 * 存档页面
 */
get_header();
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;
$per_posts = get_option('posts_per_page');
$offset = ($paged -1)*$per_posts;
$_pages = 0;
?>

<div class="b2-single-content wrapper">

    <?php do_action('b2_shop_category_buy_before'); ?>

    <div id="primary-home" class="content-area wrapper shop-home-left b2-shop-type mg-b">
        <div class="shop-category-top box b2-radius mg-b">
            <p><?php echo Shop::shop_type_breadcrumb(); ?></p>
            <h1><?php echo __('所有兑换的商品','b2'); ?></h1>
        </div>
        <div class="shop-category">
            <?php

                do_action('b2_shop_category_buy_content_before');

                $args = array(
                    'post_type' => 'shop',
                    'orderby'  => 'date',
                    'order'=>'DESC',
                    'meta_key' => 'zrz_shop_type',
                    'meta_value' => 'exchange',
                    'posts_per_page'=>$per_posts,
                    'offset'=>$offset,
                    'post_status'=>'publish'
                );
                
                $shop_the_query = new \WP_Query( $args );
              
                if ( $shop_the_query->have_posts()) {
                    $_pages = $shop_the_query->max_num_pages;
                    while ( $shop_the_query->have_posts() ) {
                        $shop_the_query->the_post();
                        get_template_part( 'TempParts/Shop/item-exchange');
                    }
                    
                }else{
                    echo str_replace('empty-page','empty-page box',B2_EMPTY);
                }
                wp_reset_postdata();

                do_action('b2_shop_category_buy_content_after');

            ?>
        </div>
        <?php 
            $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
            if($pagenav && $_pages > 0){
                echo '<div class="b2-pagenav post-nav box b2-radius mg-t">'.$pagenav.'</div>';
            }
        ?>
    </div>

    <?php do_action('b2_shop_category_buy_after'); ?>

    <?php 
        get_sidebar(); 
    ?>

</div>
<?php
get_footer();