<?php
use B2\Modules\Common\Shop;
/**
 * 存档页面
 */
get_header();
$term = get_queried_object();
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;
?>

    <div class="b2-single-content wrapper">

        <?php do_action('b2_shop_category_before'); ?>

        <div id="primary-home" class="content-area wrapper shop-home-left mg-b">
            <div class="shop-category-top b2-hover box b2-radius mg-b">
                <p><?php echo Shop::shop_category_breadcrumb(); ?></p>
                <h1><?php echo $term->name; ?></h1>
            </div>
            <div class="shop-category">
                <?php

                    do_action('b2_shop_category_content_before');
                    if( have_posts()){
                        while ( have_posts() ) :
                            the_post();
                            $shop_type = get_post_meta(get_the_id(), 'zrz_shop_type', true);
                            get_template_part( 'TempParts/Shop/item',$shop_type);

                        endwhile;
                    }else{
                        echo str_replace('empty-page','empty-page box',B2_EMPTY);
                    }
                    do_action('b2_shop_category_content_after');

                ?>
            </div>
            <?php 
                $pagenav = b2_pagenav(array('pages'=>0,'paged'=>$paged)); 
                if($pagenav){
                    echo '<div class="b2-pagenav post-nav box b2-radius mg-t">'.$pagenav.'</div>';
                }
            ?>
        </div>

        <?php do_action('b2_shop_category_after'); ?>

        <?php 
            get_sidebar(); 
        ?>

    </div>
    
<?php
get_footer();