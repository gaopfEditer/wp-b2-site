<?php
use B2\Modules\Templates\Collection;
use B2\Modules\Templates\Modules\Sliders;
get_header();

$type = get_query_var('b2_module_type') ? get_query_var('b2_module_type') : false;

if(!$type){
    $error = __('当前模块的Key未设置','b2');
}

$index_settings = b2_get_option('template_index','index_group');

$settings = '';
foreach ($index_settings as $k => $v) {
    if($v['key'] == $type){
        $settings = $v;
        break;
    }
}

?>
<div class="b2-single-content wrapper">
    <div id="cat-group" class="wrapper">
        <main id="main" class="site-main">
            <?php if($error){ echo $error; ?>

            <?php }else{ ?>
                <div class="cat-group-page">
                    <div>
                        <h1><?php echo isset($settings['title']) ? $settings['title'] : ''; ?></h1>
                        <p><?php echo isset($settings['desc']) ? $settings['desc'] : ''; ?></p>
                    </div>
                    <div class="cat-group">
                        <?php 
                            if(!empty($settings['post_cat'])){
                                foreach ($settings['post_cat'] as $k => $v) {
                                    $cat_thumb = get_term_meta($v,'b2_tax_img',true);
                                    $cat_thumb = b2_get_thumb(array('thumb'=>$cat_thumb,'width'=>424,'height'=>170));
                                    $term = get_term_by( 'id',$v, 'category' ); 
                                ?>
                                    <div class="cat-group-box">
                                        <div class="cg-box-out b2-radius box">
                                            
                                                <div class="cg-header">
                                                    <div class="cg-bg">
                                                        <div class="cg-bg-in"><?php echo b2_get_img(array('src'=>$cat_thumb)); ?></div>
                                                        <div class="cg-title">
                                                            <h2><?php echo $term->name; ?></h2>
                                                            <p><?php echo $term->description ? $term->description : __('这个分类没有描述','b2'); ?></p>
                                                        </div>
                                                        <a class="button empty" href="<?php echo get_term_link((int)$v); ?>" target="_blank"><?php echo __('查看所有','b2'); ?></a>
                                                    </div>
                                                </div>
                                                <div class="cg-content">
                                                    <ul>
                                                    <?php 
                                                        $args = array(
                                                            'post_type'=>'post',
                                                            'posts_per_page'=>3,
                                                            'include_children'=>true,
                                                            'cat'=>$v,
                                                        );

                                                        $the_query = new \WP_Query( $args );

                                                        if ( $the_query->have_posts() ) {
                                                            while ( $the_query->have_posts() ) {

                                                                $the_query->the_post();
                                                                $excerpt = b2_get_excerpt($the_query->post->ID);
                                                                $view = get_post_meta($the_query->post->ID,'views',true);
                                                                ?>
                                                                <li>
                                                                    <div class="cg-p-title"><a href="<?php echo get_permalink(); ?>" target="_blank"><?php echo get_the_title(); ?></a></div>
                                                                    <div class="cg-p-desc"><?php echo b2_get_excerpt($the_query->post->ID); ?></div>
                                                                    <div class="cg-p-meta">
                                                                        <span class="cg-p-view"><?php echo b2_number_format($view ? $view : 0);?></span>
                                                                        <span><?php echo __('评论：','b2').b2_number_format(get_comments_number($the_query->post->ID));?></span>
                                                                        <span><?php echo __('时间：','b2').b2_timeago(get_the_date('Y-n-j G:i:s'));?></span>
                                                                    </div>
                                                                </li>
                                                                <?php
                                                                
                                                            }
                                                            wp_reset_postdata();
                                                        }else{
                                                            echo __('该分类下没有文章','b2');
                                                        }
                                                    ?>
                                                    </ul>
                                                </div>
                                            
                                        </div>
                                    </div>
                                <?php
                                }
                            }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </main>
    </div>
</div>
<?php

get_footer();