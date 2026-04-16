<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}
/**
 * 首页
 */
get_header();

?>

<?php do_action('b2_index_before'); ?>

<div class="b2-content">

    <div id="primary-home" class="content-area">

        <?php do_action('b2_index'); ?>
        
    </div>

</div>

<?php do_action('b2_index_after'); ?>

<?php
get_footer();
