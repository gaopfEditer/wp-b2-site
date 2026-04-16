<?php
/**
 *Template Name: 瀑布流排版
*
*
*/
get_header();
?>
<div class="b2-single-content">

    <div id="primary-home" class="wrapper">

        <?php 
            do_action('b2_haoqi');
        ?>
    </div>
</div>

<?php
get_footer();