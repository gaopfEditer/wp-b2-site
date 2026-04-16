<?php
$post_id = get_the_id();

?>

<article class="single-cpay b2-radius">
    <div class="entry-content">
        <?php echo do_shortcode( '[b2_custom_pay id="'.$post_id.'"]' ); ?>
    </div>
</article>