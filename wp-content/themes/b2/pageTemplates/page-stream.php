<?php
use B2\Modules\Common\Stream;
/**
 *Template Name: 信息流
*
*
*/
get_header();
$paged = isset($GLOBALS['wp_query']->query['paged']) ? $GLOBALS['wp_query']->query['paged'] : 1;

$content = b2_get_excerpt(get_the_ID());
?>

<div id="primary-home" class="stream-area">
    <?php if($content){ ?>
        <div class="home_row">
            <div class="wrapper">
                <?php the_content(); ?>
            </div>
        </div>
    <?php } ?>
    <div class="mg-t home_row">
        <div class="wrapper">
            <div class="home-row-left content-area box b2-radius" >
                <div id="b2-stream" ref="b2stream" data-paged="<?php echo $paged; ?>" data-author="0">
                    <div class="gujia" ref="gujia">
                        <?php

                            $count = get_option('posts_per_page');

                            $offset = ($paged -1)*$count;

                            $args = array(
                                'post_type'=>apply_filters('b2_stream_post_type', array(
                                    'post','circle','document','newsflashes'
                                )),
                                'posts_per_page' => $count,
                                'orderby' => 'date',
                                'offset'=>$offset,
                                'post_status'=>'publish',
                                'include_children' => true,
                                'paged'=>$paged
                            );

                            $the_query = new \WP_Query( $args );

                            $post_data = array();
                            $_pages = 1;
                            $_count = 0;

                            if ( $the_query->have_posts() ) {

                                $_pages = $the_query->max_num_pages;
                                $_count = $the_query->found_posts;

                                while ( $the_query->have_posts() ) {

                                    $the_query->the_post();

                                    ?>
                                    <article class="stream-article post-type-post">
                                        <header class="item-header">
                                            <div class="s-a-h">
                                                <div class="sah-l">
                                                    <span class="sah-avatar"></span>
                                                    <span class="sah-name"></span>
                                                    <span class="sah-date"></span>
                                                </div>
                                                <div class="sah-r">
                                                    <div class="sah-type"></div>
                                                    <div class="sah-catlist"></div>
                                                </div>
                                            </div>
                                        </header>
                                        <div class="s-a-c">
                                            <div class="s-a-c-l">
                                                <h2><a href="<?php echo get_permalink();?>" target="_blank" class="b2-out"><?php echo get_the_title(); ?></a></h2>
                                                <div class="item-content">
                                                    <div class="b2-out"></div>
                                                </div>
                                            </div>
                                            <div class="s-a-c-r"></div>
                                        </div>
                                        <div class="s-a-f">
                                            <div class="saf-z">
                                                <button></button>
                                                <button></button>
                                            </div>
                                            <div class="saf-c">
                                                <span class="saf-share"></span>
                                                <span class="saf-comment"></span>
                                            </div>
                                        </div>
                                    </article>
                                    <?php

                                }
                                wp_reset_postdata();
                            }

                        ?>
                    </div>
                    <div v-if="data !== ''" v-cloak>
                        <template v-for="(item,index) in data" :key="index">
                            <article :class="'stream-article st-'+item.data.terms.post_type.type" v-if="item.data.terms.post_type.type == 'post'">
                                <?php echo get_template_part( 'TempParts/stream/post'); ?>
                            </article>
                            <article :class="'stream-article st-'+item.data.terms.post_type.type" v-else-if="item.data.terms.post_type.type == 'circle'">
                                <?php echo get_template_part( 'TempParts/stream/circle'); ?>
                            </article>
                            <article :class="'stream-article st-'+item.data.terms.post_type.type" v-else-if="item.data.terms.post_type.type == 'document'">
                                <?php echo get_template_part( 'TempParts/stream/document'); ?>
                            </article>
                            <article :class="'stream-article st-'+item.data.terms.post_type.type" v-else-if="item.data.terms.post_type.type == 'newsflashes'">
                                <?php echo get_template_part( 'TempParts/stream/newsflashes'); ?>
                            </article>
                            <article :class="'stream-article st-'+item.data.terms.post_type.type" v-else-if="item.data.terms.post_type.type == 'shop'">
                                <?php echo get_template_part( 'TempParts/stream/shop'); ?>
                            </article>
                        </template>
                    </div>
                </div>
                <div class="b2-pd">
                    <?php echo b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); ?>
                </div>
            </div>
            <?php 
                get_sidebar(); 
            ?>
        </div>
    </div>
</div>

<?php
get_footer();