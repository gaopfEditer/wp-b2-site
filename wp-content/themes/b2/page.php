<?php
    get_header();
?>
    <div class="b2-single-content wrapper">

        <div id="primary-home" class="content-area">

            <?php  while ( have_posts() ) : the_post();

				get_template_part( 'TempParts/Pages/normal' );

                if ( (comments_open() || get_comments_number()) && (int)b2_get_option('template_comment','comment_close') === 1) :
                    comments_template();
                endif;

                endwhile; ?>

        </div>

    <?php 
        get_sidebar(); 
    ?>
    
    </div>

<?php
get_footer();