<?php
get_header();
$top_img = b2_get_option('document_main','document_image');
$search_title = b2_get_option('document_main','document_search_title');
$search_attr = b2_get_option('document_main','document_search_attr');
$cats = b2_get_option('document_main','document_cat');

$count = b2_get_option('document_main','document_show_count');
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

$offset = ($paged -1)*$count;

$document_name = b2_get_option('normal_custom','custom_document_name');

?>
<div class="document-top" style="background-image:url(<?php echo b2_get_thumb(array('thumb'=>$top_img,'width'=>1900,'height'=>600)); ?>)">
    <h2><?php echo $search_title; ?></h2>
    <form method="get" action="<?php echo B2_HOME_URI; ?>" class="search-form-document">
        <input type="text" name="s" autocomplete="off" class="search-input b2-radius" placeholder="<?php echo $search_attr; ?>"> 
        <input type="hidden" name="type" value="document"> 
        <div class="search-button"><button><?php echo b2_get_icon('b2-search-line'); ?></button></div>
    </form>
</div>
<div id="primary-home" class="wrapper">
    <main class="site-main">
        <?php 
            if($cats) {
        ?>
            <div class="document-cat-box box b2-radius">
                <?php
                    foreach ($cats as $k => $v) {
                        $t = get_term_by('id', $v, 'document_cat');

                        if(is_wp_error( $t ) || !isset($t->name)) continue;

                        $img = get_term_meta($v,'b2_tax_img',true);
                        $thumb = b2_get_thumb(array('thumb'=>$img,'width'=>50,'height'=>50,'ratio'=>2));

                        ?>
                        <div class="document-cat-item">
                            <a href="<?php echo get_term_link((int)$v); ?>" class="link-block"></a>
                            <?php echo b2_get_img(array('src'=>$thumb,'alt'=>$t->name,'class'=>array('b2-radius'))); ?>
                            <div class="document-cat-item-info">
                                <h2><?php echo $t->name; ?></h2>
                                <p><?php echo $t->description; ?></p>
                            </div>
                        </div>
                        <?php
                    }
                ?>
            </div>
        <?php } ?>
        <div class="document-content">
            <h2 class="home-atchive-title"><?php echo sprintf(__('最新%s','b2'),$document_name); ?></h2>
            <div class="box b2-radius">
                <?php 
                    $_pages = 0;
                     $args = array(
                        'post_type' => 'document',
                        'orderby'  => 'modified',
                        'order'=>'DESC',
                        'post_status'=>'publish',
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
                        wp_reset_postdata();
                    }else{
                        echo B2_EMPTY;
                    }
                    
                ?>
            </div>
            <?php 
                $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
                if($pagenav){
                    echo '<div class="b2-pagenav collection-nav post-nav box">'.$pagenav.'</div>';
                }
            ?>
        </div>
    </main>
</div>

<?php
get_footer();