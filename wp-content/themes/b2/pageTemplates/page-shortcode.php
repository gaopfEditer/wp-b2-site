<?php
    /**
     *Template Name: 无白色背景页面
    *
    *
    */
    get_header();
?>
    <div class="b2-single-content wrapper">

        <div id="primary-home" class="wrapper content-area">
            <article class="single-article">
            <div class="entry-content">
                <?php  while ( have_posts() ) : the_post();
                    
                    the_content();

                endwhile; ?>
            </div>
            </article>
        </div>
    </div>

<?php
get_footer();