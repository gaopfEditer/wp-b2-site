<?php
get_header();
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
if(!$topic_id){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}

$circle_id = B2\Modules\Common\Circle::get_circle_id_by_topic_id($topic_id);
?>
<div class="b2-single-content wrapper circle-topic-edit topicEdit" ref="topicEdit" data-circleId="<?php echo $circle_id; ?>">
    <div id="primary-home" class="wrapper content-area">
        <main class="site-main">
            <?php get_template_part( 'TempParts/circle/circle-form');?>
        </main>
    </div>
</div>
<style>.single-circle .b2-single-content, .circle-topic-edit.b2-single-content {
        width: 620px;
        max-width: 100%;
    }</style>
<?php
get_footer();