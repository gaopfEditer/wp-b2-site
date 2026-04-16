<?php
    /**
     *Template Name: 无小工具页面
    *
    *
    */
    get_header();
?>
    <div class="b2-single-content">

        <div id="primary-home" class="wrapper">

            <?php  while ( have_posts() ) : the_post();

				get_template_part( 'TempParts/Pages/normal' );

                if ( (comments_open() || get_comments_number()) && (int)b2_get_option('template_comment','comment_close') === 1) :
                    comments_template();
                endif;

                endwhile; ?>

        </div>
    </div>

<?php
get_footer();