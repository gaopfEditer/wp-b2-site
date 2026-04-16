<?php

    $key = get_search_query();

    $count = 20;

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $offset = ($paged -1)*$count;

    global $wp;
    $url = B2_HOME_URI.'/'.$wp->request;
    $request = http_build_query($_REQUEST);
    $request = $request ? '?'.$request : '';

    $args = array(
        'post_type'=>'shop',
        'posts_per_page' => $count,
        'offset'=>$offset,
        'post_status'=>'publish',
        'search_tax_query'=>true,
        's'=>esc_attr($key)

    );

    $the_query = new \WP_Query( $args );

    $post_data = array();
    $_pages = 1;
    
    echo '<div class="hidden-line"><div class="shop-category shop-home-left">';
    if ( $the_query->have_posts() ) {
        
        $_pages = $the_query->max_num_pages;

        while ( $the_query->have_posts() ) {

            $the_query->the_post();

            $shop_type = get_post_meta($the_query->post->ID, 'zrz_shop_type', true);
            get_template_part( 'TempParts/Shop/item',$shop_type);

        }
        
    }else{
        echo '<div class="box" style="width: calc(100% - 16px);">'.B2_EMPTY.'</div>';
    }
    echo '</div></div>';
    wp_reset_postdata();
    

    // $pages = ceil($total/$count);


?>
<?php if($_pages > 1){ ?>
    <div class="b2-pagenav post-nav">
        <?php echo b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); ?>
    </div>
<?php } ?>