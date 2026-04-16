<?php
    use B2\Modules\Common\Links;
    use B2\Modules\Templates\Modules\Sliders;
    $key = get_search_query();

    $count = 20;

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $offset = ($paged -1)*$count;

    global $wp;
    $request = http_build_query($_REQUEST);
    $request = $request ? '?'.$request : '';

    $args = array(
        'post_type'=>'links',
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
    <div class="box b2-radius b2-pd">
    <div class="link-breadcrumb b2-hover">
        <?php echo Links::link_breadcrumb(); ?>
    </div>
    <?php
    echo '<div class="hidden-line"><div class="link-search document-home-left">';
    if ( $the_query->have_posts() ) {
        
        $_pages = $the_query->max_num_pages;

        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $id = $the_query->post->ID;

            $term = wp_get_post_terms($id,'link_cat');
            $cat = '';
            if($term){
                $cat = '<div class="s-link-cat b2-color"><a href="'.get_term_link($term[0]->term_id).'" target="_blank">'.$term[0]->name.'</a></div>';
            }

            $icon = b2_get_thumb(array('thumb'=>get_post_meta($id,'b2_link_icon',true),'width'=>100,'height'=>100));
            
            $desc = $the_query->post->post_excerpt ? $the_query->post->post_excerpt : $the_query->post->post_content;
            $desc = $desc ? $desc : __('这个网站没有任何描述信息','b2');
            $desc = Sliders::get_des(0,200, $desc);


            echo '<div class="s-link-item">
                
                <div class="s-link-info">
                    '.b2_get_img(array('src'=>$icon)).'
                    <div class="s-link-data">
                        <h2><a href="'.get_permalink().'" target="_blank">'.get_the_title().'</a>'.$cat.'</h2>
                        <p>'.html_entity_decode($desc).'</p>
                    </div>
                </div>
            </div>';

        }
    }else{
        echo B2_EMPTY;
    }
    wp_reset_postdata();
    echo '</div></div>';

    // $pages = ceil($total/$count);


?>
</div>
<?php if($_pages > 1){ ?>
    <div class="b2-pagenav post-nav box b2-radius mg-t">
        <?php echo b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); ?>
    </div>
<?php } ?>