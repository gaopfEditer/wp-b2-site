<?php
use B2\Modules\Common\Post;
get_header();

$tags = Post::get_post_tags(198);

?>
<div class="b2-single-content wrapper">
    <div id="tags" class="tags-page wrapper">
        <main id="main" class="site-main">
            <h1><?php echo __('热门标签','b2'); ?></h1>
            <?php if($tags){ 
                echo '<ul>';
                foreach ($tags as $k => $v) {
                    echo '<li>
                        <a href="'.$v['link'].'" target="_blank" class="box b2-radius b2-mg">
                        <h2 title="'.$v['name'].'">'.$v['name'].'</h2>
                        <p>'.__('共','b2').b2_number_format($v['count']).__('篇文章','b2').'</p>
                        </a>
                    </li>';
                }
                echo '</ul>';
            ?>

            <?php 
            }else{ 
                echo B2_EMPTY;    
            } ?>
        </main>
    </div>
</div>
<?php

get_footer();