<?php
use B2\Modules\Common\Post;

$post_id = get_the_id();
$cat = Post::get_categorys($post_id,'ask_cat');

$post_type = get_post_type($post_id);

$cat_html = '<div class="document-cat-rot">';
if($post_type == 'ask'){
    $cat_html .= '<span class="red">'.b2_get_option('normal_custom','custom_ask_name').'</span>';
}else{
    $cat_html .= '<span class="green">'.b2_get_option('normal_custom','custom_answer_name').'</span>';
}
$cat_html .= '</div>';

$title = get_the_title();
$title = $title ? $title : b2_get_excerpt($post_id);
?>
<div class="document-row b2-pd">
    <div class="document-row-left">
        <?php echo $cat_html; ?>
        <h3><a href="<?php echo get_permalink(); ?>"><?php echo $title; ?></a></h3>
    </div>
    <div class="document-row-right">
        <?php echo get_the_date('Y-m-d H:i:s'); ?>
    </div>
</div>