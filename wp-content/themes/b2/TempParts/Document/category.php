<?php
use B2\Modules\Common\Document;
get_header();
$search_attr = b2_get_option('document_main','document_search_attr');
$term = get_queried_object();
$term_id = $term->term_id;

$count = b2_get_option('document_main','document_show_count');
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;
$offset = ($paged -1)*$count;
?>
<div class="b2-document-single mg-t- mg-b">
    <div class="document-single-top wrapper">
        <div class="document-breadcrumb b2-hover">
            <?php echo Document::document_breadcrumb($post_id = 0); ?>
        </div>
        <div class="document-top-search">
        <form method="get" action="<?php echo B2_HOME_URI; ?>" class="single-document-search">
            <input type="text" name="s" autocomplete="off" class="search-input b2-radius" placeholder="<?php echo __('请输入关键词','b2'); ?>"> 
            <input type="hidden" name="type" value="document"> 
            <div class="search-button"><button><?php echo b2_get_icon('b2-search-line'); ?></button></div>
        </form>
        </div>
    </div>
</div>
<div class="b2-single-content wrapper">
    <div id="primary-home" class="content-area">
        <div class="document-content">
            <div class="document-cat-top">
                <h2><?php echo $term->name; ?></h2>
                <p><?php echo $term->description; ?></p>
            </div>
            <div class="box b2-radius">
                <?php 
                    $_pages = 0;
                     $args = array(
                        'post_type' => 'document',
                        'order' => 'ASC',
                        'orderby'   => 'meta_value_num',
                        'post_status'=>'publish',
                        'tax_query' => array(
                            'relation' => 'OR',
                            array(
                                'taxonomy' => 'document_cat',
                                'field' => 'term_id',
                                'terms' => $term_id
                            )
                        ),
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key' => 'b2_document_order',
                                'type' => 'NUMERIC',
                            ),
                            array(
                                'key' => 'b2_document_order',
                                'compare' => 'NOT EXISTS'
                            )
                        ),
                        'posts_per_page'=>$count,
                        'offset'=>$offset,
                        'paged'=>$paged
                    );
                    
                    $document_the_query = new \WP_Query( $args );

                    if ( $document_the_query->have_posts()) {
                        $_pages = $document_the_query->max_num_pages;
                        while ( $document_the_query->have_posts() ) {
                            $document_the_query->the_post();
                            get_template_part( 'TempParts/Document/item','normal');
                        }
                        
                    }else{
                        echo B2_EMPTY;
                    }
                    wp_reset_postdata();
                ?>
            </div>
            <?php 
                $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
                if($pagenav){
                    echo '<div class="b2-pagenav collection-nav post-nav mg-t box b2-radius">'.$pagenav.'</div>';
                }
            ?>
        </div>
    </div>
</div>
<?php
get_footer();