<?php
use B2\Modules\Common\Shop;
use B2\Modules\Templates\Modules\Products;
use B2\Modules\Common\Coupon;

/**
 * 存档页面
 */
get_header();

$slider = Shop::shop_home_slider();
$slider = is_array($slider) ? $slider : array();

$width = (int)b2_get_option('template_main','wrapper_width');

$_height = b2_get_option('shop_main','shop_slider_height');
$show_title = (int)b2_get_option('shop_main','shop_slider_title');
$last = array_slice($slider,-4,4);
$count = count($slider);
$count = $count - 5;
?>

<?php do_action('b2_shop_home_top'); ?>

<div class="b2-single-content">

    <?php do_action('b2_shop_home_before'); ?>

    <div id="primary-home" class="content-area">

            <?php
                do_action('b2_shop_archive_content_before');
            ?>
            <div class="wrapper">
                <?php 
                    if(!empty($slider)){
                        //幻灯的设置项
                        $settings = array(
                            'wrapAround'=>true,
                            'fullscreen'=>true,
                            'autoPlay'=> 4000,
                            "prevNextButtons"=>true,
                            'pageDots'=> true
                        );

                        $settings = json_encode($settings,true);

                        $slider_setting = "data-flickity='".$settings."'";
                        ?>
                        <div class="shop-slider b2-radius mg-b"><div class="shop-slider-in" style="height:0;padding-top:<?php echo round($_height/$width*100,6).'%'; ?>"><div class="shop-slider-box" <?php echo $slider_setting; ?>>
                        <?php
                            foreach ($slider as $k => $v) {
                                $thumb = b2_get_thumb(array('thumb'=>$v['thumb'],'width'=>$width,'height'=>round($_height,0)));
                                ?>
                                    <div class="shop-slider-item">
                                        <a href="<?php echo $v['link']; ?>" class="link-block"></a>
                                        <?php echo b2_get_img(array(
                                            'src'=>$thumb,
                                            'class'=>array('shop-slider-img'),
                                            'alt'=>$v['title']
                                        )); ?>
                                        <?php if($show_title){ ?>
                                            <div class="shop-slider-info">
                                                <h2><?php echo $v['title']; ?></h2>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php
                            }
                        ?>
                       </div></div></div>
                       <?php
                    }
                ?>
            </div>
            <?php 
                $cats = b2_get_option('shop_main','shop_cat');
                $cats = Shop::get_shop_cat_data($cats);
                if(!empty($cats)){
                    
            ?>
                <div class="wrapper shop-cats mg-b">
                    <a class="shop-previous shop-cat-button" href="javascript:void(0)">
                        <svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z" class="arrow"></path></svg>
                    </a>
                    <div class="shop-cats-list">
                        <?php 
                            foreach ($cats as $k => $v) {
                                echo '<div class="shop-cats-item b2-radius" style="width:'.$v['width'].'px;min-width:'.$v['width'].'px;height:'.$v['height'].'px"><div class="shop-cats-in">
                                '.b2_get_img(array(
                                    'src'=>$v['thumb'],
                                    'class'=>array('shop-slider-img'),
                                    'alt'=>$v['title']
                                )).'
                                <a class="link-block" href="'.$v['link'].'">'.$v['title'].'</a></div></div>';
                            }
                        ?>
                    </div>
                    <a class="shop-next shop-cat-button" href="javascript:void(0)">
                        <svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg>
                    </a>
                </div>
            <?php
                }
            ?>

            <?php 
                $shop_type = b2_get_option('shop_main','shop_type');
                if(!empty($shop_type)){
                    echo '<div class="shop-home-box wrapper"><div class="shop-home-left">';
                    //购买
                    if(in_array('normal',$shop_type)){
            ?>
                        <div class="shop-type-normal mg-b">
                            <div class="shop-type-normal-title box b2-radius mg-b">
                                <h2 class="b2-color"><?php echo b2_get_icon('b2-shopping-bag-line').'<span>'.__('商品购买','b2').'</span>'; ?></h2>
                                <a href="<?php echo get_post_type_archive_link('shop').'/buy'; ?>"><?php echo __('全部','b2'); ?> ❯</a>
                            </div>
                            <div class="shop-normal-list-box">
                                <div class="shop-normal-list">
                                    <div class="hidden-line">
            <?php
                                        $args = array(
                                            'post_type' => 'shop',
                                            'orderby'  => 'date',
                                            'order'=>'DESC',
                                            'meta_key' => 'zrz_shop_type',
                                            'meta_value' => 'normal',
                                            'post_status'=>'publish',
                                            'no_found_rows'=>true,
                                            'posts_per_page'=>b2_get_option('shop_main','shop_type_count')
                                        );
                                        
                                        $shop_the_query = new \WP_Query( $args );

                                        if ( $shop_the_query->have_posts()) {
                                            while ( $shop_the_query->have_posts() ) {
                                                $shop_the_query->the_post();
                                                get_template_part( 'TempParts/Shop/item','normal');
                                            }
                                            
                                        }else{
                                            echo '<div class="box" style="width:100%">'.B2_EMPTY.'</div>';
                                        }
                                        
                                        wp_reset_postdata();
            ?>
                                    </div>    
                                </div>
                            </div>
                        </div>
            <?php
                    }
                    if(in_array('exchange',$shop_type)){
            ?>
                        <div class="shop-type-normal mg-b">
                            <div class="shop-type-normal-title box b2-radius mg-b">
                                <h2 class="b2-color"><?php echo b2_get_icon('b2-exchange-box-line').'<span>'.__('积分兑换','b2').'</span>'; ?></h2>
                                <a href="<?php echo get_post_type_archive_link('shop').'/exchange'; ?>"><?php echo __('全部','b2'); ?> ❯</a>
                            </div>
                            <div class="shop-normal-list-box">
                                <div class="shop-normal-list">
                                    <div class="hidden-line">
            <?php
                                        $args = array(
                                            'post_type' => 'shop',
                                            'orderby'  => 'date',
                                            'order'=>'DESC',
                                            'meta_key' => 'zrz_shop_type',
                                            'meta_value' => 'exchange',
                                            'post_status'=>'publish',
                                            'no_found_rows'=>true,
                                            'posts_per_page'=>b2_get_option('shop_main','shop_type_count')
                                        );
                                        
                                        $shop_the_query = new \WP_Query( $args );

                                        if ( $shop_the_query->have_posts()) {
                                            while ( $shop_the_query->have_posts() ) {
                                                $shop_the_query->the_post();
                                                get_template_part( 'TempParts/Shop/item','exchange');
                                            }
                                            
                                        }else{
                                            echo '<div class="box" style="width:100%">'.B2_EMPTY.'</div>';
                                        }
                                        wp_reset_postdata();
            ?>
                                    </div>    
                                </div>
                            </div>
                        </div>
            <?php
                    }
                    if(in_array('lottery',$shop_type)){
            ?>
                        <div class="shop-type-normal mg-b">
                            <div class="shop-type-normal-title box b2-radius mg-b">
                                <h2 class="b2-color"><?php echo b2_get_icon('b2-pantone-line').'<span>'.__('积分抽奖','b2').'</span>'; ?></h2>
                                <a href="<?php echo get_post_type_archive_link('shop').'/lottery'; ?>"><?php echo __('全部','b2'); ?> ❯</a>
                            </div>
                            <div class="shop-normal-list-box">
                                <div class="shop-normal-list">
                                    <div class="hidden-line">
            <?php
                                        $args = array(
                                            'post_type' => 'shop',
                                            'orderby'  => 'date',
                                            'order'=>'DESC',
                                            'meta_key' => 'zrz_shop_type',
                                            'meta_value' => 'lottery',
                                            'post_status'=>'publish',
                                            'no_found_rows'=>true,
                                            'posts_per_page'=>b2_get_option('shop_main','shop_type_count')
                                        );
                                        
                                        $shop_the_query = new \WP_Query( $args );

                                        if ( $shop_the_query->have_posts()) {
                                            while ( $shop_the_query->have_posts() ) {
                                                $shop_the_query->the_post();
                                                get_template_part( 'TempParts/Shop/item','lottery');
                                            }
                                            
                                        }else{
                                            echo '<div class="box" style="width:100%">'.B2_EMPTY.'</div>';
                                        }
                                        wp_reset_postdata();
            ?>
                                    </div>    
                                </div>
                            </div>
                        </div>
            <?php
                    }
                    echo '</div>';
                    get_sidebar(); 
   
                    echo '</div>';
                }
            ?>
            <?php
                do_action('b2_shop_archive_content_after');
            ?>
        
    </div>

    <?php do_action('b2_shop_home_after'); ?>

</div>
<?php
get_footer();