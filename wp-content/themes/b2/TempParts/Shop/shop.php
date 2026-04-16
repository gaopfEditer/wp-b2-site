<?php
/**
 * 存档页面
 */
get_header();
$type = get_post_meta(get_the_id(), 'zrz_shop_type', true);
?>

<?php do_action('b2_shop_list_top'); ?>

<div class="b2-single-content wrapper">

    <?php do_action('b2_shop_list_before'); ?>

    <div id="primary-home" class="content-area wrapper">

            <?php

                do_action('b2_shop_list_content_before');

                get_template_part( 'TempParts/Shop/list',$type);

                do_action('b2_shop_list_content_after');

            ?>

    </div>

    <?php do_action('b2_shop_list_after'); ?>

    <?php 
        get_sidebar(); 
    ?>

</div>
<?php
get_footer();