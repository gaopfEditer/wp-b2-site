<?php
use B2\Modules\Templates\Modules\Links;
use B2\Modules\Common\Links as LinkAction;

$top_ad = b2_get_option('links_single','link_single_top');
$bottom_ad = b2_get_option('links_single','link_single_bottom');

$id = get_the_id();

$link_to = get_post_meta($id,'b2_link_to',true);

$term = wp_get_post_terms( $id, 'link_cat', array( 'fields' => 'ids' ) );

if(!is_wp_error( $term ) && !empty($term)){
    $term = $term[0];
}else{
    $term = false;
}

$content = apply_filters( 'the_content', get_the_content() );
$excerpt = get_the_excerpt();

$content = $content ? $content : $excerpt;
$content = $content ? $content : __('这个站点没有任何描述','b2');

?>

<article class="single-article b2-radius box single-link ">
    <?php do_action('b2_single_article_before'); ?>
    <div class="link-breadcrumb b2-hover mg-b"><?php echo LinkAction::link_breadcrumb($id); ?></div>
        <header class="entry-header">
            <?php if($top_ad){ ?>
                <div class="link-single-top b2-radius mg-b">
                    <?php echo $top_ad;?>
                </div>
            <?php } ?>
            <div class="link-single-header">
                <h1><?php echo get_the_title(); ?></h1>
                <div>
                    <a href="<?php echo $link_to; ?>" target="_blank" class="button" rel="noopener noreferrer nofollow"><?php echo __('访问网站','b2'); ?></a>
                </div>
            </div>
        </header>
        <div class="entry-content">
            <?php do_action('b2_single_post_content_before'); ?>
            
            <?php echo $content; ?>
                        
            <?php do_action('b2_single_post_content_after'); ?>
        </div>

        <div class="single-link-rating" ref="linkSingle" data-id="<?php echo $id; ?>">
            <button :disable="locked" @click="linkVote" :class="isUp ? 'hasup text' : 'text'">
                <span v-if="!isUp"><?php echo b2_get_icon('b2-thumb-up-line'); ?></span>
                <span v-else><?php echo __('已赞','b2'); ?></span>
                <span v-text="up"></span>
            </button>
        </div>
        <?php if($bottom_ad){ ?>
            <div class="link-single-bottom b2-radius mg-t">
                <?php echo $bottom_ad;?>
            </div>
        <?php } ?>
        
    <?php do_action('b2_single_article_after'); ?>

</article>

<?php if($term){ ?>
    <div class="link-related">
        <?php
            $html = new Links();

            $arg = LinkAction::get_default_settings($term,['link_count'=>3,'link_count_total'=>12,'link_order'=>'link_rating'],true);

            echo $html->init($arg,0);
        ?>
    </div>
<?php } ?>