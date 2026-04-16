<?php
    use B2\Modules\Common\Document;
    get_header();
    $post_id = get_the_id();
    $terms = get_the_terms($post_id,'document_cat');
    $term_id = isset($terms[0]->term_id) ? $terms[0]->term_id : 0;
    global $wp;
    $current_url = B2_HOME_URI.'/'.$wp->request;
?>
    
    <?php do_action('b2_document_wrapper_before'); ?>
    <div class="b2-document-single mg-t- mg-b">
        <div class="document-single-top wrapper">
            <div class="document-breadcrumb b2-hover">
                <?php echo Document::document_breadcrumb($post_id); ?>
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
    <div class="b2-document-content wrapper">
        <?php do_action('b2_document_before'); ?>

        <div class="document-left widget-area b2-pd-r">
            <?php 
                $li = '';
                $args = array(
                    'post_type' => 'document',
                    'post_status'=>'publish',
                    'tax_query' => array(
                        array(
                        'taxonomy' => 'document_cat',
                        'field' => 'term_id',
                        'terms' => array($term_id)
                        )
                    ),
                    'order' => 'ASC',
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
                    'orderby'   => 'meta_value_num',
                    'posts_per_page'=>50,
                    'no_found_rows'=>true
                );

                $document_the_query = new \WP_Query( $args );

                if ( $document_the_query->have_posts()) {

                    while ( $document_the_query->have_posts() ) {
                        
                        $document_the_query->the_post();
                        $link = get_permalink();
                        if($current_url === $link){
                            $li .= '<li>'.get_the_title().'</li>';
                        }else{
                            $li .= '<li><a href="'.$link.'">'.get_the_title().'</a></li>';
                        }
                        
                    }
                    
                }else{
                    $li = '';
                }
                wp_reset_postdata();
             
            ?>
            <?php if($li) { ?>
                <div class="document-left-item box b2-hover b2-radius mg-b">
                    <h2><?php echo __('此组别内的文章','b2'); ?></h2>
                    <ul>
                        <?php echo $li; ?>
                    </ul>
                </div>
            <?php } ?>
            <div class="document-left-item box b2-radius request-supper">
                <h2><?php echo __('需要支持？','b2'); ?></h2>
                <p><?php echo __('如果通过文档没办法解决您的问题，请提交工单获取我们的支持！','b2'); ?></p>
                <div><a href="javascript:void(0)" class="button" @click="canRequest('<?php echo b2_get_custom_page_url('requests'); ?>')"><?php echo __('提交工单','b2'); ?></a></div>
            </div>
        </div>

        <div id="primary-home" class="content-area">

            <?php  while ( have_posts() ) : the_post();

                echo '<div class="box b2-radius b2-pd mg-b">';

                do_action('b2_document_content_before');

                get_template_part( 'TempParts/single-document');

                do_action('b2_document_content_after');

                echo '</div>';

                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
        
                endwhile; ?>
            
        </div>
        
        <?php do_action('b2_document_after'); ?>

    </div>

    <?php do_action('b2_document_wrapper_after'); ?>
<?php
get_footer();