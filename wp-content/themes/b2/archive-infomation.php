<?php
use B2\Modules\Common\Infomation;
/**
 * 供求
 */
get_header();

$name = b2_get_option('normal_custom','custom_infomation_name');
$for = b2_get_option('normal_custom','custom_infomation_for');
$get = b2_get_option('normal_custom','custom_infomation_get');
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

$count = b2_get_option('infomation_main','infomation_per_count');
$offset = ($paged -1)*$count;

?>
<div class="b2-single-content wrapper mg-b">

    <div id="primary-home" class="content-area b2-infomation" ref="b2infomation" data-paged="<?php echo $paged; ?>" data-count="<?php echo $count; ?>">
        <div class="infomation-list box b2-radius">
            <div class="infomation-top-box">
                <div class="info-h1">
                    <h1><?php echo b2_get_option('infomation_main','infomation_title'); ?></h1>
                    <a href="<?php echo b2_get_custom_page_url('po-infomation');?>" class="button" target="_blank">发布</a>
                </div>
                <div class="infomation-desc">
                    <?php echo b2_get_option('infomation_main','infomation_desc'); ?>
                </div>
            </div>
            <?php get_template_part( 'TempParts/infomation/archive'); ?>
        </div>

        <div class="circle-list">
            <ul>
                <?php 
                    $_pages = 0;
                    $args = [
                        'offset'=>$offset,
                        'post_status'=>'publish',
                        'include_children' => true,
                        'posts_per_page'=>$count,
                        'post_type'=>'infomation',
                        'meta_query'=>array(
                            'relation' => 'AND',
                            array(
                                'key'     => 'b2_infomation_sticky',
                                'type' => 'NUMERIC'
                            ),
                        ),
                        'orderby'=>['meta_value_num' => 'DESC','date' => 'DESC']
                    ];

                    $topic_query = new \WP_Query( $args );

                    if ( $topic_query->have_posts()) {
                        $_pages = $topic_query->max_num_pages;
                        while ( $topic_query->have_posts() ) {
                            $topic_query->the_post();

                            get_template_part( 'TempParts/infomation/infomation','item');

                        }
                        
                    }
                    wp_reset_postdata();
                ?>
            </ul>
            <?php
                $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
                if($pagenav){
                    echo '<div class="b2-pagenav collection-nav post-nav box">'.$pagenav.'</div>';
                }
            ?>
        </div>
    </div>

    <?php 
        get_sidebar(); 
    ?>

</div>
<?php
get_footer();