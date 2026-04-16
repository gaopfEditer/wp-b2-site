<?php
use B2\Modules\Common\Infomation;

$name = b2_get_option('normal_custom','custom_infomation_name');
$for = b2_get_option('normal_custom','custom_infomation_for');
$get = b2_get_option('normal_custom','custom_infomation_get');
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

$count = (int)b2_get_option('infomation_main','infomation_per_count');
$offset = ($paged -1)*$count;

$key = get_search_query();

?>
<div class="b2-single-content wrapper">

    <div id="primary-home" class="content-area b2-infomation" ref="b2infomation" data-paged="<?php echo $paged; ?>" data-count="<?php echo $count; ?>" data-key="<?php echo $key; ?>">
        <div class="infomation-list box b2-radius">
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
                        's'=>$s,
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