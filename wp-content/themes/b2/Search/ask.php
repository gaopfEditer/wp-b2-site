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
        'post_type'=>['ask','answer'],
        'posts_per_page' => $count,
        'offset'=>$offset,
        'post_status'=>'publish',
        'search_tax_query'=>true,
        's'=>esc_attr($key)

    );

    $the_query = new \WP_Query( $args );

    $post_data = array();
    $_pages = 1;
    ?>
    <?php
    echo '<div class="hidden-line"><div class="document-category box b2-radius document-home-left circle-search">';
    if ( $the_query->have_posts() ) {
        
        $_pages = $the_query->max_num_pages;

        while ( $the_query->have_posts() ) {

            $the_query->the_post();
            get_template_part( 'TempParts/Ask/search-item');

        }
    }else{
        echo B2_EMPTY;
    }
    wp_reset_postdata();
    echo '</div></div>';

    // $pages = ceil($total/$count);


?>
<?php if($_pages > 1){ ?>
    <div class="b2-pagenav post-nav box b2-radius mg-t">
        <?php echo b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); ?>
    </div>
<?php } ?>