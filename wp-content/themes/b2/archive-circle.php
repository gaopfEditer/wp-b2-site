<?php
get_header();
?>
<div class="b2-single-content wrapper circle-archive">
    <?php get_template_part( 'Sidebars/circle','left-sidebar');?>
    <div id="primary-home" class="wrapper content-area">
        <main class="site-main">
            <?php get_template_part( 'TempParts/circle/circle-form');?>
            <?php get_template_part( 'TempParts/circle/circle-topic-list');?>
        </main>
    </div>
    <?php
        get_sidebar();
    ?>
</div>
<?php
get_footer();