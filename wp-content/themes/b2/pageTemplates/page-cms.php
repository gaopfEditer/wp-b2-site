<?php
use B2\Modules\Templates\Index;
/**
 *Template Name: CMS首页
*
*
*/
get_header();

?>

<?php do_action('b2_index_before'); ?>

<div class="b2-content">

    <div id="primary-home" class="content-area">

        <?php 
            $index = new Index();
            $index->modules_loader();
        ?>
        
    </div>

</div>

<?php do_action('b2_index_after'); ?>

<?php
get_footer();
