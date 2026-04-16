<?php
$style = B2\Modules\Templates\Single::get_single_post_settings(get_the_id(),'single_post_style');
$style = $style ? $style : 'post-style-1';

get_template_part( 'TempParts/Single/content',$style);