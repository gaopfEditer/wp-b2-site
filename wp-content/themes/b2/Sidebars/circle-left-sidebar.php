<?php
/**圈子左侧边栏
 */
$sidebars_widgets = wp_get_sidebars_widgets();
if(empty($sidebars_widgets['sidebar-9'])) return;

?>
<aside id="secondary-left" class="widget-area widget-area-left">
    <div class="sidebar">
        <div class="sidebar-innter widget-ffixed">
            <?php 
                dynamic_sidebar( 'sidebar-9' );
            ?>
        </div>
    </div>
</aside>