<?php
use B2\Modules\Common\Post;

$post_id = get_the_id();
$cat = Post::get_categorys($post_id,'document_cat');

$cat_html = '<div class="document-cat-rot">';
foreach ($cat as $k => $v) {
    $cat_html .='<span><a href="'.$v['link'].'">'.$v['name'].'</a></span>';
}
$cat_html .= '</div>';
?>
<div class="document-row b2-pd">
    <div class="document-row-left">
        <?php echo $cat_html; ?>
        <h3><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
    </div>
    <div class="document-row-right">
        <?php echo b2_timeago(get_the_date('Y-m-d G:i:s')); ?>
    </div>
</div>